/* MailClick Real-time Checkout & Cart Tracking */
jQuery(document).ready(function($) {
    var lastCapturedEmail = '';

    function triggerCartCapture(email, phone) {
        if (!email) return;
        localStorage.setItem('mailclick_user_email', email);
        if (email === lastCapturedEmail) return;
        lastCapturedEmail = email;

        $.post(mailclick_vars.ajax_url, {
            action: 'mailclick_track_cart',
            nonce: mailclick_vars.nonce,
            email: email,
            phone: phone || ''
        });
    }

    // Capture on email field blur or typing on checkout page
    $(document).on('change blur input', 'input[type="email"], #billing_email', function() {
        var email = $(this).val();
        var phone = $('#billing_phone').val() || '';
        if (email && email.indexOf('@') > 0) {
            localStorage.setItem('mailclick_user_email', email);
            triggerCartCapture(email, phone);
        }
    });

    // Capture on Add to Cart form submission
    $('form.cart').on('submit', function() {
        var email = $('input[name="billing_email"]').val() || $('#billing_email').val();
        if (email) {
            triggerCartCapture(email, '');
        }
    });
});
