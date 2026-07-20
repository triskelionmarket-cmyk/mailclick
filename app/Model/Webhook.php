<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Acelle\Library\Traits\HasUid;

class Webhook extends Model
{
    use HasFactory;
    use HasUid;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const REQUEST_METHOD_GET = 'get';
    public const REQUEST_METHOD_POST = 'post';
    public const REQUEST_METHOD_PUT = 'put';
    public const REQUEST_METHOD_DELETE = 'delete';

    public const REQUEST_AUTH_TYPE_BASIC_AUTH = 'basic_auth';
    public const REQUEST_AUTH_TYPE_BEARER_TOKEN = 'bearer_token';
    public const REQUEST_AUTH_TYPE_CUSTOM = 'custom';
    public const REQUEST_AUTH_TYPE_NO_AUTH = 'no_auth';

    public const REQUEST_BODY_TYPE_KEY_VALUE = 'key_value';
    public const REQUEST_BODY_TYPE_PLAIN = 'plain';

    public function webhookJobs()
    {
        return $this->hasMany(WebhookJob::class, 'webhook_id');
    }

    public function fillAttributes($attributes)
    {
        if ($attributes['header_type'] == 'no_headers') {
            $attributes['request_headers'] = [];
        }

        if ($attributes['request_body_type'] == static::REQUEST_BODY_TYPE_PLAIN) {
            $attributes['request_body_params'] = [];
        } else {
            $attributes['request_body_plain'] = null;
        }

        $this->name = $attributes['name'] ?? $this->name;
        $this->setting_retry_times = $attributes['setting_retry_times'] ?? 0;
        $this->setting_retry_after_seconds = $attributes['setting_retry_after_seconds'] ?? 900;
        $this->request_method = $attributes['request_method'];
        $this->request_url = $attributes['request_url'];
        $this->request_auth_type = $attributes['request_auth_type'];
        $this->request_auth_bearer_token = $attributes['request_auth_bearer_token'];
        $this->request_auth_basic_username = $attributes['request_auth_basic_username'];
        $this->request_auth_basic_password = $attributes['request_auth_basic_password'];
        $this->request_auth_custom_key = $attributes['request_auth_custom_key'];
        $this->request_auth_custom_value = $attributes['request_auth_custom_value'];
        $this->request_headers = json_encode($attributes['request_headers']);
        $this->request_body_type = $attributes['request_body_type'];
        $this->request_body_params = isset($attributes['request_body_params']) ? json_encode($attributes['request_body_params']) : json_encode([]);
        $this->request_body_plain = $attributes['request_body_plain'];
    }

    public static function newDefault()
    {
        $webhook = new static();
        $webhook->status = static::STATUS_ACTIVE;

        $webhook->setting_retry_times = 2;
        $webhook->setting_retry_after_seconds = 900;
        $webhook->request_method = static::REQUEST_METHOD_GET;
        $webhook->request_auth_type = static::REQUEST_AUTH_TYPE_BASIC_AUTH;
        $webhook->request_body_type = static::REQUEST_BODY_TYPE_KEY_VALUE;

        return $webhook;
    }

    public static function scopeSearch($query, $keyword)
    {
        // Keyword
        if (!empty(trim($keyword))) {
            foreach (explode(' ', trim($keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('webhooks.name', 'like', '%'.$keyword.'%')
                        ->orWhere('webhooks.content', 'like', '%'.$keyword.'%');
                });
            }
        }
    }

    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function isInactive()
    {
        return $this->status == self::STATUS_INACTIVE;
    }

    public function disable()
    {
        $this->status = self::STATUS_INACTIVE;

        return $this->save();
    }

    public function enable()
    {
        $this->status = self::STATUS_ACTIVE;

        return $this->save();
    }

    public static function scopeActive($query)
    {
        $query->where('status', static::STATUS_ACTIVE);
    }

    public function saveWebhook($name, $event)
    {
        // fill
        $this->name = $name;
        $this->event = $event;

        $validator = \Validator::make($this->getAttributes(), [
            'name' => 'required',
            'event' => 'required',
        ]);

        if ($validator->fails()) {
            return [false, $validator->errors()];
        }

        $this->save();

        return [true, $validator->errors()];
    }

    public function getTags()
    {
        return array_map(function ($param) {
            return [
                'tag' => "{".$param."}",
                'label' => trans('messages.webhook.tag.' .$param),
            ];
        }, config('webhook_events')[$this->event]['params']);
    }

    public function getRequestHeaders()
    {
        if (!$this->request_headers) {
            return [];
        }

        return json_decode($this->request_headers, true);
    }

    public function getRequestBodyParams()
    {
        if (!$this->request_body_params) {
            return [];
        }

        return json_decode($this->request_body_params, true);
    }

    public function test($requestDetails = null)
    {
        switch ($this->event) {
            case 'cancel_subscription':
                $params = [
                    'customer_id' => '{customer_test_id}',
                    'plan_id' => '{plan_test_id}',
                ];
                break;
            case 'new_subscription':
                $params = [
                    'customer_id' => '{customer_test_id}',
                    'plan_id' => '{plan_test_id}',
                ];
                break;
            case 'new_customer':
                $params = [
                    'customer_id' => '{customer_test_id}',
                ];
                break;
            case 'change_plan':
                $params = [
                    'customer_id' => '{customer_test_id}',
                    'old_plan_id' => '{old_plan_test_id}',
                    'new_plan_id' => '{new_plan_test_id}',
                ];
                break;
            case 'terminate_subscription':
                $params = [
                    'customer_id' => '{customer_test_id}',
                    'plan_id' => '{plan_test_id}',
                ];
                break;
            case 'automation_webhook':
                $params = [
                    'automation_id' => '{automation_test_id}',
                ];
                break;
            default:
                throw new \Exception('Unknown event: ' . $this->event);
        }

        // Dispatch the job with the given parameters
        $webhookJob = $this->dispatch($params);

        // If request details are provided, populate webhook attributes (test only, do not save)
        if (!is_null($requestDetails)) {
            $webhookJob->webhook->fillAttributes($requestDetails);
        }

        // Execute the webhook job
        $webhookJob->run();

        return $webhookJob;
    }

    public function dispatch(array $params)
    {
        $webhookJob = $this->webhookJobs()->make();

        $webhookJob->customer_id = $this->customer_id;
        $webhookJob->fillParams($params);
        $webhookJob->status = WebhookJob::STATUS_NEW;

        $webhookJob->save();

        return $webhookJob;
    }

    public function run($params)
    {
        $webhookJob = $this->dispatch($params);
        $webhookJob->run();

        return $webhookJob;
    }

    public static function scopeBackend($query)
    {
        $query->whereNull('customer_id');
    }
}
