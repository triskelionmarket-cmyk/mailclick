jQuery(document).ready(function ($) {
    if (typeof mailclick_product_vars === 'undefined') {
        return;
    }

    var email = mailclick_product_vars.user_email || localStorage.getItem('mailclick_user_email') || '';

    if (!email) {
        return;
    }

    // Send product view event
    $.ajax({
        url: mailclick_product_vars.ajax_url,
        type: 'POST',
        data: {
            action: 'mailclick_track_product_view',
            nonce: mailclick_product_vars.nonce,
            email: email,
            product_id: mailclick_product_vars.product_id,
            product_title: mailclick_product_vars.product_title,
            price: mailclick_product_vars.price,
            page_url: mailclick_product_vars.page_url
        }
    });
});
