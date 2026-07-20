<?php

namespace Acelle\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller
{
    use ValidatesRequests;

    public function __construct()
    {
        // Keep it here for plugin compatibility
        // Certain plugins might inherit this controller and call parent::construct();
    }

    /**
     * Check if the user is not authorized.
     *
     * @return \Illuminate\Http\Response
     */
    public function notAuthorized()
    {
        if (request()->ajax()) {
            return response()->json(['message' => trans('messages.not_authorized_message')], 403);
        }

        return response()->view('notAuthorized')->setStatusCode(403);
    }

    /**
     * Check if the user cannot create more item.
     *
     * @return \Illuminate\Http\Response
     */
    public function noMoreItem()
    {
        return view('noMoreItem');
    }
}
