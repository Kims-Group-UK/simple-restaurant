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
final class Job {
		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
	public function __construct() {
		// Hook the function to the 'init' action to ensure the custom post type is registered.
		add_action( 'init', array( $this, 'sr_job_post_type' ) );
		add_filter( 'manage_edit-sr-job_columns', array( $this, 'sr_job_add_columns' ) );
		add_action( 'manage_sr-job_posts_custom_column', array( $this, 'sr_job_populate_columns' ), 10, 2 );

		add_action( 'init', array( $this, 'sr_job_taxonomies' ) );
	}

	/**
	 * Job
	 *
	 * @since 1.0.0
	 */
	public function sr_job_post_type() {
		$labels = array(
			'name'               => _x( 'Jobs', 'post type general name', 'simple-restaurant' ),
			'singular_name'      => _x( 'Job', 'post type singular name', 'simple-restaurant' ),
			'job_name'           => _x( 'Jobs', 'admin job', 'simple-restaurant' ),
			'name_admin_bar'     => _x( 'Job', 'add new on admin bar', 'simple-restaurant' ),
			'add_new'            => _x( 'Add New', 'job', 'simple-restaurant' ),
			'add_new_item'       => __( 'Add New Job', 'simple-restaurant' ),
			'new_item'           => __( 'New Job', 'simple-restaurant' ),
			'edit_item'          => __( 'Edit Job', 'simple-restaurant' ),
			'view_item'          => __( 'View Job', 'simple-restaurant' ),
			'all_items'          => __( 'All Jobs', 'simple-restaurant' ),
			'search_items'       => __( 'Search Jobs', 'simple-restaurant' ),
			'parent_item_colon'  => __( 'Parent Jobs:', 'simple-restaurant' ),
			'not_found'          => __( 'No jobs found.', 'simple-restaurant' ),
			'not_found_in_trash' => __( 'No jobs found in Trash.', 'simple-restaurant' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_job'        => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'sr-job' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'job_position'       => 20,
			'supports'           => array( 'title', 'editor', 'author' ),
			'show_in_rest'       => true, // Enables Gutenberg editor.
			'show_in_menu'       => false, // Nest under the main menu.
		);

		register_post_type( 'sr-job', $args );
	}



	/**
	 * Add Colu8mns.
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed $columns Columns.
	 */
	public function sr_job_add_columns( $columns ) {
		// Add new columns.
		$columns['sr_job_location'] = __( 'Location', 'simple-restaurant' );
		$columns['sr_job_position'] = __( 'Position', 'simple-restaurant' );
		$columns['sr_job_type']     = __( 'Type', 'simple-restaurant' );
		// Remove the Comments column.
		unset( $columns['comments'] );
		unset( $columns['date'] ); // Remove it from its original position.
		$columns['date'] = $date_column; // Add it to the end.
		return $columns;
	}

		/**
		 * Add values to columns.
		 *
		 * @since 1.0.0
		 *
		 * @param  mixed $column Columns.
		 * @param  mixed $post_id ID.
		 */
	public function sr_job_populate_columns( $column, $post_id ) {
		if ( 'sr_job_location' === $column ) {
			// Get terms for 'sr-job_location'.
			$terms = get_the_terms( $post_id, 'sr-job-location' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$categories = wp_list_pluck( $terms, 'name' );
				echo esc_html( implode( ', ', $categories ) );
			} else {
				esc_html_e( 'No Locations', 'simple-restaurant' );
			}
		}

		if ( 'sr_job_position' === $column ) {
			// Get terms for 'sr-job_position'.
			$terms = get_the_terms( $post_id, 'sr-job-position' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$tags = wp_list_pluck( $terms, 'name' );
				echo esc_html( implode( ', ', $tags ) );
			} else {
				esc_html_e( 'No Tags', 'simple-restaurant' );
			}
		}

		if ( 'sr_job_type' === $column ) {
			// Get terms for 'sr-job_type'.
			$terms = get_the_terms( $post_id, 'sr-job-type' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$tags = wp_list_pluck( $terms, 'name' );
				echo esc_html( implode( ', ', $tags ) );
			} else {
				esc_html_e( 'No Tags', 'simple-restaurant' );
			}
		}
	}

		/**
		 * Menu Taxonomy.
		 *
		 * @since 1.0.0
		 */
	public function sr_job_taxonomies() {
		// Location Taxonomy.
		register_taxonomy(
			'sr-job-location',
			'sr-job',
			array(
				'labels'       => array(
					'name'          => __( 'Locations', 'simple-restaurant' ),
					'singular_name' => __( 'Location', 'simple-restaurant' ),
				),
				'hierarchical' => true,
				'show_in_rest' => true,
				'rest_base'    => 'sr-job-locations',
			)
		);

		// Position Taxonomy.
		register_taxonomy(
			'sr-job-position',
			'sr-job',
			array(
				'labels'       => array(
					'name'          => __( 'Positions', 'simple-restaurant' ),
					'singular_name' => __( 'Position', 'simple-restaurant' ),
				),
				'hierarchical' => true,
				'show_in_rest' => true,
				'rest_base'    => 'sr-job-positions',
			)
		);

		// Type Taxonomy.
		register_taxonomy(
			'sr-job-type',
			'sr-job',
			array(
				'labels'       => array(
					'name'          => __( 'Types', 'simple-restaurant' ),
					'singular_name' => __( 'Type', 'simple-restaurant' ),
				),
				'hierarchical' => true,
				'show_in_rest' => true,
				'rest_base'    => 'sr-job-types',
			)
		);
	}
}
