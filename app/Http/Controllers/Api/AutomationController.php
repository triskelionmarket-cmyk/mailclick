<?php

namespace Acelle\Http\Controllers\Api;

use Acelle\Http\Controllers\Controller;
use Acelle\Model\Automation2;
use Acelle\Jobs\ForceTriggerAutomationViaApi;
use Illuminate\Http\Request;

/**
 * /api/v1/campaigns - API controller for managing campaigns.
 */
class AutomationController extends Controller
{
    /**
     * Call api for automation api call type.
     *
     * GET /api/v1/campaigns
     *
     * @return \Illuminate\Http\Response
     */
    public function execute(Request $request)
    {
        try {
            $automation = Automation2::findByUid($request->uid);
            $automation->logger()->info(sprintf('Queuing automation "%s" in response to API call', $automation->name));
            safe_dispatch(new ForceTriggerAutomationViaApi($automation));

            return \Response::json(['success' => true], 200);
        } catch (\Exception $ex) {
            return \Response::json(['success' => false, 'error' => $ex->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $user = \Auth::guard('api')->user();

        // Get page and per_page from request, with default values
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        // Get automations with pagination
        $automationsQuery = $user->customer->local()->automation2s();

        $total = $automationsQuery->count();
        $automations = $automationsQuery->skip(($page - 1) * $perPage)->take($perPage)->get();

        $automations = $automations->map(function ($automation, $key) {
            return [
                'uid' => $automation->uid,
                'name' => $automation->name,
                'description' => $automation->getBriefIntro(),
                'subscribers' => $automation->mailList->readCache('SubscriberCount', '#'),
                'emails' => $automation->countEmails(),
                'complete' => $automation->readCache('SummaryStats') ? $automation->readCache('SummaryStats')['complete'] : 0,
                'status' => $automation->status,
                'last_error' => $automation->last_error,
                'last_updated' => $automation->updated_at->diffForHumans(['options' => 2]),
                'created_at' => $automation->created_at,
                'updated_at' => $automation->updated_at,
            ];
        });

        return \Response::json($automations, 200);
    }
}
