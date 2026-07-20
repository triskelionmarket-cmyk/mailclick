@if (isset($errors) && $errors->has('recaptcha_invalid'))
    <span class="help-block text-danger">
        <strong>{{ $errors->first('recaptcha_invalid') }}</strong>
    </span>
@endif

<input type="hidden" name="g-recaptcha-response" value="" />

<script src="https://www.google.com/recaptcha/enterprise.js?render={{ Acelle\Model\Setting::get('recaptcha_site_key') }}"></script>
<script>
    $(() => {
        var reCaptchaForm = $('[name="g-recaptcha-response"]').closest('form');
        reCaptchaForm.on('submit', function(e) {
            if ($('[name="g-recaptcha-response"]').val() == "") {
                grecaptcha.enterprise.ready(async () => {
                    try {
                        const token = await grecaptcha.enterprise.execute('{{ Acelle\Model\Setting::get('recaptcha_site_key') }}', {action: 'LOGIN'});
                        console.log(token);

                        $('[name="g-recaptcha-response"]').val(token);

                        // submit again
                        reCaptchaForm.submit();
                    } catch (error) {
                        console.log(error);
                        alert('Error during reCAPTCHA execution:' + error);
                    }
                });

                e.preventDefault();
                return false;
            }
        });
    });
</script>