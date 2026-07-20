<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use Acelle\Library\Traits\HasUid;

class WebhookJobLog extends Model
{
    use HasUid;

    public function webhookJob()
    {
        return $this->belongsTo(WebhookJob::class, 'webhook_job_id');
    }

    public function getRequestDetails()
    {
        return json_decode($this->request_details, true);
    }
}
