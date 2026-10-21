jQuery(function ($) {
    $('.variations_form').each(function () {
        var $form = $(this);
        var $wrap = $form.closest('.product').find('.soft79-single-product-discount-wrap').first();
        var original = $wrap.html();
        $form.on('found_variation.soft79', function (event, variation) {
            $wrap.html(typeof variation.j79_soft79_single_product_discount === 'string' ? variation.j79_soft79_single_product_discount : '');
        });
        $form.on('reset_data.soft79 hide_variation.soft79', function () {
            $wrap.html(original);
        });
    });
});
