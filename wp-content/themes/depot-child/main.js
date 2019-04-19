jQuery(function ($) {
  $(document).one('woo_variation_gallery_init', function(event, wooVariationGallery) {
    if (wooVariationGallery.is_variation_product) {
      wooVariationGallery.$variations_form.trigger('check_variations.wc-variation-form');
    }
  });
});
