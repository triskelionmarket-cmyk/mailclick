<h4 class="mb-2">
    {{ trans('messages.campaign.delivery_settings') }}
</h4>
<p>{{ trans('messages.campaign.delivery_settings.intro') }}</p>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <div>
                @foreach ([
                    \Acelle\Model\Subscriber::VERIFICATION_STATUS_UNVERIFIED,
                    \Acelle\Model\Subscriber::VERIFICATION_STATUS_DELIVERABLE,
                    \Acelle\Model\Subscriber::VERIFICATION_STATUS_RISKY,
                    \Acelle\Model\Subscriber::VERIFICATION_STATUS_UNKNOWN,
                    \Acelle\Model\Subscriber::VERIFICATION_STATUS_UNDELIVERABLE,
                ] as $status)
                    <div data-control="option-container" class="d-flex mb-2">
                        <label class="me-3">
                            <input {{ in_array($status, $email->getDeliveryStatuses()) ? 'checked' : '' }} type="checkbox" name="delivery_statuses[]" value="{{ $status }}" id="{{ $status }}_status" class="styled"><span class="check-symbol"></span>
                        </label>
                        <div>
                            <label for="{{ $status }}_status" class="mb-0 radio-label d-inline-block">{{ trans('messages.delivery_settings.status.' . $status) }}</label>
                            <p class="text-muted mb-2 small fst-italic">
                                {{ trans('messages.delivery_settings.status.' . $status . '.wording') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>