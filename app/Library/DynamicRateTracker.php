<?php

namespace Acelle\Library;

use Illuminate\Support\Carbon;
use Exception;
use Acelle\Library\Exception\RateLimitExceeded;
use Acelle\Library\Exception\RateLimitReservedByAnotherFileSystem;
use Closure;
use Illuminate\Support\Facades\Cache;

/*
 * Limitation of this algorithm
 *
 *   + What if a process reserves all the credits but did not use it all?
 *   + It is even worse if the credits reservation is too long (no other process of another server can use it)
 *       - even if we use only 01 dedicated worker per campaign,
 *         we still have to deal with resource sharing for global sending servers (used by many customers and campaigns)
 *
 *   + Doubled credits used at window point
 *
 */

class DynamicRateTracker
{
    protected $resourceKey;
    protected $reservedPath;
    protected $remoteLock;

    protected $limits;

    protected $seperator = ':';
    protected $hostname;

    public function __construct(string $resourceKey, $limits = []) // RateLimit class
    {
        $this->resourceKey = $resourceKey;

        // IMPORTANT: this path is shared among processes of the same server (same filesystem)
        $this->reservedPath = "/tmp/dynamic-rate-tracker-pid-".md5($this->resourceKey);

        // Store the datetime until remote reservation is expired
        $this->remoteLock = "/tmp/dynamic-rate-tracker-pid-".md5($this->resourceKey)."-remote-lock";

        $this->limits = $limits;
        $this->hostname = gethostname() ?: 'Unknown';

        if (!file_exists($this->reservedPath)) {
            touch($this->reservedPath);
        }
    }

    public function getReservedPath()
    {
        return $this->reservedPath;
    }

    // Reverse of count()
    // @deprecated: rollback is not needed as even a failed operation is also counted in rate limits
    public function rollback()
    {
        // This is important, in case a tracker is counted (successfully, returning ok) in the execute_with_limits() function
        // and is later on rolled back
        if (empty($this->limits)) {
            return;
        }

        $lock = new Lockable($this->reservedPath);
        $lock->getExclusiveLock(function ($fopen) {
            list($until, $credits) = $this->parseReservedCredits($fopen);

            if ($until) {
                $this->updateReservedCredits($until, $credits + 1);
            } else {
                // do nothing as there is no reserved credits avaialable
            }

        });
    }

    public function getRateLimits()
    {
        return $this->limits;
    }

    // Example of $period: "24 hours", "1 week"
    // i.e. Clean up credit tracking logs that are older than "24 hours", "1 week"
    public function cleanup(string $period = null)
    {
        // no cleanup needed
    }

    private function updateReservedCredits(Carbon $until, $credits)
    {
        file_put_contents($this->reservedPath, "{$until->timestamp}{$this->seperator}{$credits}");
    }

    private function cacheRemoteLock($until)
    {
        file_put_contents($this->remoteLock, "{$until->timestamp}");
    }

    private function isRemoteLockCached()
    {
        if (!file_exists($this->remoteLock)) {
            return;
        }

        $until = file_get_contents($this->remoteLock);

        if (empty($until)) {
            return;
        }

        $untilObject = new Carbon((int)$until);

        if ($untilObject->gt(now())) {
            return $untilObject;
        } else {
            return;
        }
    }

    private function countReservedCredits(Carbon $now, $fopen, $failedCallback)
    {
        list($until, $credits) = $this->parseReservedCredits($fopen);

        if ($until && $until->gte($now)) {
            // locally reserved

            if ($credits > RateLimit::ZERO) {
                // if local credits available
                $this->updateReservedCredits($until, $credits - 1);

                return true;
            } else {
                $msg = "Calculated rate limit exceeded. Currently reserved by this host {$this->hostname}, but no credits left: {$this->getMinCredits()}/{$this->getShortestLimitPeriod()} ({$this->getLimitsDescription()}). Reserved until {$until->format('H:i T')}, {$until->diffForHumans()}";

                if ($failedCallback) {
                    $failedCallback($msg);
                }
                // if locally reserved but no credits left
                throw new RateLimitExceeded($msg);

            }
        } else {
            // Not locally reserved
            return false;
        }
    }

    // Test and throw exception, do not count()
    // Used by DelayedJob
    public function test(Carbon $now = null, $failedCallback = null)
    {
        $this->doCount($now, $count = false, $failedCallback);
    }

    public function count(Carbon $now = null, $failedCallback = null)
    {
        $this->doCount($now, $count = true, $failedCallback);
    }

    private function doCount(Carbon $now = null, $count = true, $failedCallback = null)
    {
        if (empty($this->limits)) {
            return;
        }

        $lock = new Lockable($this->reservedPath);
        $lock->getExclusiveLock(function ($fopen) use ($now, $count, $failedCallback) {
            $now = $now ?: Carbon::now();

            // If there is a valid local reservation, 3 possible outcome
            // - true: OK
            // - false: no local reservation, check remote....
            // - Exception: reserved by this host but no credits left
            if ($this->countReservedCredits($now, $fopen, $failedCallback)) {
                return;
            }

            // Else, no local reservation, checking remote

            // A valid remote reservation which was previously cached (local check)
            if ($until = $this->isRemoteLockCached()) {
                $msg = "Currently reserved by another process/filesystem (current host: {$this->hostname}): {$this->getMinCredits()}/{$this->getShortestLimitPeriod()} ({$this->getLimitsDescription()}). Will be available for booking in {$until->diffForHumans()}, at {$until->format('H:i T')}";

                if ($failedCallback) {
                    $failedCallback($msg);
                }

                throw new RateLimitReservedByAnotherFileSystem(
                    $msg,
                    $code = 0,
                    $previous = null,
                    $until // pass this additional parameter to Exception
                );
            }


            // In the worst case, connect to remote....
            with_cache_lock($this->resourceKey, function () use ($now, $count, $failedCallback) {
                // returning null or an active reservation Carbon object
                if ($until = $this->isReservedByOthers($now)) {
                    $msg = "***Currently reserved by another process/filesystem (current host: {$this->hostname}): {$this->getMinCredits()}/{$this->getShortestLimitPeriod()} ({$this->getLimitsDescription()}). Will be available for booking in {$until->diffForHumans()}, at {$until->format('H:i T')}";

                    if ($failedCallback) {
                        $failedCallback($msg);
                    }

                    // Cache here, so other process does not have to come all the way here
                    // They will be able to stop earlier at isRemoteLockCache() check above
                    // A remote request is eliminated
                    $this->cacheRemoteLock($until);

                    throw new RateLimitReservedByAnotherFileSystem(
                        $msg,
                        $code = 0,
                        $previous = null,
                        $until // pass this additional parameter to Exception
                    );
                }

                if (!$count) {
                    // this is for Delay job only
                    // test if it shoudl resume a campaign, do not count ONE use credits
                    return;
                }

                // Okie, so ready
                $until = $now->add($this->getShortestLimitPeriod());
                $credits = $this->getMinCredits();

                // reserve credits for local & remote
                // also count 1 credit used
                $this->reserve($until, $credits - 1);

            }, $timeout = 15);
        });
    }

    // Return a Carbon object if reserved by remote peer
    // Return null if no reservation or reservation is already expired
    public function isReservedByOthers(Carbon $now)
    {
        $reservedUntil = Cache::get($this->resourceKey);

        if (!is_null($reservedUntil)) {
            $untilObj = new Carbon((int)$reservedUntil);
            if ($untilObj->gte($now)) {
                return $untilObj;
            }
        }

        return null;
    }

    private function reserve(Carbon $until, $credits)
    {
        // Set local
        $this->updateReservedCredits($until, $credits);

        // Set remote reserved flag
        Cache::put($this->resourceKey, $until->timestamp);
    }

    public function parseReservedCredits($fopen = null)
    {
        if (is_null($fopen)) {
            $fopen = fopen($this->getReservedPath(), 'r');
        }

        rewind($fopen);
        $contents = fgets($fopen);
        if (empty($contents)) {
            return [null, null];
        }

        list($until, $credits) = explode($this->seperator, $contents);

        return [new Carbon((int)$until), (int)$credits];
    }

    public function getShortestLimitPeriod()
    {
        $shortest = null;
        $now = now();
        foreach ($this->limits as $limit) {
            $d = $now->copy()->add($limit->getPeriod());
            if (is_null($shortest) || $now->copy()->add($shortest->getPeriod())->gte($d)) {
                $shortest = $limit;
            }
        }

        return $shortest->getPeriod();
    }

    public function getMinCredits()
    {
        $min = null;
        foreach ($this->limits as $limit) {
            if (is_null($min) || $limit->getAmount() < $min) {
                $min = $limit->getAmount();
            }
        }

        return $min;
    }

    public function clearReservation()
    {
        file_put_contents($this->getReservedPath(), '');
        Cache::forget($this->resourceKey);
    }

    public function clearLocalReservation()
    {
        file_put_contents($this->getReservedPath(), '');
    }

    public function getLimitsDescription()
    {
        $str = [];
        foreach ($this->getRateLimits() as $limit) {
            $str[] = $limit->getDescription();
        }

        return implode(', ', $str);
    }
}
