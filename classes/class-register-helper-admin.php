<?php
/**
 * PMPro Register Helper Admin
 *
 * @package pmprorha
 */

namespace PMPro;

/**
 * PMPro Custom User Fields
 */
class Register_Helper_Admin {
	/**
	 * Field types
	 *
	 * @var array
	 */
	public $field_types = array(
		'text',
		'select',
		'select2',
		'checkbox',
		'checkbox_grouped',
		'multiselect',
		'date',
		'file',
		'hidden',
		'html',
		'radio',
		'readonly',
		'textarea',
	);

	/**
	 * Field locations
	 *
	 * @var array
	 */
	public $field_locations = array(
		'after_username',
		'after_password',
		'after_email',
		'after_captcha',
		'checkout_boxes',
		'after_billing_fields',
		'before_submit_button',
		'just_profile',
	);

	/**
	 * Construct
	 */
	public function __construct() {
		global $pagenow;
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'pmpro_custom_account_fields';
		$param_page       = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		add_action( 'admin_menu', array( $this, 'admin_page' ) );
		add_action( 'init', array( $this, 'register_pmpro_fields' ) );
		if ( 'admin.php' === $pagenow && 'pmpro-registration-fields' === $param_page ) {
			add_action( 'init', array( $this, 'maybe_create_table' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_page_enqueue' ) );
			add_action( 'admin_notices', array( $this, 'display_notice' ) );
			add_filter( 'admin_footer_text', array( $this, 'change_footer_text' ) );
		}
	}

	/**
	 * Create DB table if it doesnt exist
	 *
	 * @return false
	 */
	public function maybe_create_table() {
		global $wpdb;
		$table_name = $this->table_name;

		$create_ddl = 'CREATE TABLE ' . $table_name . '(
				id int(6) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name VARCHAR(30),
				type VARCHAR(30),
				attr VARCHAR(255),
				location VARCHAR(50),
				UNIQUE (name)
			)';

		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

		if ( $wpdb->get_var( $query ) == $table_name ) { // phpcs:ignore
			return true;
		}

		// Didn't find it try to create it..
		$wpdb->query( $create_ddl ); // phpcs:ignore

		// We cannot directly tell that whether this succeeded!
		if ( $wpdb->get_var( $query ) == $table_name ) { // phpcs:ignore
			return true;
		}
		return false;
	}

	/**
	 * Get all PMPro custom fields
	 */
	public function get_fields_from_db() {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM {$this->table_name}" ); // phpcs:ignore
		$fields  = array();
		foreach ( $results as $result ) {
			$fields[] = array(
				'name'     => $result->name,
				'type'     => $result->type,
				'attr'     => json_decode( $result->attr, true ),
				'location' => $result->location,
			);
		}
		return $fields;
	}

	/**
	 * Delete Field from DB
	 *
	 * @param string $name ID/Name of field to delete.
	 * @return $result
	 */
	public function delete_field_from_db( $name ) {
		global $wpdb;
		$result = $wpdb->delete( // phpcs:ignore
			$this->table_name,
			array(
				'name' => $name,
			)
		);
		return $result;
	}

	/**
	 * Add or update PMPro custom field
	 */
	public function add_field_to_db() {
		global $wpdb;
		$nonce = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'pmprorha_add_field_nonce' ) ) {
			die( 'Security check' );
		}
		$field_id          = isset( $_POST['field_text_id'] ) ? filter_input( INPUT_POST, 'field_text_id' ) : '';
		$field_label       = filter_input( INPUT_POST, 'field_text_label' );
		$field_is_required = ! empty( $_POST['field_is_required'] ) ? true : false;
		$field_on_profile  = ! empty( $_POST['field_on_profile'] ) ? true : false;
		$field_type        = filter_input( INPUT_POST, 'field_type_select' );
		$field_location    = filter_input( INPUT_POST, 'field_location_select' );
		$field_options     = 'select' === $field_type ? array( '' => 'Select ' . $field_label ) : array();
		$field_options     = ! empty( $_POST['field_select_options'] ) ? array_merge( $field_options, explode( "\n", filter_input( INPUT_POST, 'field_select_options' ) ) ) : false;
		$field_attr        = array(
			'label'    => $field_label,
			'profile'  => $field_on_profile,
			'required' => $field_is_required,
		);

		$show_options = array(
			'select',
			'select2',
			'multiselect',
			'checkbox_grouped',
			'radio',
		);

		if ( in_array( $field_type, $show_options, true ) ) {
			$field_attr['options'] = $field_options;
		}

		$field_attr_json = wp_json_encode( $field_attr );

		$sql = "REPLACE INTO {$this->table_name} (name,type,attr,location) VALUES (%s,%s,%s,%s)"; // phpcs:ignore
		$data = array(
			$field_id,
			$field_type,
			$field_attr_json,
			$field_location,
		);

		$result = $wpdb->query( // phpcs:ignore
			$wpdb->prepare(
				$sql, // phpcs:ignore
				$data
			)
		);
		return $result;
	}

	/**
	 * Register PMPro Fields
	 */
	public function register_pmpro_fields() {
		$db_fields = $this->get_fields_from_db();
		foreach ( $db_fields as $db_field ) {
			$field = new \PMProRH_Field(
				$db_field['name'],
				$db_field['type'],
				$db_field['attr']
			);
			pmprorh_add_registration_field(
				$db_field['location'],
				$field
			);
		}
	}

	/**
	 * Notices
	 */
	public function display_notice() {
		$result = false;
		if ( isset( $_POST['add_pmpro_field_submit'] ) ) {
			$nonce = filter_input( INPUT_GET, '_wpnonce' );
			if ( ! wp_verify_nonce( $nonce, 'pmprorha_add_field_nonce' ) ) {
				die( 'Security check' );
			}
			$result  = $this->add_field_to_db();
			$type    = $result ? 'success' : 'error';
			$label   = filter_input( INPUT_POST, 'field_text_label' );
			$message = $result ? 'Field <strong>' . $label . '</strong> added successfully.' : 'Error adding field.';
			echo wp_kses_post( $this->notice_markup( $result, $type, $label, $message ) );
		}
		if ( isset( $_POST['delete_pmpro_form_field'] ) ) {
			$nonce = filter_input( INPUT_GET, '_wpnonce' );
			if ( ! wp_verify_nonce( $nonce, 'pmprorha_delete_field_nonce' ) ) {
				die( 'Security check' );
			}
			$result  = $this->delete_field_from_db( filter_input( INPUT_POST, 'delete_pmpro_form_field' ) );
			$type    = $result ? 'success' : 'error';
			$label   = filter_input( INPUT_POST, 'delete_pmpro_form_field' );
			$message = $result ? 'Field <strong>' . $label . '</strong> deleted successfully.' : 'Error deleting field.';
			echo wp_kses_post( $this->notice_markup( $result, $type, $label, $message ) );
		}
	}

	/**
	 * Notice markup
	 *
	 * @param boolean $result Result of DB query.
	 * @param string  $type Notice type (success, error, warning, or info).
	 * @param string  $label Field label.
	 * @param string  $message Notice message.
	 * @return $markup
	 */
	public function notice_markup( $result = false, $type = 'warning',
	$label = 'NO_FIELD', $message = 'Notice' ) {
		$markup = '<div class="notice notice-' . $type . ' is-dismissible"><p>' . $message . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
		return $markup;
	}

	/**
	 * Custom Admin Menu
	 *
	 * @return void
	 */
	public function admin_page() {
		add_submenu_page( 'pmpro-dashboard', 'Register Helper', 'Register Helper', 'pmpro_dashboard', 'pmpro-registration-fields', array( $this, 'admin_page_markup' ), 6 );
	}

	/**
	 * Admin page markup
	 */
	public function admin_page_markup() {
		$existing_fields  = $this->get_fields_from_db();
		$field_types      = $this->field_types;
		$location_options = $this->field_locations;
		ob_start();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">Register Helper</h1>
			<div class="flex-row">
				<div class="flex-col">
					<?php $nonce = wp_create_nonce( 'pmprorha_add_field_nonce' ); ?>
					<form action="?page=pmpro-registration-fields&_wpnonce=<?php echo esc_attr( $nonce ); ?>" method="post" class="card">
						<h2>Add Field</h2>
						<p><em>Adds a field to membership checkout. Entering a field with an ID/Name that already exists will update that field.</em></p>
						<table class="form-table">
							<tr>
								<th><label for="field_type_select">Type</label></th>
								<td>
									<select name="field_type_select" id="field_type_select">
										<option value="" disabled>Choose Field Type</option>
										<?php
										$i = 0;
										foreach ( $field_types as $field_type ) :
											$option_selected = 0 === $i ? 'selected="selected"' : '';
											// todo: make more options available by using conditionals.
											$option_disabled = 20 < $i ? 'disabled' : ''; // limit available options to what has been developed.
											?>
											<option value="<?php echo esc_attr( $field_type ); ?>" <?php echo esc_html( $option_selected ); ?> <?php echo esc_attr( $option_disabled ); ?>><?php echo esc_html( ucwords( str_replace( '_', ' ', $field_type ) ) ); ?></option>
											<?php
											$i++;
										endforeach;
										?>
									</select>
								</td>
							</tr>
							<tr>
								<th><label for="field_text_label">Label</label></th>
								<td><input class="regular-text" type="text" name="field_text_label" id="field_text_label" required></td>
							</tr>
							<tr>
								<th><label for="field_text_id">ID/Name</label></th>
								<td><input class="regular-text" type="text" name="field_text_id" id="field_text_id" required></td>
							</tr>
							<tr id="field_select_options_wrapper" style="display: none;">
								<th><label for="field_select_options">Options</label></th>
								<td><textarea placeholder="Enter each option on a new line." name="field_select_options" id="field_select_options" cols="30" rows="4"></textarea></td>
							</tr>
							<tr>
								<th><label for="field_location_select">Location</label></th>
								<td>
									<select name="field_location_select" id="field_location_select">
										<option value="" disabled>Choose Location</option>
										<?php
										$i = 0;
										foreach ( $location_options as $option ) :
											$option_selected = 0 === $i ? 'selected="selected"' : '';
											?>
											<option value="<?php echo esc_attr( $option ); ?>" <?php echo esc_html( $option_selected ); ?>><?php echo esc_html( ucwords( str_replace( '_', ' ', $option ) ) ); ?></option>
											<?php
											$i++;
										endforeach;
										?>
									</select>
								</td>
							</tr>
							<tr>
								<th><label for="field_is_required">Required</label></th>
								<td>
									<label for="field_is_required">
										<input type="checkbox" name="field_is_required" id="field_is_required">
										Field is required
									</label>
								</td>
							</tr>
							<tr>
								<th><label for="field_on_profile">Show on Profile</label></th>
								<td>
									<label for="field_on_profile">
										<input type="checkbox" name="field_on_profile" id="field_on_profile" checked="checked">
										Visible on user profile
									</label>
								</td>
							</tr>
						</table>
						<p>
							<input type="submit" name="add_pmpro_field_submit" id="add_pmpro_field_submit" class="button button-primary" value="Add Field">
						</p>
					</form>
				</div>
					<div class="flex-col">
					<h2>Current Fields</h2>
					<table class="wp-list-table widefat fixed striped" style="max-width: 600px;">
						<thead>
							<tr>
								<th><strong>Label</strong></th>
								<th><strong>ID/Name</strong></th>
								<th style="text-align: center;"><strong>Action</strong></th>
							</tr>
						</thead>
						<?php
						foreach ( $existing_fields as $field ) :
							?>
							<tr>
								<th>
								<?php echo esc_html( $field['attr']['label'] ); ?>
								</th>
								<td style="vertical-align: middle;">
								<?php echo esc_html( $field['name'] ); ?>
								</td>
								<td style="text-align: center;">
									<?php $nonce = wp_create_nonce( 'pmprorha_delete_field_nonce' ); ?>
									<form action="?page=pmpro-registration-fields&_wpnonce=<?php echo esc_attr( $nonce ); ?>" method="post">
										<button type="submit" value="<?php echo esc_attr( $field['name'] ); ?>" name="delete_pmpro_form_field" id="delete_<?php echo esc_attr( $field['name'] ); ?>" class="button button-danger">Delete</button>
									</form>
								</td>
							</tr>
							<?php
						endforeach;
						?>
					</table>
				</div>
			</div>
		</div>
		<?php
		echo ob_get_clean(); // phpcs:ignore
	}

	/**
	 * Register JS
	 */
	public function admin_page_enqueue() {
		wp_enqueue_script(
			'pmprorhadmin-js',
			plugins_url( '/assets/index.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_enqueue_style(
			'pmprorhadmin',
			plugins_url( '/assets/style.css', dirname( __FILE__ ) ),
			array(),
			'1.0,0',
			'all'
		);
	}

	/**
	 * Change footer credit text
	 */
	public function change_footer_text() {
		echo '<span id="footer-thankyou">Developed by <a href="https://travisaw.com" target="_blank">Travis A. Wagner</a></span>';
	}
}
