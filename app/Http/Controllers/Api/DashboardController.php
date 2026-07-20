<?php

namespace Acelle\Http\Controllers\Api;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;
use Acelle\Model\Customer;
use Acelle\Model\Language;
use Acelle\Library\Facades\SubscriptionFacade;
use Acelle\Model\SubscriptionLog;
use Acelle\Library\TransactionResult;
use Acelle\Library\Facades\Hook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Acelle\Model\User;

/**
 * /api/v1/customers - API controller for managing customers.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = \Auth::guard('api')->user();

        event(new \Acelle\Events\UserUpdated($user->customer));
        $currentTimezone = $user->customer->getTimezone();

        // Last month
        $customer = $user->customer;

        $maxLists = get_tmp_quota($customer, 'list_max');
        $maxCampaigns = get_tmp_quota($customer, 'campaign_max');
        $maxSubscribers = get_tmp_quota($customer, 'subscriber_max');

        $listsCount = $customer->local()->listsCount();
        $listsUsed = ($maxLists == -1) ? 0 : $listsCount / $maxLists;

        $campaignsCount = $customer->local()->campaignsCount();
        $campaignsUsed = ($maxCampaigns == -1) ? 0 : $campaignsCount / $maxCampaigns;

        $subscribersCount = $customer->local()->readCache('SubscriberCount', 0);
        $subscribersUsed = ($maxSubscribers == -1) ? 0 : $subscribersCount / $maxSubscribers;

        return response()->json([
            'campaign_count' => $campaignsCount,
            'list_count' => $listsCount,
            'subscriber_percent' => $subscribersUsed * 100,
            'subscriber_used' => (number_with_delimiter($subscribersCount)) . '/' . (($maxSubscribers == -1) ? '∞' : number_with_delimiter($maxSubscribers)),

            // 'currentTimezone' => $currentTimezone,
            // 'maxLists' => $maxLists,
            // 'listsCount' => $listsCount,
            // 'listsUsed' => $listsUsed,
            // 'maxCampaigns' => $maxCampaigns,
            // 'campaignsCount' => $campaignsCount,
            // 'campaignsUsed' => $campaignsUsed,
            // 'maxSubscribers' => $maxSubscribers,
            // 'subscribersCount' => $subscribersCount,
            // 'subscribersUsed' => $subscribersUsed,
        ], 200);
    }
}
