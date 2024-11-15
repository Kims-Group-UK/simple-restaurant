<?php
/**
 * SimpleRestaurant setup
 *
 * @package SimpleRestaurant
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main SimpleRestaurant Class.
 *
 * @class   SimpleRestaurant
 * @version 1.0.0
 */
final class SimpleRestaurant {

	/**
	 * SimpleRestaurant version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * The single instance of the class.
	 *
	 * @var   SimpleRestaurant
	 * @since 1.0.0
	 */
	protected static $instance = null;


    /**
	 * Main SimpleRestaurant Instance.
	 *
	 * Ensures only one instance of SimpleRestaurant is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 * @see    SR()
	 * @return SimpleRestaurant - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

    /**
	 * SimpleRestaurant Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'objects' ), 1 );
		do_action( 'simple_restaurant_loaded' );
	}

    /**
	 * Setup objects.
	 *
	 * @since      1.0.0
	 */
	public function objects() {
		// Global objects.
	}
}