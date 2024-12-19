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
final class Menu {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Hook the function to the 'init' action to ensure the custom post type is registered.
		add_action( 'init', array( $this, 'sr_menu_post_type' ) );
		add_action( 'init', array( $this, 'sr_menu_taxonomies' ) );
		add_filter( 'manage_edit-sr-menu_columns', array( $this, 'sr_menu_add_columns' ) );
		add_action( 'manage_sr-menu_posts_custom_column', array( $this, 'sr_menu_populate_columns' ), 10, 2 );
	}

	/**
	 * Restaurant menu.
	 *
	 * @since 1.0.0
	 */
	public function sr_menu_post_type() {
		// Labels for the custom post type.
		$labels = array(
			'name'               => _x( 'Menus', 'post type general name', 'simple-restaurant' ),
			'singular_name'      => _x( 'Menu', 'post type singular name', 'simple-restaurant' ),
			'menu_name'          => _x( 'Menus', 'admin menu', 'simple-restaurant' ),
			'name_admin_bar'     => _x( 'Menu', 'add new on admin bar', 'simple-restaurant' ),
			'add_new'            => _x( 'Add New', 'menu', 'simple-restaurant' ),
			'add_new_item'       => __( 'Add New Menu', 'simple-restaurant' ),
			'new_item'           => __( 'New Menu', 'simple-restaurant' ),
			'edit_item'          => __( 'Edit Menu', 'simple-restaurant' ),
			'view_item'          => __( 'View Menu', 'simple-restaurant' ),
			'all_items'          => __( 'All Menus', 'simple-restaurant' ),
			'search_items'       => __( 'Search Menus', 'simple-restaurant' ),
			'parent_item_colon'  => __( 'Parent Menus:', 'simple-restaurant' ),
			'not_found'          => __( 'No menus found.', 'simple-restaurant' ),
			'not_found_in_trash' => __( 'No menus found in Trash.', 'simple-restaurant' ),
		);

		// Arguments for the custom post type.
		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'taxonomies'         => array( 'sr-menu_category', 'sr-menu_tag' ),
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'sr-menu' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields' ),
			'show_in_rest'       => true, // Enables Gutenberg editor.
		);

		// Register the custom post type.
		register_post_type( 'sr-menu', $args );
	}

	/**
	 * Menu Taxonomy.
	 *
	 * @since 1.0.0
	 */
	public function sr_menu_taxonomies() {
		// Categories taxonomy.
		register_taxonomy(
			'sr-menu_category',
			'sr-menu',
			array(
				'label'        => __( 'Categories', 'simple-restaurant' ),
				'rewrite'      => array( 'slug' => 'menu-category' ),
				'hierarchical' => true, // Makes it behave like categories.
				'show_in_rest' => true,  // Enable Gutenberg.
			)
		);

		// Tags taxonomy.
		register_taxonomy(
			'sr-menu_tag',
			'sr-menu',
			array(
				'label'        => __( 'Tags', 'simple-restaurant' ),
				'rewrite'      => array( 'slug' => 'sr-menu-tag' ),
				'hierarchical' => false, // Makes it behave like tags.
				'show_in_rest' => true,   // Enable Gutenberg.
			)
		);

		// Add specific categories (Udon Dishes, Rice Dishes, Vegan Dishes, Sides).
		if ( ! term_exists( 'Udon Dishes', 'sr-menu_category' ) ) {
				wp_insert_term( 'Udon Dishes', 'sr-menu_category' );
		}
		if ( ! term_exists( 'Rice Dishes', 'sr-menu_category' ) ) {
			wp_insert_term( 'Rice Dishes', 'sr-menu_category' );
		}
		if ( ! term_exists( 'Vegan Dishes', 'sr-menu_category' ) ) {
			wp_insert_term( 'Vegan Dishes', 'sr-menu_category' );
		}
		if ( ! term_exists( 'Sides', 'sr-menu_category' ) ) {
			wp_insert_term( 'Sides', 'sr-menu_category' );
		}
	}

	/**
	 * Add Colu8mns.
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed $columns Columns.
	 */
	public function sr_menu_add_columns( $columns ) {
		// Add new columns.
		$columns['sr_menu_category'] = __( 'Categories', 'simple-restaurant' );
		$columns['sr_menu_tag']      = __( 'Tags', 'simple-restaurant' );
		// Remove the Comments column.
		unset( $columns['comments'] );
		// Add the Image column at the beginning.
		$new_columns = array(
			'sr_menu_image' => __( 'Image', 'simple-restaurant' ),
		);

		// Move the Date column to the end.
		$date_column = $columns['date'];
		unset( $columns['date'] ); // Remove it from its original position.
		$columns['date'] = $date_column; // Add it to the end.
		return array_merge( $new_columns, $columns );
	}

	/**
	 * Add values to columns.
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed $column Columns.
	 * @param  mixed $post_id ID.
	 */
	public function sr_menu_populate_columns( $column, $post_id ) {
		if ( 'sr_menu_category' === $column ) {
			// Get terms for 'sr-menu_category'.
			$terms = get_the_terms( $post_id, 'sr-menu_category' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$categories = wp_list_pluck( $terms, 'name' );
				echo esc_html( implode( ', ', $categories ) );
			} else {
				esc_html_e( 'No Categories', 'simple-restaurant' );
			}
		}

		if ( 'sr_menu_tag' === $column ) {
			// Get terms for 'sr-menu_tag'.
			$terms = get_the_terms( $post_id, 'sr-menu_tag' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$tags = wp_list_pluck( $terms, 'name' );
				echo esc_html( implode( ', ', $tags ) );
			} else {
				esc_html_e( 'No Tags', 'simple-restaurant' );
			}
		}

		if ( 'sr_menu_image' === $column ) {
			if ( has_post_thumbnail( $post_id ) ) {
				// Display the post's featured image (thumbnail).
				echo get_the_post_thumbnail( $post_id, array( 50, 50 ) ); // Adjust the size as needed.
			} else {
				esc_html_e( 'No Image', 'simple-restaurant' );
			}
		}
	}
}
