<?php

/*** Child Theme Function  ***/
if (!function_exists('depot_mikado_child_theme_enqueue_scripts')) {
  function depot_mikado_child_theme_enqueue_scripts() {
    wp_enqueue_style('depot-mikado-child-style',
        get_stylesheet_directory_uri() . '/style.css');
    wp_enqueue_script('depot-mikado-child-script',
        get_stylesheet_directory_uri() . '/main.js',
        array('woo-variation-gallery'), false, true);
  }

  // The parent depot enqueues many stylesheets, to ensure we load the latest, assign a higher
  // priority (the lower the earlier).
  add_action('wp_enqueue_scripts', 'depot_mikado_child_theme_enqueue_scripts', 99);
}

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
// Remove default unselect variation option
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
 * @param $classString The string containing the all the classes
 * @param $classNameToRemove The class to be removed.
 *
 * @return The updated class string with the specified name removed.
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
 * Returns false to indicate product is unique.
 *
 * @return bool Always return false
 */
function wp_le_wc_product_has_unique_sku() {
  return false;
}

function wp_le_console_log_var($var) {
  wp_le_console_log(print_r($var, true));
}

function wp_le_console_log($message) {
  echo "<script>console.log(`{$message}`)</script>";
}

