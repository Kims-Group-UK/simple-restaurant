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

		add_action( 'save_post', array( $this, 'sr_save_ean_on_menu_create' ), 10, 3 );
		add_action( 'restrict_manage_posts', array( $this, 'add_import_export_button_in_menu_table' ) );

		// Import or Export.
		add_action( 'admin_post_export_sr_menu_to_csv', array( $this, 'export_sr_menu_to_xlsx' ) );
		add_action( 'admin_post_import_sr_menu_from_csv', array( $this, 'import_sr_menu_from_csv' ) );
		add_action( 'admin_footer', array( $this, 'sr_menu_import_popup' ) );

		// Meta Boxes.
		add_action( 'add_meta_boxes', array( $this, 'sr_add_menu_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'sr_save_menu_meta_boxes' ) );

		// Manage Excel File.
		add_action( 'wp_ajax_sr_menu_import_ajax', array( $this, 'sr_menu_import_ajax' ) );
	}


	/**
	 * Import Excel File.
	 *
	 * @since 1.0.0
	 */
	public function sr_menu_import_ajax() {
		if ( ! isset( $_FILES['csv_file'] ) || ! isset( $_FILES['csv_file']['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			wp_send_json_error( array( 'message' => 'No file uploaded.' ) );
		}

		$file      = $_FILES['csv_file']['tmp_name']; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$filename  = sanitize_file_name( $_FILES['csv_file']['name'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$extension = pathinfo( $filename, PATHINFO_EXTENSION );
		if ( 'csv' === $extension ) {
			$this->sr_menu_import_csv( $file );
		} elseif ( 'xlsx' === $extension ) {
			$this->sr_menu_import_xlsx( $file );
		} else {
			wp_send_json_error( array( 'message' => 'Invalid file type. Please upload a Excel File or Excel file.' ) );
		}
		wp_send_json_success( array( 'message' => 'File imported successfully.' ) );
	}


	/**
	 * Process Excel File.
	 *
	 * @since 1.0.0
	 *
	 * @param  srting $file_path  File.
	 */
	public function sr_menu_import_csv( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			return;
		}

		global $wpdb;
		$csv = fopen( $file_path, 'r' );
		if ( ! $csv ) {
			return;
		}

		$headers = fgetcsv( $csv, 1000, "\t" );

		while ( ( $row = fgetcsv( $csv, 1000, "\t" ) ) !== false ) {
			$data = array_combine( $headers, $row );
			sr_menu_process_data( $data );
		}

		fclose( $csv );
	}


	/**
	 * Process Excel File.
	 *
	 * @since 1.0.0
	 *
	 * @param  srting $file_path  File.
	 */
	public function sr_menu_import_xlsx( $file_path ) {

		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load( $file_path );
		$sheet       = $spreadsheet->getActiveSheet();
		$rows        = $sheet->toArray();

		$headers = $rows[0];
		for ( $i = 1; $i < count( $rows ); $i++ ) {
			$data = array_combine( $headers, $rows[ $i ] );
			$this->sr_menu_process_data( $data );
		}
	}


	/**
	 * Process Excel File.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $data  Row.
	 */
	public function sr_menu_process_data( $data ) {
		global $wpdb;

		$category2       = sanitize_text_field( $data['Category 2'] );
		$category1       = sanitize_text_field( $data['Category 1'] );
		$product_name    = sanitize_text_field( $data['Product'] );
		$ingredients     = sanitize_text_field( $data['Ingredients'] );
		$allergy_advice1 = sanitize_text_field( $data['AllergyAdvice 1 (inc May contain)'] );
		$allergy_advice2 = sanitize_text_field( $data['AllergyAdvice 2'] );
		$price           = sanitize_text_field( $data['Price'] );
		$eat_in_price    = sanitize_text_field( $data['Eat In Price'] );
		$use_by          = sanitize_text_field( $data['Storage / Used by'] );
		$plu             = sanitize_text_field( $data['PLU'] );
		$ean13           = sanitize_text_field( $data['BarcodeEAN13'] );

		if ( empty( $plu ) ) {
			return;
		}

		$existing_menu = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'sr-menu' AND post_status = 'publish' AND ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sr_plu' AND meta_value = %s)",
				$plu
			)
		);

		if ( $existing_menu ) {
			$post_id = $existing_menu;
			wp_update_post(
				array(
					'ID'         => $post_id,
					'post_title' => $product_name,
				)
			);
		} else {
			$post_id = wp_insert_post(
				array(
					'post_title'  => $product_name,
					'post_status' => 'publish',
					'post_type'   => 'sr-menu',
				)
			);
			if ( empty( $ean13 ) ) {
				$ean13 = $this->generate_ean();
			}
			update_post_meta( $post_id, '_sr_plu', $plu );
			update_post_meta( $post_id, '_sr_ean13', $ean13 );
		}

		update_post_meta( $post_id, '_sr_category_1', $category1 );
		update_post_meta( $post_id, '_sr_category_2', $category2 );

		update_post_meta( $post_id, '_sr_ingredients', $ingredients );
		update_post_meta( $post_id, '_sr_allergy_advice1', $allergy_advice1 );
		update_post_meta( $post_id, '_sr_allergy_advice2', $allergy_advice2 );
		update_post_meta( $post_id, '_sr_price', $price );
		update_post_meta( $post_id, '_sr_eat_in_price', $eat_in_price );
		update_post_meta( $post_id, '_sr_use_by', $use_by );
	}

	/**
	 * Export to Excel.
	 *
	 * @since 1.0.0
	 */
	public function export_sr_menu_to_xlsx() {
		if ( isset( $_GET['post_type'] ) && 'sr-menu' === $_GET['post_type'] ) {
			// Ensure user has permissions.
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'You are not allowed to export this data.' );
			}

			$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
			$sheet       = $spreadsheet->getActiveSheet();

			// Set filename.
			$filename = 'sr-menu-export-' . gmdate( 'Y-m-d-H-i-s' ) . '.xlsx';

			// Get menu items.
			$args  = array(
				'post_type'      => 'sr-menu',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'ASC',
			);
			$posts = get_posts( $args );

			// Define headers.
			$headers = array(
				'Category1',
				'Category2',
				'Product',
				'Ingredients',
				'AllergyAdvice 1 (inc May contain)',
				'AllergyAdvice 2',
				'Price',
				'Eat In Price',
				'Storage / Used by',
				'BarcodeEAN13',
				'PLU',
			);

			// Write headers.
			$sheet->fromArray( array( $headers ), null, 'A1' );

			// Set barcode column (J) to text format.
			$sheet->getStyle( 'J:J' )->getNumberFormat()->setFormatCode( \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT );

			// Start writing data from row 2.
			$row_index = 2;
			foreach ( $posts as $post ) {

				// Get barcode and ensure it has leading zeros.
				$barcode = get_post_meta( $post->ID, '_sr_ean13', true );
				$barcode = str_pad( $barcode, 13, '0', STR_PAD_LEFT );

				$data = array(
					get_post_meta( $post->ID, '_sr_category_1', true ),
					get_post_meta( $post->ID, '_sr_category_2', true ),
					$post->post_title,
					get_post_meta( $post->ID, '_sr_ingredients', true ),
					get_post_meta( $post->ID, '_sr_allergy_advice1', true ),
					get_post_meta( $post->ID, '_sr_allergy_advice2', true ),
					get_post_meta( $post->ID, '_sr_price', true ),
					get_post_meta( $post->ID, '_sr_eat_in_price', true ),
					get_post_meta( $post->ID, '_sr_use_by', true ),
					$barcode ? $barcode : 'NA',
					get_post_meta( $post->ID, '_sr_plu', true ),
				);

				// Write row data.
				$sheet->fromArray( array( $data ), null, "A$row_index" );
				++$row_index;
			}

			// Set HTTP headers for file download.
			header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
			header( 'Content-Disposition: attachment;filename="' . $filename . '"' );
			header( 'Cache-Control: max-age=0' );

			// Create writer and output file.
			$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx( $spreadsheet );
			$writer->save( 'php://output' );
			exit;
		}
	}


	/**
	 * Popup.
	 *
	 * @since 1.0.0
	 */
	public function sr_menu_import_popup() {
		?>
<div id="sr-menu-import-modal" class="sr-modal-overlay">
		<div class="sr-modal-content">
			<h2>Import Excel File File</h2>
			<p>Select a Excel File file and click "Upload & Import" to proceed.</p>
			<form id="sr-menu-import-form" method="post" enctype="multipart/form-data">
				<input type="file" name="csv_file" id="csv_file" accept=".csv, .xlsx">
				<button type="submit" class="button button-primary">Upload & Import</button>
				<div id="sr-upload-status" style="display: none; margin-top: 10px;">
					<span class="spinner is-active"></span> Uploading...
				</div>
			</form>
			<button id="sr-menu-close-modal" class="button button-secondary">Cancel</button>
		</div>
	</div>

	<style>
		/* Popup Overlay */
		.sr-modal-overlay {
			position: fixed;
			top: 0; left: 0;
			width: 100%; height: 100%;
			background: rgba(0, 0, 0, 0.6);
			display: flex;
			justify-content: center;
			align-items: center;
			opacity: 0;
			visibility: hidden;
			transition: opacity 0.3s ease, visibility 0.3s ease;
			z-index: 9999;
		}

		/* Show Popup */
		.sr-modal-overlay.active {
			opacity: 1;
			visibility: visible;
		}

		/* Modal Content */
		.sr-modal-content {
			background: #fff;
			padding: 20px;
			border-radius: 10px;
			box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
			text-align: center;
			max-width: 400px;
			width: 90%;
			animation: fadeIn 0.3s ease-out;
		}

		/* Smooth fade-in animation */
		@keyframes fadeIn {
			from {
				opacity: 0;
				transform: translateY(-10px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		/* Button Styles */
		.sr-modal-content .button {
			margin-top: 10px;
			width: 100%;
			padding: 8px;
			font-size: 16px;
		}

		.sr-modal-content .button-primary {
			background-color: #0073aa;
			border-color: #0073aa;
			color: #fff;
		}

		.sr-modal-content .button-primary:hover {
			background-color: #005a87;
		}

		.sr-modal-content .button-secondary {
			background-color: #ccc;
			border-color: #aaa;
			color: #333;
		}

		.sr-modal-content .button-secondary:hover {
			background-color: #bbb;
		}

		/* File Input Styling */
		#csv_file {
			display: block;
			width: 100%;
			margin-bottom: 15px;
			padding: 6px;
			border: 1px solid #ddd;
			border-radius: 5px;
		}

		/* Upload status indicator */
		#sr-upload-status {
			font-weight: bold;
			color: #0073aa;
		}

	</style>

	<script>
		document.addEventListener("DOMContentLoaded", function() {
			var modal = document.getElementById("sr-menu-import-modal");
			var openButton = document.getElementById("sr-import-csv-btn");
			var closeButton = document.getElementById("sr-menu-close-modal");
			var form = document.getElementById("sr-menu-import-form");
			var uploadStatus = document.getElementById("sr-upload-status");

			// Show the popup when "Import Excel File" button is clicked
			openButton.addEventListener("click", function(event) {
				event.preventDefault();
				modal.classList.add("active");
			});

			// Close the popup
			closeButton.addEventListener("click", function() {
				modal.classList.remove("active");
			});

			// Handle file upload
			form.addEventListener("submit", function(event) {
				event.preventDefault();

				var fileInput = document.getElementById("csv_file").files[0];
				if (!fileInput) {
					alert("Please select a Excel File file.");
					return;
				}

				uploadStatus.style.display = "block"; // Show loading indicator

				var formData = new FormData();
				formData.append("csv_file", fileInput);
				formData.append("action", "sr_menu_import_ajax");

				fetch(ajaxurl, {
					method: "POST",
					body: formData,
				})
				.then(response => response.json())
				.then(data => {
					alert(data.data.message);
					modal.classList.remove("active"); // Close modal
					location.reload(); // Refresh page to see updates
				})
				.catch(error => console.error("Error:", error));
			});
		});
	</script>
		<?php
	}

	/**
	 * Export Menu Button.
	 *
	 * @since 1.0.0
	 */
	public function add_import_export_button_in_menu_table() {
		if ( isset( $_GET['post_type'] ) && 'sr-menu' === $_GET['post_type'] ) {
			?>
			<div class="alignleft actions">
				<a href="#" id="sr-import-csv-btn" class="button" >Import From Excel File</a>
				<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=export_sr_menu_to_csv&post_type=sr-menu' ) ); ?>" class="button">Export to Excel File</a>
			</div>
			<?php
		}
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
		$columns['ean']              = __( 'EAN', 'simple-restaurant' );
		$columns['sr_price']         = __( 'Price', 'simple-restaurant' );
		$columns['sr_eat_in_price']  = __( 'Eat In Price', 'simple-restaurant' );

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
				echo get_the_post_thumbnail( $post_id, array( 50, 50 ) );
			} else {
				esc_html_e( 'No Image', 'simple-restaurant' );
			}
		}

		if ( 'ean' === $column ) {
			$ean = get_post_meta( $post_id, '_sr_ean13', true );

			if ( ! empty( $ean ) ) {
				echo esc_html( $ean );
			} else {
				echo esc_html_e( 'N/A', 'simple-restaurant' );
			}
		}

		if ( 'sr_price' === $column ) {
			$sr_price = get_post_meta( $post_id, '_sr_price', true );
			echo esc_html( $sr_price ? $sr_price : 'N/A' );
		}

		if ( 'sr_eat_in_price' === $column ) {
			$sr_eat_in_price = get_post_meta( $post_id, '_sr_eat_in_price', true );
			echo esc_html( $sr_eat_in_price ? $sr_eat_in_price : 'N/A' );
		}
	}


	/**
	 * Create and save EAN
	 *
	 * @since 1.0.0
	 *
	 * @param  [type] $post_id Post ID.
	 * @param  [type] $post Post.
	 */
	public function sr_save_ean_on_menu_create( $post_id, $post ) {
		if ( 'sr-menu' !== $post->post_type ) {
			return;
		}

		$eans = $this->generate_ean();

		// Save the EANs as an array in a custom field.
		update_post_meta( $post_id, 'EAN', $eans );
	}

	/**
	 * Generate Unique EAN
	 *
	 * @since 1.0.0
	 */
	public function generate_unique_ean() {
		// Generate a random 13-digit EAN (barcode).
		$ean = $this->generate_ean();

		// Check if the generated EAN already exists in the database.
		$args = array(
			'post_type'      => 'sr-menu',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_key'       => 'EAN',
			'meta_value'     => $ean,
			'fields'         => 'ids',
		);

		$existing_posts = get_posts( $args );

		// If any posts with the same EAN are found, it means the barcode already exists.
		if ( ! empty( $existing_posts ) ) {
			// EAN already exists, regenerate and check again.
			return generate_unique_ean(); // Recursive call until unique EAN is found.
		}

		return $ean;
	}

	/**
	 * Generate EAN Number.
	 *
	 * @since 1.0.0
	 */
	public function generate_ean() {
		$ean = '';
		for ( $i = 0; $i < 12; $i++ ) {
			$ean .= wp_rand( 0, 9 );
		}

		// Calculate checksum (EAN-13 uses a checksum digit).
		$checksum = 0;
		for ( $i = 0; $i < 12; $i++ ) {
			$checksum += $ean[ $i ] * ( 0 === $i % 2 ? 1 : 3 );
		}
		$checksum = ( 10 - ( $checksum % 10 ) ) % 10;
		$ean     .= $checksum;

		return $ean;
	}

	/**
	 * Add custom fields.
	 *
	 * @since 1.0.0
	 */
	public function sr_add_menu_meta_boxes() {
		add_meta_box(
			'sr_menu_price_meta_box', // ID of the meta box.
			'Menu Prices', // Title of the meta box.
			array( $this, 'sr_menu_price_meta_box_callback' ), // Callback function.
			'sr-menu', // Post type.
			'normal', // Context (normal, side, etc.).
			'high' // Priority.
		);
	}

	/**
	 * Additional fields.
	 *
	 * @since 1.0.0
	 *
	 * @param  midex $post Menu.
	 */
	public function sr_menu_price_meta_box_callback( $post ) {
		// Nonce field for security.
		wp_nonce_field( 'sr_menu_price_meta_box_nonce', 'sr_menu_price_meta_box_nonce' );

		// Get existing values for custom fields.

		$sr_category_1 = get_post_meta( $post->ID, '_sr_category_1', true );
		$sr_category_2 = get_post_meta( $post->ID, '_sr_category_2', true );

		$sr_price        = get_post_meta( $post->ID, '_sr_price', true );
		$sr_eat_in_price = get_post_meta( $post->ID, '_sr_eat_in_price', true );

		$sr_ingredients     = get_post_meta( $post->ID, '_sr_ingredients', true );
		$sr_allergy_advice1 = get_post_meta( $post->ID, '_sr_allergy_advice1', true );
		$sr_allergy_advice2 = get_post_meta( $post->ID, '_sr_allergy_advice2', true );

		$sr_allergen_information = get_post_meta( $post->ID, '_sr_allergen_information', true );
		$sr_use_by               = get_post_meta( $post->ID, '_sr_use_by', true );

		?>
		<p>
			<label for="sr_category_1">Category1:</label><br/>
			<input type="text" id="sr_category_1" name="sr_category_1" value="<?php echo esc_attr( $sr_category_1 ); ?>" />
		</p>		<p>
			<label for="sr_category_2">Category2:</label><br/>
			<input type="text" id="sr_category_2" name="sr_category_2" value="<?php echo esc_attr( $sr_category_2 ); ?>" />
		</p>

		<p>
			<label for="sr_price">Price:</label><br/>
			<input type="text" id="sr_price" name="sr_price" value="<?php echo esc_attr( $sr_price ); ?>" />
		</p>
		<p>
			<label for="sr_eat_in_price">Eat In Price:</label><br/>
			<input type="text" id="sr_eat_in_price" name="sr_eat_in_price" value="<?php echo esc_attr( $sr_eat_in_price ); ?>" />
		</p>

		<p>
			<label for="sr_ingredients">Ingredients:</label><br/>
			<input type="text" id="_sr_ingredients" name="sr_ingredients" value="<?php echo esc_attr( $sr_ingredients ); ?>" />
		</p>
		<p>
			<label for="sr_use_by">Storage / Used by:</label><br/>
			<input type="text" id="sr_use_by" name="sr_use_by" value="<?php echo esc_attr( $sr_use_by ); ?>" />
		</p>
		<p>
			<label for="sr_allergy_advice1">Allergy Advice1:</label><br/>
			<?php
				// Output the WYSIWYG editor for allergen information with 'sr_' prefix.
				wp_editor(
					$sr_allergy_advice1,
					'sr_allergy_advice1',
					array(
						'textarea_name' => 'sr_allergy_advice1', // Use the field name.
						'textarea_rows' => 5, // Set the rows of the editor.
						'teeny'         => false, // Optional: for a simpler editor with fewer options.
					)
				);
			?>
		</p>
		<p>
			<label for="sr_allergy_advice2">Allergy Advice2:</label><br/>
			<?php
				// Output the WYSIWYG editor for allergen information with 'sr_' prefix.
				wp_editor(
					$sr_allergy_advice2,
					'sr_allergy_advice2',
					array(
						'textarea_name' => 'sr_allergy_advice2', // Use the field name.
						'textarea_rows' => 5, // Set the rows of the editor.
						'teeny'         => false, // Optional: for a simpler editor with fewer options.
					)
				);
			?>

		</p>

		<?php
	}

	/**
	 * Save custom fields.
	 *
	 * @since 1.0.0
	 *
	 * @param  int $post_id Post ID.
	 */
	public function sr_save_menu_meta_boxes( $post_id ) {

		// Check if nonce is set and is valid.
		if ( ! isset( $_POST['sr_menu_price_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['sr_menu_price_meta_box_nonce'] ) ), 'sr_menu_price_meta_box_nonce' ) ) {
			return;
		}

		// Check if the post is being autosaved.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Save custom fields.
		if ( isset( $_POST['sr_category_1'] ) ) {
			update_post_meta( $post_id, '_sr_category_1', sanitize_text_field( wp_unslash( $_POST['sr_category_1'] ) ) );
		}
		if ( isset( $_POST['sr_category_2'] ) ) {
			update_post_meta( $post_id, '_sr_category_2', sanitize_text_field( wp_unslash( $_POST['sr_category_2'] ) ) );
		}

		if ( isset( $_POST['sr_price'] ) ) {
			update_post_meta( $post_id, '_sr_price', sanitize_text_field( wp_unslash( $_POST['sr_price'] ) ) );
		}
		if ( isset( $_POST['sr_eat_in_price'] ) ) {
			update_post_meta( $post_id, '_sr_eat_in_price', sanitize_text_field( wp_unslash( $_POST['sr_eat_in_price'] ) ) );
		}

		if ( isset( $_POST['sr_ingredients'] ) ) {
			update_post_meta( $post_id, '_sr_ingredients', sanitize_text_field( wp_unslash( $_POST['sr_ingredients'] ) ) );
		}
		if ( isset( $_POST['sr_use_by'] ) ) {
			update_post_meta( $post_id, '_sr_use_by', sanitize_text_field( wp_unslash( $_POST['sr_use_by'] ) ) );
		}

		if ( isset( $_POST['sr_allergy_advice1'] ) ) {
			update_post_meta( $post_id, '_sr_allergy_advice1', wp_kses_post( wp_unslash( $_POST['sr_allergy_advice1'] ) ) );
		}

		if ( isset( $_POST['sr_allergy_advice2'] ) ) {
			update_post_meta( $post_id, '_sr_allergy_advice2', wp_kses_post( wp_unslash( $_POST['sr_allergy_advice2'] ) ) );
		}
	}
}
