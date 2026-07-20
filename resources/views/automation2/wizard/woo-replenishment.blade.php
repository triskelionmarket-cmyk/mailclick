<div class="mb-20">
    <input type="hidden" name="options[type]" value="woo-replenishment" />
    
    @php
        $user = Auth::user() ?: request()->user();
        $customer = $user ? $user->customer->local() : null;
        $sourceOptions = $customer ? $customer->getSelectOptions('woocommerce') : [];
        $defaultSourceUid = count($sourceOptions) > 0 ? $sourceOptions[0]['value'] : '';
    @endphp

    <div class="edit-connect-url">
        @include('helpers.form_control', [
            'type' => 'select',
            'class' => '',
            'label' => trans('messages.automation.trigger.woo_replenishment.select_store'),
            'name' => 'options[source_uid]',
            'value' => $defaultSourceUid,
            'options' => $sourceOptions,
            'help_class' => 'trigger',
            'rules' => [],
        ])
    </div>

    @include('helpers.form_control', [
        'name' => 'mail_list_uid',
        'include_blank' => trans('messages.automation.choose_list'),
        'type' => 'select',
        'label' => trans('messages.list'),
        'value' => '',
        'options' => $customer ? $customer->readCache('MailListSelectOptions', []) : [],
    ])

    <div class="automation-segment">

    </div>
</div>
