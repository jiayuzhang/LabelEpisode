jQuery(function ($) {
  $('#le_wcfm_add_note').click(function() {
    const $this = $(this);
    const content = $this.find('textarea').val();
    if (!content || !content.trim()) {
      return;
    }

    const data = {
      action: 'wp_le_ajax_vendor_add_order_notes',
      note: content,
      order_id: labelepisode_ajax.order_id,
      security: labelepisode_ajax.security,
    };

    $('#wp_le_wcfm_order_notes').block({
      message: null,
      overlayCSS: {
        background: '#fff',
        opacity: 0.6
      }
    });

    $.post(labelepisode_ajax.ajax_url, data, function(response) {
      $('#wp_le_wcfm_order_notes ul').prepend(response);
      $this.find('textarea').val('');
      $('#wp_le_wcfm_order_notes').unblock();
    });
  });
});
