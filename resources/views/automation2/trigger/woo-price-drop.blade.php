<div class="mb-20">
    <input type="hidden" name="options[type]" value="woo-price-drop" />
    
    @php
        $trigger = $automation->getTrigger();
    @endphp

    <div class="edit-connect-url {{ $trigger->getOption('source_uid') && !request()->options ? 'hide' : '' }}">
        @include('helpers.form_control', [
            'type' => 'select',
            'class' => '',
            'label' => trans('messages.automation.trigger.woo_price_drop.select_store'),
            'name' => 'options[source_uid]',
            'value' => $trigger->getOption('source_uid'),
            'options' => request()->user()->customer->local()->getSelectOptions('woocommerce'),
            'help_class' => 'trigger',
            'rules' => $rules,
        ])
    </div>

    @if ($trigger->getOption('source_uid') && !request()->options)
        @php
            $source = Acelle\Model\Source::findByUid($trigger->getOption('source_uid'));
            $shopinfo = $source ? $source->getData()['data'] ?? [] : [];
        @endphp
        @if (isset($shopinfo['name']))
            <div class="cart-settings mb-4">
                <div class="settings">
                    <div class="d-flex my-2 py-1">
                        <div class="check-icon mr-4 pt-1">
                            <span class="material-symbols-rounded text-success">check_circle</span>
                        </div>
                        <div class="setting-content">
                            {!! trans('messages.automation.trigger.woo_price_drop.connected', [
                                'store' => $shopinfo['name'],
                            ]) !!}
                        </div>
                    </div>
                    <div class="d-flex my-2 py-1">
                        <div class="check-icon mr-4 pt-1">
                            <span class="material-symbols-rounded text-success">check_circle</span>
                        </div>
                        <div class="setting-content">
                            {{ trans('messages.automation.trigger.woo_price_drop.description') }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
