<div class="mb-20">
    <input type="hidden" name="options[type]" value="woo-price-drop" />
    
    @php
        $trigger = $automation->getTrigger();
        $sourceOptions = request()->user()->customer->local()->getSelectOptions('woocommerce');
        $defaultSourceUid = count($sourceOptions) > 0 ? $sourceOptions[0]['value'] : '';
        $selectedSourceUid = $trigger->getOption('source_uid') ?: $defaultSourceUid;
    @endphp

    <div class="edit-connect-url">
        @include('helpers.form_control', [
            'type' => 'select',
            'class' => '',
            'label' => trans('messages.automation.trigger.woo_price_drop.select_store'),
            'name' => 'options[source_uid]',
            'value' => $selectedSourceUid,
            'options' => $sourceOptions,
            'help_class' => 'trigger',
            'rules' => [],
        ])
    </div>
</div>
