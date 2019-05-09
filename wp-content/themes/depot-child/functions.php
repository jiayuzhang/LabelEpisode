<?php

//====================================================
// Enqueue scripts & styles
//====================================================
add_action('wp_enqueue_scripts', 'wp_le_depot_child_theme_enqueue_scripts', 99);
function wp_le_depot_child_theme_enqueue_scripts() {
  wp_enqueue_style('wp-le-depot-child-theme-style',
      get_stylesheet_directory_uri() . '/style.css');
  wp_enqueue_script('wp-le-depot-child-theme-script-main',
      get_stylesheet_directory_uri() . '/main.js',
      array('woo-variation-gallery'), false, true);
}

add_action('admin_enqueue_scripts', 'wp_le_depot_child_theme_admin_enqueue_scripts', 99);
function wp_le_depot_child_theme_admin_enqueue_scripts($hook) {
  if ($hook === 'post.php') {
    wp_enqueue_script('wp-le-depot-child-theme-admin-script-post',
      get_stylesheet_directory_uri() . '/assets/js/admin-post.js',
      array('jquery'), false, true);
  }
}
//====================================================
// End of Enqueue scripts & styles
//====================================================

//Remove the additional information tab from the product page
function wp_le_woocommerce_product_tabs($tabs) {
  unset($tabs['additional_information']);
  return $tabs;
}

add_filter('woocommerce_product_tabs', 'wp_le_woocommerce_product_tabs', 99);

//====================================================
// Integration woo-variation-gallery and wc-frontend-manager
//====================================================

// Add additional gallery when loading manage-product page
function wp_le_wcfm_variation_edit_data($variations, $variation_id, $variation_id_key) {
  // NOTE: $variations->get_image_id returns product feature image if variation image misses
  $variation_gallery_images =
      get_post_meta($variation_id, 'woo_variation_gallery_images', true);
  if ($variation_gallery_images) {
    foreach ($variation_gallery_images as $key => $variation_gallery_img_id) {
      $variation_gallery_images[$key] =
          array('image' => wp_get_attachment_url($variation_gallery_img_id));
    }
    $variations[$variation_id_key]['gallery_images'] = $variation_gallery_images;
  }
  return $variations;
}

add_filter('wcfm_variation_edit_data', 'wp_le_wcfm_variation_edit_data', 99, 3);

// Add additional gallery into the variations form on manage-product page
function wp_le_wcfm_product_manage_fields_variations($options, $variations) {
  // Variation additional gallery (exclude variation featured image)
  // To preserve the display order of options in a form, add a new option
  // `gallery_images` right after `image` (The variation featured image)
  $idx = array_search('image', array_keys($options));
  $offset = $idx + 1;
  return array_slice($options, 0, $offset, true) + array('gallery_images' => array(
          'type' => 'multiinput',
          'class' => 'le-variation-gallery wcfm-text wcfm_ele variable variable-subscription wcfm_additional_variation_images',
          'options' => array(
              'image' => array(
                  'type' => 'upload',
                  'class' => 'le-variation-gallery-item wcfm_gallery_upload',
                  'prwidth' => 75
              )
          )
      )) + array_slice($options, $offset, null, true);
}

add_filter('wcfm_product_manage_fields_variations',
    'wp_le_wcfm_product_manage_fields_variations', 99, 3);

// Store additional gallery into post metadata (used by woo-variation-gallery)
function wp_le_after_wcfm_product_variation_meta_save($new_product_id, $variation_id,
    $variations) {
  global $WCFM;
  if (isset($variations['gallery_images'])) {
    // gallery_images is an array of array(image=>img_url)
    // Exclude array items not having img_url
    $variation_gallery_image_ids = [];
    foreach ($variations['gallery_images'] as $variation_img) {
      if (!empty($variation_img['image'])) {
        $variation_gallery_image_ids[] =
            $WCFM->wcfm_get_attachment_id($variation_img['image']);
      }
    }
    if (!empty($variation_gallery_image_ids)) {
      update_post_meta($variation_id, 'woo_variation_gallery_images',
          $variation_gallery_image_ids);
    } else {
      delete_post_meta($variation_id, 'woo_variation_gallery_images');
    }
  } else {
    delete_post_meta($variation_id, 'woo_variation_gallery_images');
  }
}

add_action('after_wcfm_product_variation_meta_save',
    'wp_le_after_wcfm_product_variation_meta_save', 99, 3);

//====================================================
// End of Integration woo-variation-gallery and wc-frontend-manager
//====================================================


//====================================================
// Remove default unselect variation option
//====================================================

// Inspired by plugin 'force-default-variant-for-woocommerce'

// Remove 'Choose an option' from dropdown
function wp_le_woocommerce_dropdown_variation_attribute_options_html($html, $args) {
  if (empty($args['selected'])) {
    return $html;
  }

  $show_option_none_text = $args['show_option_none']
      ? $args['show_option_none']
      :
      __('Choose an option', 'woocommerce');
  $show_option_none_html =
      '<option value="">' . esc_html($show_option_none_text) . '</option>';

  $html = str_replace($show_option_none_html, '', $html);

  return $html;
}

add_filter('woocommerce_dropdown_variation_attribute_options_html',
    'wp_le_woocommerce_dropdown_variation_attribute_options_html', 99, 2);

// Returns the default variation option. If not set, choose the first variation id
function wp_le_woocommerce_product_get_default_attributes($defaults) {
  global $product;

  if (!$product) {
    return $defaults;
  }

  if (!empty($defaults)) {
    return $defaults;
  }

  if ($product->post_type !== 'product') {
    return $defaults;
  }

  if (!$product->is_type('variable')) {
    return $defaults;
  }

  // Sorts all child product variation ids, PHP copies array by default
  $children = $product->get_children();
  sort($children);

  // Gets the first child product variation (if any)
  $variation = wc_get_product($children[0]);
  if (empty($variation)) {
    return $defaults;
  }

  $attr = $variation->get_attributes();
  $defaults = array();
  foreach ($attr as $key => $value) {
    $defaults[$key] = $value;
  }
  return $defaults;
}

add_filter('woocommerce_product_get_default_attributes',
    'wp_le_woocommerce_product_get_default_attributes', 99, 1);

// Remove the Clear selection link.
function wp_le_woocommerce_reset_variations_link($value) {
  return '';
}

add_filter('woocommerce_reset_variations_link', 'wp_le_woocommerce_reset_variations_link',
    99, 1);
//====================================================
// End of Remove default unselect variation option
//====================================================

/** Remove inventory tab from variable product. */
add_filter('wcfm_pm_block_class_stock', 'wp_le_wcfm_pm_block_class_stock', 20, 1);
function wp_le_wcfm_pm_block_class_stock($classes) {
  return removeCssClass($classes, 'variable');
}

/** Remove attribute tab from simple product. */
add_filter('wcfm_pm_block_class_attributes', 'wp_le_wcfm_pm_block_class_attributes', 20,
    1);
function wp_le_wcfm_pm_block_class_attributes($classes) {
  return removeCssClass($classes, 'simple');
}

/**
 * Removes the specified $classNameToRemove from the classString.
 * e.g. classString = 'abc def ghi', classNameToRemove = def, result = 'abc ghi'
 *
 * @param string $classString The string containing the all the classes
 * @param string $classNameToRemove The class to be removed.
 *
 * @return string The updated class string with the specified name removed.
 */
function removeCssClass($classString, $classNameToRemove) {
  $chunk = preg_split('/\s/', $classString);
  $chunk = array_filter($chunk, function ($element) use ($classNameToRemove) {
    if ($element == $classNameToRemove) {
      return false;
    }
    return true;
  });

  return implode(' ', $chunk);
}

add_filter('wc_product_has_unique_sku', 'wp_le_wc_product_has_unique_sku', 20, 1);


/**
 * @return bool True if there's an existing SKU for the new product.
 */
function wp_le_wc_product_has_unique_sku($sku_found) {
  // Returns false to make the system think product has unique sku - no conflict with existing SKU.
  // This is to skip the uniqueness checks/validations.
  return false;
}

function wp_le_console_log_var($var) {
  wp_le_console_log(print_r($var, true));
}

function wp_le_console_log($message) {
  echo "<script>console.log(`{$message}`)</script>";
}

//====================================================
// Add "Order notes" in WCFM order details view
//====================================================
add_action('after_wcfm_load_scripts', 'wp_le_after_wcfm_load_scripts', 99, 1);
function wp_le_after_wcfm_load_scripts($endpoint) {
  if ($endpoint === 'wcfm-orders-details') {
    global $wp;
    wp_enqueue_script('depot-mikado-child-wcfm-script',
      get_stylesheet_directory_uri() . '/assets/js/wcfm-orders-details.js',
      array('jquery', 'wcfm_core_js'), false, true);
    wp_localize_script('depot-mikado-child-wcfm-script', 'labelepisode_ajax',
      array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'order_id' => $wp->query_vars['orders-details'],
        'security' => wp_create_nonce('labelepisode')
      )
    );
  }
}

add_action('wp_ajax_wp_le_ajax_vendor_add_order_notes', 'wp_le_ajax_vendor_add_order_notes');
function wp_le_ajax_vendor_add_order_notes() {
  check_ajax_referer('labelepisode', 'security');

  $order_id = absint($_POST['order_id']);
  $note = wp_kses_post( trim(wp_unslash($_POST['note'])));
  if ($order_id > 0) {
    $order = wc_get_order($order_id);
    $comment_id = $order->add_order_note($note, false, false);
    $note = wc_get_order_note($comment_id);
    wp_le_render_order_note($note);
  }
  wp_die();
}

// Set the comment's author and author_email to vendor if the note is
// added from vendor's store-manager page.
add_filter('woocommerce_new_order_note_data', 'wp_le_woocommerce_new_order_note_data', 99, 1);
function wp_le_woocommerce_new_order_note_data($commentdata) {
  if ($_POST['note_type'] === 'vendor') {
    if (wcfm_is_vendor()) {
      $user = wp_get_current_user();
      $commentdata['comment_author'] = $user->display_name;
      $commentdata['comment_author_email'] = $user->user_email;
      $commentdata['comment_author_url'] = '';
    }
  }
  return $commentdata;
}

// The `note_type` is only used by WC to determine if a comment is for
// customer. We will check the actual `note_type` in HTTP POST body to
// flip `is_vendor_note` bit in metadata accordingly.
add_action('wp_insert_comment', 'wp_le_wp_insert_comment', 99, 2);
function wp_le_wp_insert_comment($id, $comment) {
  if ($_POST['note_type'] === 'vendor') {
    add_comment_meta($id, 'is_vendor_note', 1);
  }
}

add_action('end_wcfm_orders_details', 'wp_le_end_wcfm_orders_details', 99, 1);
function wp_le_end_wcfm_orders_details($order_id) {
  // Inspired from woocommerce function wc_get_order_notes
  $args = array('order_id' => $order_id);
  $is_current_vendor = wcfm_is_vendor();
  if ($is_current_vendor) {
    $args['type'] = 'vendor';
    $args['meta_query'] = array(
      array(
        'key' => 'is_vendor_note',
        'value' => 1,
        'compare' => '='
      )
    );
  }
  $notes = wc_get_order_notes($args);
  ?>
  <div class="wcfm-clearfix"></div><br/>
  <!-- collapsible -->
  <div class="page_collapsible wc_dhl_shipping" id="wcfm_wc_dhl_shipping_options">
    <?php _e('Order notes', 'wc-frontend-manager'); ?>
  </div>
  <div class="wcfm-container">
    <div id="wp_le_wcfm_order_notes" class="wcfm-content">
      <ul>
      <?php
      if (!empty($notes)) {
        foreach ($notes as $note) {
          wp_le_render_order_note($note);
        }
      } else {
        echo '<li>' . __( 'There are no notes yet.', 'woocommerce' ) . '</li>';
      }

      if ($is_current_vendor) {
      ?>
        <li id="le_wcfm_add_note">
          <textarea type="text" name="order_note" class="input-text" cols="20" rows="10"></textarea>
          <button type="button" class="le-wcfm-add-note-button wcfm_add_attribute button"><?php _e( 'Add note', 'woocommerce' ); ?></button>
        </li>
      <?php } else { ?>
        <!-- Leave a message on store-manager page for Administrator -->
        <li>
          <p>Administrator should manage order notes in admin panel.</p>
        </li>
      <?php } ?>
      </ul>
    </div>
  </div>
  <!-- end collapsible -->
  <?php
}

function wp_le_render_order_note($note) {
?>
<li rel="<?php echo absint($note->id); ?>">
  <div class="le-wcfm-note-content">
    <?php echo wpautop(wptexturize(wp_kses_post($note->content))); ?>
  </div>
  <p class="meta">
    <abbr class="exact-date" title="<?php echo $note->date_created->date('y-m-d h:i:s'); ?>">
    <?php printf(__('added on %1$s at %2$s', 'woocommerce'),
      $note->date_created->date_i18n(wc_date_format()),
      $note->date_created->date_i18n(wc_time_format())); ?>
    </abbr>
    <?php
    if ('system' !== $note->added_by) :
      /* translators: %s: note author */
      printf(' ' . __('by %s', 'woocommerce'), $note->added_by);
    endif;
    ?>
  </p>
</li>
<?php
}
//====================================================
// End of Add "Order notes" in WCFM order details view
//====================================================


//====================================================
// Disable "virtual", "downloadable", "schedule", DRAFT, "Add attributes" from WCFM add product
//====================================================
add_filter('wcfm_product_manage_fields_general', 'wp_le_wcfm_product_manage_fields_general', 99, 1);
function wp_le_wcfm_product_manage_fields_general($general_fields) {
  unset($general_fields['is_virtual']);
  unset($general_fields['is_downloadable']);
  return $general_fields;
}

add_filter('wcfm_product_manage_fields_pricing', 'wp_le_wcfm_product_manage_fields_pricing', 99, 1);
function wp_le_wcfm_product_manage_fields_pricing($fields) {
  unset($fields['sale_price']['desc']);
  return $fields;
}

add_filter('wcfm_is_allow_draft_published_products', 'wp_le_wcfm_is_allow_draft_published_products', 99);
function wp_le_wcfm_is_allow_draft_published_products() {
  return false;
}

add_filter('wcfm_is_allow_add_attribute', 'wp_le_wcfm_is_allow_add_attribute', 99);
function wp_le_wcfm_is_allow_add_attribute() {
  return false;
}
//====================================================
// End of Disable "virtual", "downloadable", "schedule", DRAFT, "Add attributes" from WCFM add product
//====================================================

