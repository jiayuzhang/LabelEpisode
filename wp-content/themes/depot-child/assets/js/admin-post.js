jQuery(function ($) {
  // Add 'Note to vendor' option in 'Order notes' select
  const orderNoteType = $('#order_note_type');
  if(orderNoteType.length) {
    if (!orderNoteType.find('option[value="vendor"]').length) {
      $('<option value="vendor">Note to vendor</option>').appendTo(orderNoteType);
    }
  }
});
