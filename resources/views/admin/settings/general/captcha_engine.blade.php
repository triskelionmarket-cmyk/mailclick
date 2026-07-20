<div class="form-group">
    <label class="fw-semibold">{{ trans('messages.captcha_engine') }}</label>
    @include('helpers.form_control.select', [
        'name' => 'general[captcha_engine]',
        'value' => Acelle\Model\Setting::get('captcha_engine'),
        'options' =>  array_map(function ($cap) {
            return ['value' => $cap['id'], 'text' => $cap['title']];
        }, \Acelle\Library\Facades\Hook::execute('captcha_method')),
        'attributes' => [
            'required' => 'required',
        ],
    ])
</div>

<div data-control="captcha-keys">
    <div class="form-group">
        <label class="fw-semibold">{{ trans('messages.recaptcha.site_key') }}</label>
        @include('helpers.form_control.text', [
            'name' => 'general[recaptcha_site_key]',
            'value' => (\Acelle\Model\Setting::get('recaptcha_site_key','') == '' ? config('app.recaptcha_sitekey') : \Acelle\Model\Setting::get('recaptcha_site_key','')),
        ])
    </div>

    <div class="form-group">
        <label class="fw-semibold">{{ trans('messages.recaptcha.secret_key') }}</label>
        @include('helpers.form_control.text', [
            'name' => 'general[recaptcha_secret_key]',
            'value' => (\Acelle\Model\Setting::get('recaptcha_secret_key','') == '' ? config('app.recaptcha_secret') : \Acelle\Model\Setting::get('recaptcha_secret_key','')),
        ])
    </div>

    <div class="alert alert-info">
        {{ trans('messages.recaptcha.keys.warning') }}
    </div>
</div>