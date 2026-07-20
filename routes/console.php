<?php

use Illuminate\Support\Facades\Schedule;

use Acelle\Model\Automation2;
use Acelle\Model\Notification;
use Acelle\Cashier\Cashier;
use Acelle\Model\Subscription;
use Acelle\Model\Setting;
use Acelle\Model\Campaign;
use Acelle\Model\Customer;
use Laravel\Tinker\Console\TinkerCommand;
use Acelle\Library\Facades\SubscriptionFacade;
use Acelle\Helpers\LicenseHelper;

// Call \Artisan::call(...) in the application will exit(). tmp fixed
// if (isInitiated()) {
//     exit();
// }

if (isInitiated()) {
    // Make sure CLI process is NOT executed as root
    Notification::recordIfFails(function () {
        if (!exec_enabled()) {
            throw new Exception('The exec() function is missing or disabled on the hosting server');
        }

        if (exec('whoami') == 'root') {
            throw new Exception("Cronjob process is executed by 'root' which might cause permission issues. Make sure the cronjob process owner is the same as the acellemail/ folder's owner");
        }
    }, 'CronJob issue');

    // Make sure CLI process is NOT executed as root
    Notification::recordIfFails(function () {
        $minPHPRecommended = config('custom.php_recommended');

        if (!version_compare(PHP_VERSION, $minPHPRecommended, '>=')) {
            throw new Exception(trans('messages.requirement.php_version.not_supuported.description', ['current' => PHP_VERSION, 'required' => $minPHPRecommended]));
        }
    }, $phpMsgTitle = trans('messages.requirement.php_version.not_supuported.title'));

    Schedule::call(function () {
        event(new \Acelle\Events\CronJobExecuted());
    })->name('cronjob_event:log')->everyMinute();

    // Echo okie
    Schedule::command('automation:dispatch')->everyFiveMinutes();

    // Bounce/feedback handler
    Schedule::command('handler:run')->everyThirtyMinutes();

    // Sender verifying
    Schedule::command('sender:verify')->everyFiveMinutes();

    // System clean up
    Schedule::command('system:cleanup')->daily();

    // Re verify tracking domains
    Schedule::command('tracking-domains:verify')->weekly()->sundays()->at('13:00');

    // GeoIp database check
    Schedule::command('geoip:check')->everyMinute()->withoutOverlapping(60);

    // Subscription: check expiration
    Schedule::call(function () {
        Notification::recordIfFails(function () {
            SubscriptionFacade::endExpiredSubscriptions();
            SubscriptionFacade::createRenewInvoices();
            SubscriptionFacade::autoChargeRenewInvoices();
        }, 'Error checking subscriptions');
    })->name('subscription:monitor')->everyFiveMinutes();

    // Check for scheduled campaign to execute
    Schedule::command('campaign:schedule')->everyMinute();

    $licenseTask = Schedule::call(function () {
        Notification::recordIfFails(
            function () {
                $license = LicenseHelper::getCurrentLicense();

                if (is_null($license)) {
                    throw new Exception(trans('messages.license.error.no_license'));
                }

                LicenseHelper::refreshLicense();
            },
            $title = trans('messages.license.error.invalid'),
            $exceptionCallback = null,
        );
    })->name('verify_license');

    if (config('custom.japan')) {
        $licenseTask->everyMinute();
    } else {
        $licenseTask->weeklyOn(rand(1, 6), '10:'.rand(10, 59)); // randomly from Mon to Sat, at 10:10 - 10:59
    }

    // Rerun "sending" campaigns that are stuck for too long
    Schedule::command('campaign:rerun')->everyTenMinutes();

    /*
    // Update list/user cache every 30 minutes
    // @important: potential performance issue here
    Schedule::call(function() {
        $customers = Customer::all();

        foreach($customers as $customer) {
            if (is_null($customer->getCurrentActiveGeneralSubscription())) {
                continue;
            }

            $lists = $customer->local()->lists;

            foreach ($lists as $list) {
                dispatch(new \Acelle\Jobs\UpdateMailListJob($list));
            }
            dispatch(new \Acelle\Jobs\UpdateUserJob($customer));
        }
    })->name('update_list_stats')->daily();
    */

    // Queued import/export/campaign
    // Allow overlapping: max 10 proccess as a given time (if cronjob interval is every minute)
    // Job is killed after timeout
    // Notice that the following queue must be exected on master (involved in local filesystem on the same web server)
    // + default
    // + import
    //
    if (!config('custom.distributed_worker')) {
        Schedule::command('queue:work --queue=import,default,high,batch,single,automation-dispatch,automation --tries=1 --max-time=180')->everyMinute();
    }
}
