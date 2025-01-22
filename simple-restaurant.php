<?php
/**
 * Plugin Name: Simple Restaurant
 * Plugin URI: https://milanmalla.com/simple-restaurant
 * Description: Create Restaurant Menu, Locations, Gallery and Career pages and Many More with Ease.
 * Version: 1.0
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
if ( ! defined( 'SR_PLUGIN_VERSION' ) ) {
	define( 'SR_PLUGIN_VERSION', '1.1' );
}

// Define EVF_PLUGIN_FILE.
if ( ! defined( 'SR_PLUGIN_FILE' ) ) {
	define( 'SR_PLUGIN_FILE', __FILE__ );
}

// Define EVF_PLUGIN_FILE.
if ( ! defined( 'SR_PLUGIN_DIR' ) ) {
	define( 'SR_PLUGIN_DIR', __DIR__ );
}

// Include the main SimpleRestaurant class.
if ( ! class_exists( 'SimpleRestaurant' ) ) {
	include_once dirname(__FILE__) . '/includes/class-sr.php'; // phpcs:ignore
}


/**
 * Styles.
 *
 * @since 1.0.0
 */
function sr_enqueue_scripts() {
	wp_enqueue_script(
		'sr-job-filter-block',
		plugins_url( '/build/block.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ),
		SR_PLUGIN_VERSION,
		true
	);

	// Localize script with new data for AJAX.
	wp_localize_script(
		'sr-job-filter-block',
		'sr_job_ajax_object',
		array(
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'sr_job_filter_nonce' => wp_create_nonce( 'sr-job-filter-nonce' ),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'sr_enqueue_scripts' );

/**
 * Styles.
 *
 * @since 1.0.0
 */
function sr_enqueue_frontend_scripts() {
	// Register the front-end script.
	wp_enqueue_script(
		'sr-job-filter-block',
		plugins_url( '/build/block.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ),
		SR_PLUGIN_VERSION,
		true
	);

	// Localize script with new data for AJAX.
	wp_localize_script(
		'sr-job-filter-block',
		'sr_job_ajax_object',
		array(
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'sr_job_filter_nonce' => wp_create_nonce( 'sr-job-filter-nonce' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'sr_enqueue_frontend_scripts' );

/**
 * Styles.
 *
 * @since 1.0.0
 */
function sr_job_ajax_filter_jobs() {
	if ( isset( $_POST['sr-job-filter-nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['sr-job-filter-nonce'] ) ), 'sr-job-filter-nonce' ) ) {

		$location = isset( $_POST['location'] ) ? intval( $_POST['location'] ) : '';
		$position = isset( $_POST['position'] ) ? intval( $_POST['position'] ) : '';
		$type     = isset( $_POST['type'] ) ? intval( $_POST['type'] ) : '';

		$args = array(
			'post_type'      => 'sr-job',
			'posts_per_page' => -1,
			'tax_query'      => array( 'relation' => 'AND' ), //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		);

		if ( $location ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'sr-job-location',
					'field'    => 'term_id',
					'terms'    => $location,
				);
		}

		if ( $position ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'sr-job-position',
					'field'    => 'term_id',
					'terms'    => $position,
				);
		}

		if ( $type ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'sr-job-type',
					'field'    => 'term_id',
					'terms'    => $type,
				);
		}

		$jobs       = new WP_Query( $args );
		$jobs_array = array();

		if ( $jobs->have_posts() ) {
			while ( $jobs->have_posts() ) {
					$jobs->the_post();
					$locations    = wp_get_post_terms( get_the_ID(), 'sr-job-location' );
					$positions    = wp_get_post_terms( get_the_ID(), 'sr-job-position' );
					// Get the job location and title dynamically
					$location = reset( wp_list_pluck( $locations, 'name' ) );
					$position = reset( wp_list_pluck( $positions, 'name' ) );
					$job_title = get_the_title();

					// Ensure URL encoding for query parameters
					$location_encoded = urlencode( $location );
					$job_encoded = urlencode( $title );
					$position_encoded = urlencode( $position );

					// Example array with query parameters
					$query_params = array(
						'location' => reset( wp_list_pluck( $locations, 'name' ) ),
						'position' => reset( wp_list_pluck( $positions, 'name' ) ),
						'job' => get_the_title(), // dynamic job title
					);

					// Construct the full URL and apply necessary sanitization
					$apply_url = get_option( 'sr_apply_job_url' );

					$query_string = http_build_query( $query_params );
					// Construct the final URL with query parameters
					$full_url = $apply_url . '?' . $query_string;

					// Sanitize the final URL to make it safe to use
					$final_url = ( $full_url );
					$jobs_array[] = array(
						'link'      => get_permalink(),
						'id'        => get_the_ID(),
						'title'     => get_the_title(),
						'excerpt'   => get_the_excerpt(),
						'content'   => get_the_content(),
						'apply_url' => $final_url
					);
			}
		}

		wp_reset_postdata();
		wp_send_json( $jobs_array );
	}
}
add_action( 'wp_ajax_sr_job_filter_jobs', 'sr_job_ajax_filter_jobs' );
add_action( 'wp_ajax_nopriv_sr_job_filter_jobs', 'sr_job_ajax_filter_jobs' );

/**
 * Styles.
 *
 * @since 1.0.0
 *
 * @param mixed $taxonomy Taxonomy.
 */
function sr_get_terms( $taxonomy ) {
	$terms       = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	);
	$terms_array = array();

	foreach ( $terms as $term ) {
			$terms_array[] = array(
				'id'   => $term->term_id,
				'name' => $term->name,
			);
	}

	// Add the "All" option at the start of the array
	array_unshift( $terms_array, array( 'id' => 0, 'name' => 'All' ) );
	wp_send_json( $terms_array );
}

add_action(
	'wp_ajax_get_locations',
	function () {
		sr_get_terms( 'sr-job-location' );
	}
);
add_action(
	'wp_ajax_get_positions',
	function () {
		sr_get_terms( 'sr-job-position' );
	}
);
add_action(
	'wp_ajax_get_types',
	function () {
		sr_get_terms( 'sr-job-type' );
	}
);

add_action(
	'wp_ajax_nopriv_get_locations',
	function () {
		sr_get_terms( 'sr-job-location' );
	}
);
add_action(
	'wp_ajax_nopriv_get_positions',
	function () {
		sr_get_terms( 'sr-job-position' );
	}
);
add_action(
	'wp_ajax_nopriv_get_types',
	function () {
		sr_get_terms( 'sr-job-type' );
	}
);

function render_job_filter_block( $attributes ) {
	// Output the block HTML, including attributes in a data-attribute
	return sprintf(
			'<div id="sr-job-filter-root" data-attributes="%s"></div>',
			esc_attr( json_encode( $attributes ) ) // Encode attributes as JSON
	);
}

register_block_type( 'sr/job-filter', array(
	'render_callback' => 'render_job_filter_block',
) );

add_action( 'admin_menu', 'simple_restaurant_add_submenus' );

/**
 * Add sub menu
 *
 * @since 1.0.0
 */
function simple_restaurant_add_submenus() {
	// Main menu.
	add_menu_page(
		'Simple Restaurant', // Page title.
		'Simple Restaurant', // Menu title.
		'manage_options',    // Capability.
		'simple-restaurant', // Menu slug.
		'simple_restaurant_dashboard', // Callback function.
		'dashicons-carrot',  // Icon.
		20                   // Position.
	);
	// Post types to add as submenus.
	$post_types = array(
		'sr-menu'     => 'Menus',
		'sr-job'      => 'Jobs',
		'sr-location' => 'Locations',
	);

	foreach ( $post_types as $slug => $label ) {
		add_submenu_page(
			'simple-restaurant',         // Parent slug.
			$label,                      // Page title.
			$label,                      // Menu title.
			'manage_options',            // Capability.
			"edit.php?post_type=$slug",  // Menu slug linked to post type.
			null                         // Callback function.
		);
	}
}

/**
 * Main instance of SimpleRestaurant.
 *
 * Returns the main instance of SR to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return SR
 */
function sr() {
	return SR::instance();
}

// Global for backwards compatibility.
$GLOBALS['simple-restaurant'] = sr();

/**
 * Dashbo
 *
 * @since 1.0.0
 */
function simple_restaurant_dashboard() {
	echo '<h1>Welcome to Simple Restaurant Dashboard</h1>';
	echo '<p>Manage your restaurant settings and content here.</p>';

	// Check if the form is submitted and save the option.
	if ( isset( $_POST['sr_apply_job_url_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['sr_apply_job_url_nonce'] ) ), 'save_sr_apply_job_url' ) ) {
		$sr_apply_job_url = isset( $_POST['sr_apply_job_url'] ) ? sanitize_text_field( wp_unslash( $_POST['sr_apply_job_url'] ) ) : '';
		$sr_allergy_page_id = isset( $_POST['sr_allergy_page_id'] ) ? sanitize_text_field( wp_unslash( $_POST['sr_allergy_page_id'] ) ) : '';
		update_option( 'sr_apply_job_url', $sr_apply_job_url );
		update_option( 'sr_allergy_page_id', $sr_allergy_page_id );
		echo '<div class="updated"><p>Apply Job URL saved successfully!</p></div>';
	}

	// Get the current value of the option.
	$sr_apply_job_url = get_option( 'sr_apply_job_url', '' );
	$sr_allergy_page_id = get_option( 'sr_allergy_page_id', '' );

	?>
	<div class="wrap">
			<h1>Simple Restaurant Settings</h1>
			<form method="post">
					<?php wp_nonce_field( 'save_sr_apply_job_url', 'sr_apply_job_url_nonce' ); ?>
					<table class="form-table">
							<tr>
									<th scope="row">
											<label for="sr_apply_job_url">Apply Job URL</label>
									</th>
									<td>
											<input type="text" name="sr_apply_job_url" id="sr_apply_job_url" value="<?php echo esc_attr( $sr_apply_job_url ); ?>" class="regular-text" />
											<p class="description">Enter the URL where users can apply for jobs.</p>
									</td>
							</tr>
							<tr>
									<th scope="row">
											<label for="sr_allergy_page_id">Allergy Page ID</label>
									</th>
									<td>
											<input type="text" name="sr_allergy_page_id" id="sr_allergy_page_id" value="<?php echo esc_attr( $sr_allergy_page_id ); ?>" class="regular-text" />
											<p class="description">Enter the Allergy Page ID.</p>
									</td>
							</tr>
					</table>
					<?php submit_button( 'Save Settins' ); ?>
			</form>
</div>
	<?php
}

add_action('admin_menu', 'add_allergy_pdf_submenu');
	function add_allergy_pdf_submenu() {
		add_submenu_page(
				'simple-restaurant', // Parent menu slug
				'Allergy PDF',            // Page title
				'Allergy PDF',            // Menu title
				'manage_options',         // Capability
				'allergy-pdf',            // Menu slug
				'allergy_pdf_page_callback' // Callback function
		);
	}


function allergy_pdf_page_callback() {
    $existing_pdf_url = get_option('allergy_pdf_url', false);
    ?>
    <div class="wrap">
        <h1>Manage Allergy PDF</h1>

        <!-- Placeholder for success or error messages -->
        <div id="sr-allergy-message"></div>
        <?php if ($existing_pdf_url): ?>
            <!-- <p id="sr-pdf"><strong>Current PDF:</strong> <a href="<?php echo esc_url($existing_pdf_url); ?>" target="_blank" id="sr-view-pdf">View Current PDF</a></p> -->
            <p>
                <button id="sr-change-pdf-button" class="button button-primary">Change PDF</button>
            </p>
					  <div id="current-pdf-container">
                <object id="pdf-object" data="<?php echo esc_url($existing_pdf_url); ?>" type="application/pdf" style="width:100%; height:600px;" aria-label="Current Allergy PDF"></object>
                <a id="pdf-link" href="<?php echo esc_url($existing_pdf_url); ?>" target="_blank">View Current PDF</a>
            </div>
        <?php else: ?>
            <p id="sr-pdf"><strong>Current PDF:</strong> No PDF uploaded yet.</p>
            <p>
                <button id="sr-change-pdf-button" class="button button-primary">Upload PDF</button>
            </p>
        <?php endif; ?>

    </div>
    <?php
}

// update_allergy_pdf_block('');
function update_allergy_pdf_block($new_pdf_url) {
    // Specify the page ID where the file block resides
    $page_id = get_option('sr_allergy_page_id', '');

    // Retrieve the post object
    $post = get_post($page_id);

    if (!$post) {
        return;
    }

    // Retrieve current content
    $content = $post->post_content;

    // Parse blocks to locate the `core/file` block
    $blocks = parse_blocks($content);
    $updated_blocks = [];

    foreach ($blocks as $block) {
        // Check if it's the `core/file` block
        if ($block['blockName'] === 'core/file') {

						if (isset($block['attrs']['href'])) {
							$block['attrs']['href'] = esc_url($new_pdf_url); // Sanitize URL
						}

            // Optionally update the file name (if applicable)
            if (isset($block['attrs']['fileName'])) {
                $block['attrs']['fileName'] = basename($new_pdf_url); // Extract the file name
            }
						$block_content = $block['innerHTML'];

						$block_content = preg_replace("/http[s]?\:.*?.pdf/",$new_pdf_url, $block_content );
						$block_content = preg_replace("/aria-label=\".*?\"/", 'aria-label="'. basename($new_pdf_url).'"', $block_content);

						$new_pdf_name = basename($new_pdf_url);

						$updated_content = preg_replace_callback(
							'/<a[^>]*href="[^"]+\.pdf"[^>]*>([^<]+)<\/a>/',
							function($matches) use ($new_pdf_name) {
									// $matches[1] contains the current text inside the <a> tag (PDF name)
									return str_replace($matches[1], $new_pdf_name, $matches[0]);
							},
							$block_content, 1);

						// Update the block's inner HTML with the modified content
						$block['innerHTML'] = $updated_content;
						$block['innerContent'][0] = $updated_content;
        }
        // Add the (potentially updated) block to the array
        $updated_blocks[] = $block;
    }

    // Serialize blocks back into post content
    $updated_content = serialize_blocks($updated_blocks);

    // Update the page content with the new file block
    wp_update_post([
        'ID' => $page_id,
        'post_content' => $updated_content,
    ]);
}

add_action('wp_ajax_update_allergy_pdf', 'ajax_update_allergy_pdf');

function ajax_update_allergy_pdf() {

    // Check for required permissions
    if (!current_user_can('manage_options') || !isset($_POST['pdf_url'])) {
        wp_send_json_error(['message' => 'Unauthorized request.']);
        return;
    }

    // Get the PDF URL from the AJAX request
    $new_pdf_url = esc_url_raw($_POST['pdf_url']);

    if (!$new_pdf_url) {
        wp_send_json_error(['message' => 'Invalid PDF URL.']);
        return;
    }

    // Update the PDF URL in the database
    update_option('allergy_pdf_url', $new_pdf_url);

    // Update the file block on the page
    update_allergy_pdf_block($new_pdf_url);

    wp_send_json_success(['message' => 'PDF updated successfully!']);
}






add_action('admin_enqueue_scripts', 'enqueue_media_library');
function enqueue_media_library($hook) {
    if ($hook !== 'simple-restaurant_page_allergy-pdf') { // Adjust the hook name if needed
        return;
    }
    wp_enqueue_media(); // Enqueues WordPress media library
    wp_enqueue_script(
        'allergy-pdf-media-script',
        plugin_dir_url(__FILE__) . 'assets/allergy_pdf.js',
        ['jquery'],
        '1.0',
        true
    );

    wp_localize_script('allergy-pdf-media-script', 'ajaxurl', admin_url('admin-ajax.php'));
}
