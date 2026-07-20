<?php

namespace Acelle\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;
use Acelle\Model\Webhook;
use Acelle\Model\WebhookJob;

class WebhookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('admin.webhooks.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $webhooks = Webhook::search($request->keyword)
            ->backend()
            ->orderBy($request->sort_order, $request->sort_direction ? $request->sort_direction : 'asc')
            ->paginate($request->per_page);

        return view('admin.webhooks.list', [
            'webhooks' => $webhooks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        $webhook = Webhook::newDefault();

        return view('admin.webhooks.create', [
            'webhook' => $webhook,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $webhook = Webhook::newDefault();

        // try to save
        list($result, $errors) = $webhook->saveWebhook(
            $name = $request->webhook['name'],
            $event = $request->webhook['event'],
        );

        // redirect if fails
        if (!$result) {
            return response()->view('admin.webhooks.create', [
                'webhook' => $webhook,
                'errors' => $errors,
            ], 400);
        }

        return redirect()->action('Admin\WebhookController@setup', $webhook->uid)
            ->with('alert-success', trans('messages.webhook.added.success', [
                'name' => $webhook->name,
            ]));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function setup(Request $request, $id)
    {
        //
        $webhook = Webhook::findByUid($id);

        if ($request->isMethod('post')) {
            $webhook->fillAttributes($request->webhook);
            $webhook->save();

            return response()->json([
                'status' => 'success',
                'message' => trans('messages.webhook.updated.success', [
                    'name' => $webhook->name,
                ]),
                'redirect' => action('Admin\WebhookController@index'),
            ]);
        }

        return view('admin.webhooks.setup', [
            'webhook' => $webhook,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $webhook = Webhook::findByUid($id);

        // try to save
        list($result, $errors) = $webhook->saveWebhook(
            $name = $request->webhook['name'],
            $event = $request->webhook['event'],
        );

        // redirect if fails
        if (!$result) {
            return response()->view('admin.webhooks.create', [
                'webhook' => $webhook,
                'errors' => $errors,
            ], 400);
        }

        return redirect()->action('Admin\WebhookController@index')
            ->with('alert-success', trans('messages.webhook.updated.success', [
                'name' => $webhook->name,
            ]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $webhooks = Webhook::whereIn(
            'uid',
            is_array($request->uids) ? $request->uids : explode(',', $request->uids)
        );

        foreach ($webhooks->get() as $webhook) {
            $webhook->delete();
        }

        // Redirect to my lists page
        echo trans('messages.webhooks.deleted');
    }

    /**
     * Disable sending server.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function disable(Request $request)
    {
        $webhooks = Webhook::whereIn(
            'uid',
            is_array($request->uids) ? $request->uids : explode(',', $request->uids)
        );

        foreach ($webhooks->get() as $webhook) {
            $webhook->disable();
        }

        // success
        echo trans('messages.webhooks.disabled');
    }

    /**
     * Disable sending server.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function enable(Request $request)
    {
        $webhooks = Webhook::whereIn(
            'uid',
            is_array($request->uids) ? $request->uids : explode(',', $request->uids)
        );

        foreach ($webhooks->get() as $webhook) {
            $webhook->enable();
        }

        // success
        echo trans('messages.webhooks.enabled');
    }

    public function test(Request $request, $id)
    {
        $webhook = Webhook::findByUid($id);

        if ($request->isMethod('post')) {
            $webhookJob = $webhook->test($request->webhook);

            return view('admin.webhooks.testResult', [
                'webhook' => $webhook,
                'webhookJobLog' => $webhookJob->webhookJobLogs()->latest()->first(),
            ]);
        }

        return view('admin.webhooks.test', [
            'webhook' => $webhook,
        ]);
    }

    public function jobs(Request $request, $id)
    {
        $webhook = Webhook::findByUid($id);

        return view('admin.webhooks.jobs', [
            'webhook' => $webhook,
        ]);
    }

    public function jobsList(Request $request, $id)
    {
        $webhook = Webhook::findByUid($id);
        $webhookJobs = $webhook->webhookJobs()
            ->orderBy($request->sort_order, $request->sort_direction ? $request->sort_direction : 'asc')
            ->paginate($request->per_page);

        return view('admin.webhooks.jobsList', [
            'webhook' => $webhook,
            'webhookJobs' => $webhookJobs,
        ]);
    }

    public function jobsLogs(Request $request, $id)
    {
        $webhookJob = WebhookJob::findByUid($id);

        return view('admin.webhooks.jobsLogs', [
            'webhookJob' => $webhookJob,
        ]);
    }

    public function jobsLogsList(Request $request, $id)
    {
        $webhookJob = WebhookJob::findByUid($id);
        $webhookJobLogs = $webhookJob->webhookJobLogs()
            ->orderBy($request->sort_order, $request->sort_direction ? $request->sort_direction : 'asc')
            ->paginate($request->per_page);

        return view('admin.webhooks.jobsLogsList', [
            'webhookJob' => $webhookJob,
            'webhookJobLogs' => $webhookJobLogs,
        ]);
    }
}
