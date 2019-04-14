<?php

/*** Child Theme Function  ***/

if ( ! function_exists( 'depot_mikado_child_theme_enqueue_scripts' ) ) {
    function depot_mikado_child_theme_enqueue_scripts() {
        wp_enqueue_style( 'depot-mikado-child-style', get_stylesheet_directory_uri() . '/style.css' );
    }

    // The parent depot enqueues many stylesheets, to ensure we load the latest, assign a higher
    // priority (the lower the earlier).
    add_action( 'wp_enqueue_scripts', 'depot_mikado_child_theme_enqueue_scripts', 99 );
}

//Remove the additional information tab from the product page
add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );

function woo_remove_product_tabs( $tabs ) {

    unset( $tabs['additional_information'] );

    return $tabs;
}

//Hide the virtual and downloadable boxes from the vendor's manager
function wcfm_custom_product_manage_fields_general( $general_fileds, $product_id, $product_type ) {
    global $WCFM;
    if( isset( $general_fileds['is_virtual'] ) ) {
        $general_fileds['is_virtual']['class'] = 'wcfm_custom_hide';
        $general_fileds['is_virtual']['desc_class'] = 'wcfm_custom_hide';
    }
    if( isset( $general_fileds['is_downloadable'] ) ) {
        $general_fileds['is_downloadable']['class'] = 'wcfm_custom_hide';
        $general_fileds['is_downloadable']['desc_class'] = 'wcfm_custom_hide';
    }

    return $general_fileds;
}
add_filter( 'wcfm_product_manage_fields_general', 'wcfm_custom_product_manage_fields_general', 150, 3 );