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
final class Location {
		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
	public function __construct() {
		// Hook the function to the 'init' action to ensure the custom post type is registered.
		add_action( 'init', array( $this, 'sr_location_post_type' ) );
		add_filter( 'manage_edit-sr-location_columns', array( $this, 'sr_location_add_columns' ) );
	}

	/**
	 * Location
	 *
	 * @since 1.0.0
	 */
	public function sr_location_post_type() {
		$labels = array(
			'name'               => _x( 'Locations', 'post type general name', 'simple-restaurant' ),
			'singular_name'      => _x( 'Location', 'post type singular name', 'simple-restaurant' ),
			'location_name'      => _x( 'Locations', 'admin location', 'simple-restaurant' ),
			'name_admin_bar'     => _x( 'Location', 'add new on admin bar', 'simple-restaurant' ),
			'add_new'            => _x( 'Add New', 'location', 'simple-restaurant' ),
			'add_new_item'       => __( 'Add New Location', 'simple-restaurant' ),
			'new_item'           => __( 'New Location', 'simple-restaurant' ),
			'edit_item'          => __( 'Edit Location', 'simple-restaurant' ),
			'view_item'          => __( 'View Location', 'simple-restaurant' ),
			'all_items'          => __( 'All Locations', 'simple-restaurant' ),
			'search_items'       => __( 'Search Locations', 'simple-restaurant' ),
			'parent_item_colon'  => __( 'Parent Locations:', 'simple-restaurant' ),
			'not_found'          => __( 'No locations found.', 'simple-restaurant' ),
			'not_found_in_trash' => __( 'No locations found in Trash.', 'simple-restaurant' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_location'   => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'sr-location' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'location_position'  => 20,
			'supports'           => array( 'title', 'editor', 'author' ),
			'show_in_rest'       => true, // Enables Gutenberg editor.
			'show_in_menu'       => false, // Nest under the main menu.
		);

		register_post_type( 'sr-location', $args );
	}



	/**
	 * Add Colu8mns.
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed $columns Columns.
	 */
	public function sr_location_add_columns( $columns ) {
		// Remove the Comments column.
		unset( $columns['comments'] );
		unset( $columns['date'] ); // Remove it from its original position.
		$columns['date'] = $date_column; // Add it to the end.
		return $columns;
	}
}
