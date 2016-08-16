<?php
GFForms::include_addon_framework();

// include 'simpleaddon.php';

class GFVinAudit extends GFAddOn {

    protected $_version = GF_SIMPLE_ADDON_VERSION;
    protected $_min_gravityforms_version = '1.9';
    protected $_slug = 'vinaudit';
    protected $_path = 'simpleaddon/vinaudit.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms VinAudit';
    protected $_short_title = 'VinAudit Settings';

    private static $_instance = null;

    public static function get_instance() {
        if ( self::$_instance == null ) {
            self::$_instance = new GFVinAudit();
        }

        return self::$_instance;
    }

    public function init() {
        parent::init();
        add_filter( 'gform_submit_button', array( $this, 'form_submit_button' ), 10, 2 );
    }

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

    function form_submit_button( $button, $form ) {
        $settings = $this->get_form_settings( $form );
        if ( isset( $settings['enabled'] ) && true == $settings['enabled'] ) {
            $text   = $this->get_plugin_setting( 'mytextbox' );
            $button = "<div>{$text}</div>" . $button;

        }

        return $button;
    }


    public function plugin_settings_fields() {
        return array(
            array(
                'title'  => esc_html__( 'VinAudit Settings', 'vinaudit' ),
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
                    array(
                          'label'   => esc_html__( 'Message', 'vinaudit'),
                          'type'    => 'textarea',
                          'name'    => 'message',
                          'class'   => 'medium',
                          'tooltip' => esc_html__('Custom message before report link.'),
                    ),
                ),
            ),
        );
    }

    public function is_valid_setting( $value ) {
        return strlen( $value ) < 10;
    }
    public function render_uninstall() {
        // an empty function will remove the uninstall section on the settings page
    }

}

?>
