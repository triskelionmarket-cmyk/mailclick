<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;
use Acelle\Model\Webhook;
use Acelle\Model\WebhookJob;

class WebhookController extends Controller
{
    public function test(Request $request, $id)
    {
        $webhook = Webhook::findByUid($id);

        if ($request->isMethod('post')) {
            $webhookJob = $webhook->test($request->webhook);

            return view('webhooks.testResult', [
                'webhook' => $webhook,
                'webhookJobLog' => $webhookJob->webhookJobLogs()->latest()->first(),
            ]);
        }

        return view('webhooks.test', [
            'webhook' => $webhook,
        ]);
    }
}
