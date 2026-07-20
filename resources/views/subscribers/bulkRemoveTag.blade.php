@extends('layouts.popup.small')

@section('content')
	<div class="row">
        <div class="col-md-12">
            <form id="RemoveTagForm" action="{{ action("SubscriberController@bulkRemoveTag", $list->uid) }}"
                method="POST" class="remove-tag"
            >
                {{ csrf_field() }}

                @foreach (request()->all() as $name => $value)
                    @if (is_array($value))
                        @foreach ($value as $v)
                            <input type="hidden" name="{{ $name }}[]" value="{{ $v }}" />
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}" />
                    @endif
                @endforeach

                <input type="hidden" name="select_tool" value="{{ request()->select_tool }}" />

                <h3 class="mb-3">{{ trans('messages.subscriber.remove_tag') }}</h3>
                <p>{!! trans('messages.subscriber.remove_tag.wording', [
                    'count' => number_with_delimiter($subscribers->count(), $precision = 0),
                ]) !!}</p>
                    
                    @include('helpers.form_control', [
                        'type' => 'select_tag',
                        'class' => '',
                        'label' => '',
                        'name' => 'tags[]',
                        'value' => [],
                        'options' => [],
                        'rules' => ['tags' => 'required'],
                        'multiple' => 'true',
                        'placeholder' => trans('messages.subscriber.remove_tag.choose_tags'),
                    ])

                <div class="mt-4 pt-3">
                    <button class="btn btn-secondary">{{ trans('messages.save') }}</button>
                </div>
        </div>
    </div>
    
    <script>
        $(() => {
            new RemoveTagManager({
                form: $('#RemoveTagForm'),
            });
        });

        var RemoveTagManager = class {
            constructor(options) {
                this.form = options.form;

                this.events();
            }

            events() {
                this.form.on('submit', (e) =>  {
                    e.preventDefault();

                    this.submit();
                });
            }

            submit() {
                var data = this.form.serialize();
                var url = this.form.attr('action');
                
                addMaskLoading('{{ trans('messages.subscriber.remove_tag.loading') }}');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: data,
                    globalError: false,
                    statusCode: {
                        // validate error
                        400: function (res) {
                            addTagManager.popup.loadHtml(res.responseText);

                            // remove masking
                            removeMaskLoading();
                        }
                    },
                    success: function (res) {
                        // hide popup
                        removeTagManager.popup.hide();

                        // notify
                        notify('success', '{{ trans('messages.notify.success') }}', res.message);

                        // remove masking
                        removeMaskLoading();

                        // reload list
                        SubscribersIndex.getList().load();
                    }
                });  
            }
        }

        $('#RemoveTagForm').submit(function(e) {
            e.preventDefault();
            
            var form = $(this);
            var data = form.serialize();
            var url = form.attr('action');
            
            addMaskLoading('{{ trans('messages.subscriber.remove_tag.loading') }}');

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                globalError: false,
                statusCode: {
                    // validate error
                    400: function (res) {
                        addTagManager.popup.loadHtml(res.responseText);

                        // remove masking
                        removeMaskLoading();
                    }
                },
                success: function (res) {
                    // hide popup
                    addTagManager.popup.hide();

                    // notify
                    notify('success', '{{ trans('messages.notify.success') }}', res.message);

                    // remove masking
                    removeMaskLoading();

                    // reload list
                    SubscribersIndex.getList().load();
                }
            });    
        });
    </script>
@endsection
