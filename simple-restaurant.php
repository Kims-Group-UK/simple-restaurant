<?php
/**
 * Plugin Name: Simple Restaurant
 * Plugin URI: https://milanmalla.com/simple-restaurant
 * Description: Create Restaurant Menu, Locations, Gallery and Career pages and Many More with Ease.
 * Version: 1.0.0
 * Author: Milan Malla
 * Author URI: https://milanmalla.com
 * Text Domain: simple-restaurant
 * Domain Path: /languages/
 *
 * @package SimpleRestaurant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define EVF_PLUGIN_FILE.
if ( ! defined( 'SR_PLUGIN_FILE' ) ) {
	define( 'SR_PLUGIN_FILE', __FILE__ );
}

// Include the main SimpleRestaurant class.
if ( ! class_exists( 'SimpleRestaurant' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-simple-restaurant.php'; // phpcs:ignore
}

/**
 * Main instance of SimpleRestaurant.
 *
 * Returns the main instance of SR to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return SimpleRestaurant
 */
function sr() {
	return SimpleRestaurant::instance();
}

// Global for backwards compatibility.
$GLOBALS['simple-restaurant'] = sr();