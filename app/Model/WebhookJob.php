<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Acelle\Library\Traits\HasUid;

class WebhookJob extends Model
{
    use HasFactory;
    use HasUid;

    public const STATUS_NEW = 'new';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    public function webhook()
    {
        return $this->belongsTo(Webhook::class, 'webhook_id');
    }

    public function webhookJobLogs()
    {
        return $this->hasMany(WebhookJobLog::class, 'webhook_job_id');
    }

    public static function newDefault()
    {
        $log = new static();
        $log->status = static::STATUS_NEW;

        return $log;
    }

    public function fillParams($params)
    {
        $this->params = json_encode($params);
    }

    public function getParams(): array
    {
        return json_decode($this->params, true);
    }

    public function processRequests()
    {
        $attributes = [];
        $paramsValues = $this->getParams();

        //
        $attributes['request_method'] = $this->webhook->request_method;
        $attributes['request_url'] = $this->webhook->request_url;
        $attributes['request_auth_type'] = $this->webhook->request_auth_type;
        $attributes['request_auth_bearer_token'] = $this->webhook->request_auth_bearer_token;
        $attributes['request_auth_basic_username'] = $this->webhook->request_auth_basic_username;
        $attributes['request_auth_basic_password'] = $this->webhook->request_auth_basic_password;
        $attributes['request_auth_custom_key'] = $this->webhook->request_auth_custom_key;
        $attributes['request_auth_custom_value'] = $this->webhook->request_auth_custom_value;
        $attributes['request_headers'] = $this->webhook->request_headers;
        $attributes['request_body_type'] = $this->webhook->request_body_type;
        $attributes['request_body_params'] = $this->webhook->request_body_params;
        $attributes['request_body_plain'] = $this->webhook->request_body_plain;

        //
        foreach ($attributes as $key => $value) {
            foreach ($paramsValues as $param => $value) {
                if ($attributes[$key] !== null) {
                    $attributes[$key] = str_replace('{' .$param. '}', $value, $attributes[$key]);
                }
            }
        }

        //
        $attributes['request_headers'] = json_decode($attributes['request_headers'], true);
        $attributes['request_body_params'] = json_decode($attributes['request_body_params'], true);

        return $attributes;
    }

    public function run()
    {
        //
        $requests = $this->processRequests();

        // Prepare cURL initialization
        $ch = curl_init();

        // Set the URL from the options
        curl_setopt($ch, CURLOPT_URL, $requests['request_url']);

        // Set the HTTP method (POST)
        $method = strtoupper($requests['request_method']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // Set the Authorization (Basic Auth)
        if ($requests['request_auth_type'] === Webhook::REQUEST_AUTH_TYPE_BASIC_AUTH) {
            $username = $requests['request_auth_basic_username'];
            $password = $requests['request_auth_basic_password'];
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        }

        // Set Headers
        $headers = [];
        if (!empty($requests['request_headers'])) {
            foreach ($requests['request_headers'] as $header) {
                $headers[] = $header['key'] . ': ' . $header['value'];
            }
        }

        // Add Content-Type based on body type
        if ($requests['request_body_type'] === Webhook::REQUEST_BODY_TYPE_KEY_VALUE) {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        } elseif ($requests['request_body_type'] === Webhook::REQUEST_BODY_TYPE_PLAIN) {
            // No special Content-Type needed for plain text
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Set the body type and parameters
        $body = '';
        if ($requests['request_body_type'] === Webhook::REQUEST_BODY_TYPE_PLAIN) {
            $body = $requests['request_body_plain'];
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } elseif ($requests['request_body_type'] === Webhook::REQUEST_BODY_TYPE_KEY_VALUE) {
            $bodyParams = [];
            foreach ($requests['request_body_params'] as $param) {
                $bodyParams[] = urlencode($param['key']) . '=' . urlencode($param['value']);
            }
            $body = implode('&', $bodyParams);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        // Set additional cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Execute the cURL request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);  // Get HTTP response code

        // Capture cURL errors
        $curlError = curl_errno($ch) ? curl_error($ch) : null;

        // Close the cURL session
        curl_close($ch);

        // Display all response aspects
        $result = [
            'method' => $method,
            'body' => $body,
            'error' => $curlError,
            'http_code' => $httpCode,
            'response' => $response,
        ];

        //
        $this->addLog(
            $requestDetails = $requests,
            $responseHttpCode = $result['http_code'],
            $repsonseContent = $result['response'],
            $responseRrror = $result['error']
        );

        //
        if ($result['http_code'] == 200) {
            $this->status = static::STATUS_SUCCESS;
        } else {
            $this->status = static::STATUS_FAILED;
        }
        $this->save();

        return $result;
    }

    public function addLog($requestDetails, $responseHttpCode, $repsonseContent, $responseRrror = null)
    {
        $webhookJobLog = $this->webhookJobLogs()->make();
        $webhookJobLog->customer_id = $this->customer_id;
        $webhookJobLog->request_details = json_encode($requestDetails);
        $webhookJobLog->response_http_code = $responseHttpCode;
        $webhookJobLog->response_content = is_string($repsonseContent) ? mb_convert_encoding($repsonseContent, 'UTF-8', 'UTF-8') : $repsonseContent;
        $webhookJobLog->response_error = $responseRrror;

        $webhookJobLog->save();

        return $webhookJobLog;
    }

    public function getLatestLog()
    {
        return $this->webhookJobLogs()->latest()->first();
    }
}
