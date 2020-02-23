<?php
/*
Plugin Name: Spanyol Apartmanok email
Version: 1.0.2
Description: Spanyol Apartmanok email
Author: OnlineVagyok
Author URI: https://onlinevagyok.hu
*/

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}


add_action( 'woocommerce_product_options_general_product_data', 'sae_woo_custom_fields' );
/**
* Add a select Field at the bottom
*/
function sae_woo_custom_fields() {
  $field = array(
    'id' => 'sae_custom_field',
    'label' => __( 'Szállásadó email címe', 'textdomain' ),

  );
  woocommerce_wp_text_input( $field );
}

add_action( 'woocommerce_process_product_meta', 'sae_save_custom_field' );

function sae_save_custom_field( $post_id ) {
  // Tertiary operator
  // kérdés ? igaz : hamis
  $custom_field_value = isset( $_POST['sae_custom_field'] ) ? $_POST['sae_custom_field'] : '';

  update_post_meta($post_id, 'sae_custom_field', $custom_field_value);

}
add_filter('woocommerce_email_recipient_new_booking', 'sae_change_admin_new_order_email_recipient', 1, 2);
// Change new order email recipient for registered customers
function sae_change_admin_new_order_email_recipient( $recipient, $object ) {

  
  if (!isset($object)) {
    return $recipient;
  }

  $order = $object->get_order();
  $items = $order->get_items();


  $product_emails = array();
  
  foreach ( $items as $item ) {
    $product_id = $item->get_product_id();

    // Post meta lekérés
    // get_post_meta($post_id, $meta_key, $meta_value);
    $product_email_er = get_post_meta($product_id, 'sae_custom_field', true); // $product_email_er a mezőbe írt érték

    $product_email_er = str_replace(" ", "", $product_email_er); // kiszedjük  " "-t
    $product_email_ers = explode(",",$product_email_er); //áttesszük tömbbe

    foreach ($product_email_ers as $product_email) {

        if (isset($product_email) && is_email($product_email)) {
            if(!in_array($product_email, $product_emails, true)){
                array_push($product_emails, $product_email);
                $recipient .= ",$product_email";
            }
        }
    }
  }

  return $recipient;
}