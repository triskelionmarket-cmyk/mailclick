<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => '\Acelle\Http\Controllers\Api', 'prefix' => 'v1', 'middleware' => ['auth:api']], function () {
    //
    Route::get('', function () {
        return \Response::json(\Auth::guard('api')->user());
    });

    // Simple authentication
    Route::get('me', function () {
        return \Response::json(\Auth::guard('api')->user());
    });

    // List
    Route::delete('lists/{uid}', 'MailListController@delete');
    Route::post('lists/{uid}/add-field', 'MailListController@addField');
    Route::resource('lists', 'MailListController')->names([
        'index' => 'api.lists.index',
        'create' => 'api.lists.create',
        'store' => 'api.lists.store',
        'show' => 'api.lists.show',
        'edit' => 'api.lists.edit',
        'update' => 'api.lists.update',
        'destroy' => 'api.lists.destroy',
    ]);

    // Campaign
    Route::delete('campaigns/{uid}', 'CampaignController@delete');
    Route::post('campaigns/{uid}/pause', 'CampaignController@pause');
    Route::post('campaigns/{uid}/run', 'CampaignController@run');
    Route::post('campaigns/{uid}/resume', 'CampaignController@resume');
    Route::resource('campaigns', 'CampaignController')->names([
        'index' => 'api.campaigns.index',
        'create' => 'api.campaigns.create',
        'store' => 'api.campaigns.store',
        'show' => 'api.campaigns.show',
        'edit' => 'api.campaigns.edit',
        'update' => 'api.campaigns.update',
        'destroy' => 'api.campaigns.destroy',
    ]);

    // Subscriber
    Route::patch('lists/{list_uid}/subscribers/email/{email}/unsubscribe', 'SubscriberController@unsubscribeEmail');
    Route::post('subscribers/{id}/add-tag', 'SubscriberController@addTag');
    Route::post('subscribers/{id}/remove-tag', 'SubscriberController@removeTag');
    Route::get('subscribers/email/{email}', 'SubscriberController@showByEmail');
    Route::patch('lists/{list_uid}/subscribers/{id}/subscribe', 'SubscriberController@subscribe');
    Route::patch('lists/{list_uid}/subscribers/{id}/unsubscribe', 'SubscriberController@unsubscribe');
    Route::delete('subscribers/{id}', 'SubscriberController@delete');

    Route::resource('subscribers', 'SubscriberController')->names([
        'index' => 'api.subscribers.index',
        'create' => 'api.subscribers.create',
        'store' => 'api.subscribers.store',
        'show' => 'api.subscribers.show',
        'edit' => 'api.subscribers.edit',
        'update' => 'api.subscribers.update',
        'destroy' => 'api.subscribers.destroy',
    ]);

    // Automation
    Route::post('automations/{uid}/api/call', 'AutomationController@apiCall');

    // Sending server
    Route::resource('sending_servers', 'SendingServerController')->names([
        'index' => 'api.sending_servers.index',
        'create' => 'api.sending_servers.create',
        'store' => 'api.sending_servers.store',
        'show' => 'api.sending_servers.show',
        'edit' => 'api.sending_servers.edit',
        'update' => 'api.sending_servers.update',
        'destroy' => 'api.sending_servers.destroy',
    ]);

    // Plan
    Route::resource('plans', 'PlanController')->names([
        'index' => 'api.plans.index',
        'create' => 'api.plans.create',
        'store' => 'api.plans.store',
        'show' => 'api.plans.show',
        'edit' => 'api.plans.edit',
        'update' => 'api.plans.update',
        'destroy' => 'api.plans.destroy',
    ]);

    // Customer
    Route::get('customers/by-email/{email}', 'CustomerController@findByEmail');
    Route::post('customers/{uid}/subscription/update', 'CustomerController@subscriptionUpdate');
    Route::post('customers/{uid}/change-plan/{plan_uid}', 'CustomerController@changePlan');
    Route::match(['get','post'], 'login-token', 'CustomerController@loginToken');
    Route::post('customers/{uid}/assign-plan/{plan_uid}', 'CustomerController@assignPlan');
    Route::patch('customers/{uid}/disable', 'CustomerController@disable');
    Route::patch('customers/{uid}/enable', 'CustomerController@enable');
    Route::resource('customers', 'CustomerController')->names([
        'index' => 'api.customers.index',
        'create' => 'api.customers.create',
        'store' => 'api.customers.store',
        'show' => 'api.customers.show',
        'edit' => 'api.customers.edit',
        'update' => 'api.customers.update',
        'destroy' => 'api.customers.destroy',
    ]);

    // Subscription
    Route::resource('subscriptions', 'SubscriptionController')->names([
        'index' => 'api.subscriptions.index',
        'create' => 'api.subscriptions.create',
        'store' => 'api.subscriptions.store',
        'show' => 'api.subscriptions.show',
        'edit' => 'api.subscriptions.edit',
        'update' => 'api.subscriptions.update',
        'destroy' => 'api.subscriptions.destroy',
    ]);

    // File
    Route::post('file/upload', 'FileController@upload');

    // File
    Route::post('automations/{uid}/execute', 'AutomationController@execute')->name('automation_execute');

    Route::post('notification/bounce', 'NotificationController@bounce');
    Route::post('notification/feedback', 'NotificationController@feedback');

    // User
    Route::get('user/info', 'UserController@info');

    // Dashboard
    Route::get('dashboard', 'DashboardController@index');

    // Automation
    Route::get('automations', 'AutomationController@index');
});

Route::group(['namespace' => '\Acelle\Http\Controllers\Api', 'prefix' => 'v1', 'middleware' => []], function () {
    // User
    Route::post('user/login', 'UserController@login');
});

Route::group(['namespace' => '\Acelle\Http\Controllers\Api\Public', 'prefix' => 'v1'], function () {
    Route::post('public/subscribers', 'SubscriberController@store');

    // Payment
    Route::get('payment/list', 'PaymentController@list');

    // Email Verification
    Route::get('email-verification/get-checkout-url', 'EmailVerificationController@getCheckoutUrl');
    Route::get('email-verification/get-subscription', 'EmailVerificationController@getSubscription');
    Route::post('email-verification/customer/find-create', 'EmailVerificationController@findOrCreateCustomer');
    Route::get('email-verification/feature-plan', 'EmailVerificationController@getFeaturePlan');
    Route::post('email-verification/subscribe', 'EmailVerificationController@subscribe');

    // Plans
    Route::get('public/plans/available', 'PlanController@availablePlans');
});
