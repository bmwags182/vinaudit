<?php

GFForms::include_addon_framework();

class GFVinAudit extends GFAddOn {

	protected $_version = GF_VINAUDIT_ADDON_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'vinaudit';
	protected $_path = 'vinaudit/vinaudit-addon.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms VinAudit Add-On';
	protected $_short_title = 'VinAudit Add-On';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFVinAudit
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFVinAudit();
		}

		return self::$_instance;
	}

	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();
		add_filter( 'gform_submit_button', array( $this, 'form_submit_button' ), 10, 2 );
	}


	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'my_script_js',
				'src'     => $this->get_base_url() . '/js/my_script.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'strings' => array(
					'first'  => esc_html__( 'First Choice', 'vinaudit' ),
					'second' => esc_html__( 'Second Choice', 'vinaudit' ),
					'third'  => esc_html__( 'Third Choice', 'vinaudit' )
				),
				'enqueue' => array(
					array(
						'admin_page' => array( 'form_settings' ),
						'tab'        => 'vinaudit'
					)
				)
			),

		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Return the stylesheets which should be enqueued.
	 *
	 * @return array
	 */
	public function styles() {
		$styles = array(
			array(
				'handle'  => 'my_styles_css',
				'src'     => $this->get_base_url() . '/css/my_styles.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'field_types' => array( 'poll' ) )
				)
			)
		);

		return array_merge( parent::styles(), $styles );
	}


	// # FRONTEND FUNCTIONS --------------------------------------------------------------------------------------------

	/**
	 * Add the text in the plugin settings to the bottom of the form if enabled for this form.
	 *
	 * @param string $button The string containing the input tag to be filtered.
	 * @param array $form The form currently being displayed.
	 *
	 * @return string
	 */
	function form_submit_button( $button, $form ) {
		$settings = $this->get_form_settings( $form );
		if ( isset( $settings['enabled'] ) && true == $settings['enabled'] ) {
			$text   = $this->get_plugin_setting( 'mytextbox' );
			$button = "<div>{$text}</div>" . $button;
		}

		return $button;
	}


	// # ADMIN FUNCTIONS -----------------------------------------------------------------------------------------------

	/**
	 * Creates a custom page for this add-on.
	 */
	/**
	* 	public function plugin_page() {
	* 		echo 'This page appears in the Forms menu';
	* 	}
	**/
	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		// This should have the username, password, and api key for VinAudit
		// Three boxes for settings
		return array(
			array(
				'title'  => esc_html__( 'VinAudit API Settings', 'vinaudit' ),
				'fields' => array(
					array(
                        'label'   => esc_html__('API Key', 'vinaudit'),
                        'type'    => 'text',
                        'name'    => 'api_key',
                        'class'   => 'medium',
                        'tooltip' => esc_html__('VinAudit API Key', 'vinaudit'),
                    ),
                    array(
                        'label'   => esc_html__( 'Username', 'vinaudit' ),
                        'type'    => 'text',
                        'name'    => 'username',
                        'class'   => 'small',
                        'tooltip' => esc_html__( 'VinAudit Username', 'vinaudit' ),
                    ),
                    array(
                        'label'   => esc_html__( 'Password', 'vinaudit' ),
                        'type'    => 'text',
                        'name'    => 'password',
                        'class'   => 'small',
                        'tooltip' => esc_html__( 'VinAudit Password', 'vinaudit' ),
                    ),
				)
			)
		);
	}

	/**
	 * Configures the settings which should be rendered on the Form Settings > Simple Add-On tab.
	 *
	 * @return array
	 */
	public function get_field_choices($form) {
		$choices = array();
		foreach ($form['fields'] as $field) {
			$field_title = $field['label'];
			$choice = array(
			       			'label' => $field_title,
			       			'value' => $field_title,
			       			);
			array_push( $choices, $choice );
		}
		return $choices;
	}
	public function form_settings_fields( $form ) {
		return array(
			array(
				'title'  => esc_html__( 'VinAudit Form Settings', 'vinaudit' ),
				'fields' => array(
					array(
						'label'   => esc_html__( 'Enabled', 'vinaudit' ),
						'type'    => 'checkbox',
						'name'    => 'enabled',
						'tooltip' => esc_html__( 'Enable this to run checks on VINs submitted through this form', 'vinaudit' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'Enabled', 'vinaudit' ),
								'name'  => 'enabled',
							),
						),
					),
					/**
					array(
						'label'   => esc_html__( 'My checkboxes', 'vinaudit' ),
						'type'    => 'checkbox',
						'name'    => 'checkboxgroup',
						'tooltip' => esc_html__( 'This is the tooltip', 'vinaudit' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'First Choice', 'vinaudit' ),
								'name'  => 'first',
							),
							array(
								'label' => esc_html__( 'Second Choice', 'vinaudit' ),
								'name'  => 'second',
							),
							array(
								'label' => esc_html__( 'Third Choice', 'vinaudit' ),
								'name'  => 'third',
							),
						),
					),
					array(
						'label'   => esc_html__( 'My Radio Buttons', 'vinaudit' ),
						'type'    => 'radio',
						'name'    => 'myradiogroup',
						'tooltip' => esc_html__( 'This is the tooltip', 'vinaudit' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'First Choice', 'vinaudit' ),
							),
							array(
								'label' => esc_html__( 'Second Choice', 'vinaudit' ),
							),
							array(
								'label' => esc_html__( 'Third Choice', 'vinaudit' ),
							),
						),
					),
					array(
						'label'      => esc_html__( 'My Horizontal Radio Buttons', 'vinaudit' ),
						'type'       => 'radio',
						'horizontal' => true,
						'name'       => 'myradiogrouph',
						'tooltip'    => esc_html__( 'This is the tooltip', 'vinaudit' ),
						'choices'    => array(
							array(
								'label' => esc_html__( 'First Choice', 'vinaudit' ),
							),
							array(
								'label' => esc_html__( 'Second Choice', 'vinaudit' ),
							),
							array(
								'label' => esc_html__( 'Third Choice', 'vinaudit' ),
							),
						),
					),
					array(
						'label'   => esc_html__( 'My Dropdown', 'vinaudit' ),
						'type'    => 'select',
						'name'    => 'mydropdown',
						'tooltip' => esc_html__( 'This is the tooltip', 'vinaudit' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'First Choice', 'vinaudit' ),
								'value' => 'first',
							),
							array(
								'label' => esc_html__( 'Second Choice', 'vinaudit' ),
								'value' => 'second',
							),
							array(
								'label' => esc_html__( 'Third Choice', 'vinaudit' ),
								'value' => 'third',
							),
						),
					),
					array(
						'label'             => esc_html__( 'My Text Box', 'vinaudit' ),
						'type'              => 'text',
						'name'              => 'mytext',
						'tooltip'           => esc_html__( 'This is the tooltip', 'vinaudit' ),
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
					array(
						'label'   => esc_html__( 'My Text Area', 'vinaudit' ),
						'type'    => 'textarea',
						'name'    => 'mytextarea',
						'tooltip' => esc_html__( 'This is the tooltip', 'vinaudit' ),
						'class'   => 'medium merge-tag-support mt-position-right',
					),
					array(
						'label' => esc_html__( 'My Hidden Field', 'vinaudit' ),
						'type'  => 'hidden',
						'name'  => 'myhidden',
					),
					array(
						'label' => esc_html__( 'My Custom Field', 'vinaudit' ),
						'type'  => 'my_custom_field_type',
						'name'  => 'my_custom_field',
						'args'  => array(
							'text'     => array(
								'label'         => esc_html__( 'A textbox sub-field', 'vinaudit' ),
								'name'          => 'subtext',
								'default_value' => 'change me',
							),
							'checkbox' => array(
								'label'   => esc_html__( 'A checkbox sub-field', 'vinaudit' ),
								'name'    => 'my_custom_field_check',
								'choices' => array(
									array(
										'label'         => esc_html__( 'Activate', 'vinaudit' ),
										'name'          => 'subcheck',
										'default_value' => true,
									),
								),
							),
						),
					),
					**/
					array(
					      'label'				=> esc_html__('Admin Email', 'vinaudit'),
					      'type'				=> 'text',
					      'name'				=> 'admin_email',
					      'tooltip'				=> esc_html__( 'Enter your email if you would like to receive a copy of the report.', 'vinaudit' ),
						  'class'   			=> 'medium',
						  // 'feedback_callback'	=> array( $this, 'is_valid_setting' ),
					      ),
					array(
					      'label'	=> esc_html__('Email report to admin?', 'vinaudit'),
					      'type'	=> 'checkbox',
					      'name'	=> 'enable_email',
					      'tooltip'	=> esc_html__('Enable this to send a link to the report to the admin', 'vinaudit'),
					      'choices' => array(
							array(
								'label' => esc_html__( 'Enabled', 'vinaudit' ),
								'name'  => 'email_admin',
								 ),
					      					),
					      ),
					array(
					      'label'	=> esc_html__('Admin Message:', 'vinaudit'),
					      'type'	=> 'textarea',
					      'name'	=> 'admin_message',
					      'class'	=> 'medium',
					      'tooltip'	=> esc_html__('Use this field to edit the message sent to the admin. The link will be located at the end of the message'),
					      ),
					array(
					      'label'	=> esc_html__('Email report to user?', 'vinaudit'),
					      'type'	=> 'checkbox',
					      'name'	=> 'notify_user',
					      'tooltip'	=> esc_html__('Enable this to send a link to the report to the user', 'vinaudit'),
					      'choices' => array(
					        array(
					              'label'	=> esc_html__('Enabled', 'vinaudit'),
					              'name'	=> 'email_user',
					              ),
					        ),
					      ),
					array(
					      'label'	=> esc_html__('Message to user:', 'vinaudit'),
					      'type'	=> 'textarea',
					      'name'	=> 'user_message',
					      'class'	=> 'medium',
					      'tooltip'	=> esc_html__('Use this field to customize the message sent to users. The link will be located at the end of the message', 'vinaudit'),
					      ),
					// put user first and last name here
					array(
					      'label'	=> esc_html__('First Name Field', 'vinaudit'),
					      'type' 	=> 'select',
					      'name'	=> 'fname_field',
					      'tooltip' => esc_html__('Which field will be used as the user\'s first name', 'vinaudit'),
					      'choices'	=> get_field_choices($form),
					      ),
					array(
					      'label'	=> esc_html__('Last Name Field', 'vinaudit'),
					      'type'	=> 'select',
					      'name'	=> 'lname_field',
					      'tooltip'	=> esc_html__('Which field will be used as the user\'s last name', 'vinaudit'),
					      'choices'	=> get_field_choices($form),
					      ),
					array(
					      'label'	=> esc_html__('User Email', 'vinaudit'),
					      'type'	=> 'select',
					      'name'	=> 'user_email',
					      'tooltip'	=> esc_html__('Which field is used for the users email', 'vinaudit'),
					      'choices' => get_field_choices($form),
					      ),
					array(
					      'label'	=> esc_html__('VIN Field', 'vinaudit'),
					      'type'	=> 'select',
					      'name'	=> 'vin',
					      'tooltip'	=> esc_html__('Select which field is used for VIN input', 'vinaudit'),
					      'choices'	=> get_field_choices($form),
					      ),
					array(
					      'label'	=> esc_html__("Are you forwarding the report information to another form?", 'vinaudit'),
					      'type'	=> 'checkbox',
					      'name'	=> 'forward',
					      'tooltip'	=> esc_html__("Use this if you are going to be forwarding the report information to another form", 'vinaudit'),
					      'choices' => array(
							array(
								'label' => esc_html__( 'Enabled', 'vinaudit' ),
								'name'  => 'form_enabled',
							),
							),
					      ),
					array(
					      'label'	=> esc_html__('Second form address', 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'next_page',
					      'class'	=> 'medium',
					      'tooltip'	=> esc_html__("Enter the URL of the form that will be receiving the report data", 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__('First Name variable', 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'fname_var',
					      'tooltip'	=> esc_html('Variable used to dynamically fill second form', 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__('Last Name variable', 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'lname_var',
					      'tooltip'	=> esc_html__('Variabled used to dynamically fill second form', 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__('Email variable', 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'email_var',
					      'tooltip'	=> esc_html__('Variable used to dynamically fill second form', 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__('VIN Variable', 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'vin_var',
					      'tooltip'	=> esc_html__("Variable used to dynamically fill second form", 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__('Year Variable', 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'year_var',
					      'tooltip'	=> esc_html__('Variable used to dynamically fill secont form'),
					      ),
					array(
					      'label'	=> esc_html__('Make variable', 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'make_var',
					      'tooltip'	=> esc_html__('Variable used to dynamically fill second form'),
					      ),
					array(
					      'label'	=> esc_html__('Model Variable', 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'model_var',
					      'tooltip'	=> esc_html__('Variable used to dynamically fill second form', 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__('Trim Variable', 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'trim_var',
					      'tooltip'	=> esc_html__('Variable used to dynamically fill second form', 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__('Style variable', 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'style_var',
					      'tooltip'	=> esc_html__("Variable used to dynamically fill second form", 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__('ABS variable', 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'abs_var',
					      'tooltip'	=> esc_html__('Variable used to dynamically fill second form', 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__("Engine variable", 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'engine_var',
					      'tooltip' => esc_html__("Variable used to dynamically fill second form", 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__("Country of Origin variable", 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'country_var',
					      'tooltip'	=> esc_html__("Variable used to dynamically fill second form"),
					      ),
					array(
					      'label'	=> esc_html__("Fuel Tank variable", 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'tank_var',
					      'tooltip'	=> esc_html__("Variable used to fill second form", 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__("Steering variable", 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'steering_var',
					      'tooltip'	=> esc_html__("Variable used to dynamically fill second form", 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__('Clean Title variable', 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'clean_var',
					      'tooltip'	=> esc_html__('Variable used to dynamically fill second form', 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__('Height Variable', 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'height_var',
					      'tooltip'	=> esc_html__("Variable used to dynamically fill second form", 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__("Width variable", 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'width_var',
					      'tooltip'	=> esc_html__('Variable used to dynamically fill second form'),
					      ),
					array(
					      'label'	=> esc_html__("Length variable", 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'length_var',
					      'tooltip'	=> esc_html__("Variable used to dynamically fill second form", 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__('Seating variable', 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'seating_var',
					      'tooltip'	=> esc_html__('Variable used to fill second form', 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__("Optional Seating variable", 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'opt_seating_var',
					      'tooltip'	=> esc_html__('Variable used to dynamically fill second form', 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__("Highway MPG variable", 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'highway_var',
					      'tooltip'	=> esc_html__("Variable used to dynamically fill second form", 'vinaudit'),
					      ),
					array(
					      'label'	=> esc_html__("City MPG variable", 'vinaudit'),
					      'type'	=> 'text',
					      'name'	=> 'city_var',
					      'tooltip'	=> esc_html__("Variable used to dynamically fill second form", 'vinaudit'),
					      ),
				),
			),
		);
	}

	/**
	 * Define the markup for the my_custom_field_type type field.
	 *
	 * @param array $field The field properties.
	 * @param bool|true $echo Should the setting markup be echoed.
	 */
	public function settings_my_custom_field_type( $field, $echo = true ) {
		echo '<div>' . esc_html__( 'My custom field contains a few settings:', 'vinaudit' ) . '</div>';

		// get the text field settings from the main field and then render the text field
		$text_field = $field['args']['text'];
		$this->settings_text( $text_field );

		// get the checkbox field settings from the main field and then render the checkbox field
		$checkbox_field = $field['args']['checkbox'];
		$this->settings_checkbox( $checkbox_field );
	}


	// # HELPERS -------------------------------------------------------------------------------------------------------

	/**
	 * The feedback callback for the 'mytextbox' setting on the plugin settings page and the 'mytext' setting on the form settings page.
	 *
	 * @param string $value The setting value.
	 *
	 * @return bool
	 */
	public function is_valid_setting( $value ) {
		return strlen( $value ) < 10;
	}
	public function render_uninstall() {
        // an empty function will remove the uninstall section on the settings page
    }

}
