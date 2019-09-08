<?php
// List of IDs of vendors who don't need ship to transfer.
$direct_vendor_ids = [];

function wp_le_console_log_var($var) {
  wp_le_console_log(print_r($var, true));
}

function wp_le_console_log($message) {
  echo "<script>console.log(`{$message}`)</script>";
}

function wp_le_is_direct_vendor($vendor_id = '') {
  if (current_user_can('administrator')) {
    return true;
  }
  if (!wcfm_is_vendor($vendor_id)) {
    return false;
  }
  if (!$vendor_id) {
    $vendor_id = get_current_user_id();
  }
  global $direct_vendor_ids;
  return in_array($vendor_id, $direct_vendor_ids);
}

function is_brand_page() {
  return is_tax('product_brand');
}