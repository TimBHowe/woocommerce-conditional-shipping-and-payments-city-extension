<?php
/**
 * @package  WooCommerce Conditional Shipping and Payments - City Extension
 *
 * Plugin Name: WooCommerce Conditional Shipping and Payments - City/Suburb Extension
 * Plugin URI: https://woocommerce.com/products/woocommerce-conditional-shipping-and-payments
 * Description: Extends the WooCommerce Conditional Shipping and Payments to allow conditional logic on the City/Suburb field to check if they do or don't contain specified strings. This is helpful for limiting shipping and payments to cities/suburbs in the same postal code as others. ect...
 * Version: 1.0.0
 * Author: Woo, CrossPeak, TimBHowe
 * Author URI: https://woocommerce.com/
 * Text Domain: woocommerce-conditional-shipping-and-payments-city-extension
 *
 * Domain Path: /languages/
 *
 * Requires PHP: 7.4
 *
 * Requires at least: 6.2
 * Tested up to: 6.7
 *
 * WC requires at least: 8.2
 * WC tested up to: 9.7
 *
 * Requires Plugins: woocommerce, woocommerce-conditional-shipping-and-payments
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace WC_CSP_CITY_EXT;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Make sure WooCommerce and CSP are active
 *
 * @return bool
 */
function wc_csp_ext_check_dependencies() {
	if ( ! class_exists( 'WC_Conditional_Shipping_Payments' ) || ! class_exists( 'WC_CSP_Package_Condition' ) ) {
		add_action( 'admin_notices', 'wc_csp_ext_dependency_notice' );
		return false;
	}
	return true;
}

/**
 * Display notice if dependencies aren't met.
 *
 * @return void
 */
function wc_csp_ext_dependency_notice() {
	echo '<div class="error"><p>Your Custom CSP Condition requires both WooCommerce and Conditional Shipping and Payments to be installed and activated.</p></div>';
}

/**
 * Add additional WC CSP class
 *
 * @param array $conditions An array fo the conditional logic classes.
 * @return array
 */
function wc_csp_ext_register_condition( $conditions ) {

	if ( ! wc_csp_ext_check_dependencies() ) {
		return;
	}

	// Include Shipping Address condition class.
	include_once 'includes/class-wc-csp-condition-billing-city.php';

	$conditions[] = 'WC_CSP_Condition_Billing_City';

	// Include Shipping Address condition class.
	include_once 'includes/class-wc-csp-condition-shipping-city.php';

	$conditions[] = 'WC_CSP_Condition_Shipping_City';

	return $conditions;
}
add_filter( 'woocommerce_csp_conditions', __NAMESPACE__ . '\wc_csp_ext_register_condition', 10, 1 );
