<?php

/**
 *
 * @link              http://www.iris-studio.es
 * @since             0.0.1
 * @package           Wanna_Product_Reminder_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Wanna Product Reminder for WooCommerce
 * Plugin URI:        https://wordpress.org/support/plugin/wanna-product-reminder-woocommerce
 * Description:       Add removed products to Cart page, so the customer can be remembered previously added to cart products.
 * Version:           0.0.1
 * Author:            jjmrestituto
 * Author URI:        http://www.iris-studio.es
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wanna-product-reminder-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'woocommerce_after_cart_table', 'wanna_reminder_title', 10 );
add_action( 'woocommerce_after_cart_table', 'wanna_reminder_show_cart', 20 );
add_action( 'woocommerce_add_to_cart', 'wanna_reminder_item_added', 10, 2 );
add_action( 'woocommerce_cart_item_removed', 'wanna_reminder_item_removed', 10, 2 );

function wanna_reminder_show_cart() {
	echo wanna_get_items_cart();
}

function wanna_reminder_title() {
	_e( '<h2>Previously added to cart items:</h2>', '' );
}

function wanna_get_items_cart() {
	$items_cookie = $_COOKIE['wanna_removed_items'];
	$items = array();
	if( null != $items_cookie ) {
		$items = stripslashes($items_cookie);
		$items = str_replace(array("[","]","\""),"",$items);
		return do_shortcode('[products ids="'.$items.'"]');
	} else {
		return null;
	}		
}

function wanna_reminder_item_added() {
	$product_id = (int)$_POST["add-to-cart"];
	$value_string = strval($product_id);
	$items_cookie = $_COOKIE['wanna_removed_items'];
	if( null != $items_cookie ) {
		$items_cookie = stripslashes($items_cookie);
		//Delete item from cookie
		$cookie_array = json_decode( $items_cookie, true );
	    if( in_array( $value_string, $cookie_array ) ){
	    	foreach ( array_keys( $cookie_array, $value_string ) as $key ) {
			    unset( $cookie_array[ $key ] );
			}
			$cookie_json = array_values( $cookie_array );
			$removed_items_array = json_encode( $cookie_json );
	    }
	}
	wc_setcookie( 'wanna_removed_items', $removed_items_array );
}

function wanna_reminder_item_removed( $cart_item_key, $instance ) { 
	global $woocommerce;	

	//Get Removed Product ID
	$items = $woocommerce->cart->removed_cart_contents;
	$arr = json_decode(json_encode($items), true);
	$value = $arr[$cart_item_key]['product_id'];
	    
    //Get Cookie 
    $items_cookie = $_COOKIE['wanna_removed_items'];
    if( null == $items_cookie ) {
    	$items_cookie = array();
		$items_cookie = json_encode( $items_cookie );
    }
	$items_cookie = stripslashes($items_cookie);

    //Add New Removed Item Data to Array
	$cookie_json = json_decode($items_cookie);
	$value_string = strval($value);
	
	//Delete item from cookie
	$cookie_array = json_decode( $items_cookie, true );
    if( in_array( $value_string, $cookie_array ) ){
    	foreach ( array_keys( $cookie_array, $value_string ) as $key ) {
		    unset( $cookie_array[ $key ] );
		}
		$cookie_json = array_values( $cookie_array );
    }

    $items_cookie_a = array_push($cookie_json, $value_string);
	$removed_items_array = json_encode( $cookie_json );

	//Add cookie
    wc_setcookie( 'wanna_removed_items', $removed_items_array );
}
