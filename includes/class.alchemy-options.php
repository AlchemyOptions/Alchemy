<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( class_exists( 'Alchemy_Options' ) ) {
    return;
}

class Alchemy_Options {
    private $active_tab;

    public function activate() {
        $this->includes();

        /* all of the hooks go here */
        $this->hook_up();
    }

    public function includes() {
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/alchemy-functions.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/interface.alchemyField.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/class.alchemyDBValue.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/class.alchemyField.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyText.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyTextarea.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyPassword.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyRadio.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyCheckbox.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemySelect.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyColorpicker.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyDatepicker.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyButtonGroup.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyUpload.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyRepeater.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/class.alchemyFieldsLoader.php' );
    }

    public function enqueue_assets() {
        if( ! isset( $_GET[ 'page' ] ) || 'alchemy-options' !== $_GET[ 'page' ] || ! is_admin() ) {
            return;
        }

        wp_register_script( 'alchemy-scripts', ALCHEMY_OPTIONS_PLUGIN_DIR_URL . 'assets/scripts/alchemy.min.js', array(
            'jquery',
            'jquery-ui-sortable',
            'jquery-ui-autocomplete',
            'jquery-ui-datepicker',
            'iris'
        ), ALCHEMY_OPTIONS_VERSION, true );
        wp_localize_script( 'alchemy-scripts', 'alchemyData', array(
            'adminURL' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'alchemy_ajax_nonce' )
        ) );

        wp_register_style( 'alchemy-jquery', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array(), '1.12.1' );
        wp_register_style( 'alchemy-styles', ALCHEMY_OPTIONS_PLUGIN_DIR_URL . 'assets/styles/alchemy.css', array( 'alchemy-jquery' ), ALCHEMY_OPTIONS_VERSION );

        wp_enqueue_media();
        wp_enqueue_script( 'alchemy-scripts' );

        wp_enqueue_style( 'alchemy-styles' );
    }

    public function hook_up() {
        if( is_multisite() ) {
            add_action( 'network_admin_menu', array( $this, 'create_network_options_page' ) );
        }

        add_action( 'admin_menu', array( $this, 'create_options_submenu_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_alchemy_save_options', array( $this, 'handle_save_options' ) );
        add_action( 'wp_ajax_alchemy_repeater_item_add', array( $this, 'handle_repeater_item_add' ) );
//
//        add_action( 'wp_ajax_alchemy_datalist_search', array( $this, 'handle_datalist_search' ) );

        //todo: add plugin_text_domain()
    }

    public function handle_save_options() {
        if ( ! isset( $_POST[ 'nonce' ] ) || ! wp_verify_nonce( $_POST[ 'nonce' ], 'alchemy_ajax_nonce' ) ) {
            die();
        }

        $fields = $_POST[ 'fields' ];

        if( ! $fields ) {
            return;
        }

        if( count( $fields ) > 0 ) {
            foreach ( $fields as $id => $payload ) {
                $value = new Alchemy_DB_Value( $payload );

                update_option( $id, array(
                    'type' => $payload[ 'type' ],
                    'value' => $value->get_safe_value(),
                ) );
            }
        }
    }

    public function handle_datalist_search() {
        if ( ! isset( $_GET[ 'nonce' ] ) || ! is_array( $_GET[ 'nonce' ] ) || ! wp_verify_nonce( $_GET[ 'nonce' ][ 'value' ], $_GET[ 'nonce' ][ 'id' ] ) ) {
            die();
        }

        $fields = $_GET[ 'search-string' ];

        if( ! $fields ) {
            return;
        }
    }

    public function handle_repeater_item_add() {
        if ( ! isset( $_GET[ 'nonce' ] ) || ! wp_verify_nonce( $_GET[ 'nonce' ][1], $_GET[ 'nonce' ][0] ) ) {
            die();
        }

        $rID = $_GET[ 'repeater' ][0];
        $repeateeID = $_GET[ 'repeater' ][1];
        $index = $_GET[ 'index' ];

        $repeater = new Alchemy_Repeater_Field();

        $repeaterHTML = $repeater->generate_repeatee( array(
            'id' => $rID,
            'repeatee_id' => $repeateeID,
            'index' => $index
        ) );

        wp_send_json( $repeaterHTML );
    }

    public function create_network_options_page() {
        add_submenu_page(
            'themes.php',
            __( 'Alchemy Multisite Options', 'alchemy-options' ),
            __( 'Alchemy Multisite Options', 'alchemy-options' ),
            'manage_options',
            'alchemy-options',
            array( $this, 'render_multisite_options_submenu' )
        );
    }

    public function create_options_submenu_page() {
        add_submenu_page(
            'themes.php',
            __( 'Alchemy Options', 'alchemy-options' ),
            __( 'Alchemy Options', 'alchemy-options' ),
            'manage_options',
            'alchemy-options',
            array( $this, 'render_options_submenu' )
        );
    }

    public function render_options_submenu () {
        echo $this->get_options_page( alch_options_id(), __( 'Alchemy options', 'alchemy-options' ) );
    }

    public function render_multisite_options_submenu (  ) {
        echo $this->get_options_page( alch_network_options_id(), __( 'Alchemy multisite options', 'alchemy-options' ) );
    }

    public function get_options_page( $type, $pageTitle ) {
        $savedOptions = get_option( $type, array() );

        $submenuHTML = '';

        $submenuHTML .= '<div class="wrap alchemy">';
        $submenuHTML .= '<h2>' . $pageTitle . '</h2><br>';

        if( count( $savedOptions ) > 0 ) {
            $submenuHTML .= $this->get_options_page_html( $savedOptions );
        } else {
            $submenuHTML .= '<p>' . __( 'Looks like there are no options to show', 'alchemy-options' ) . '</p>';
        }

        $submenuHTML .= '</div>';

        return $submenuHTML;
    }

    public function get_options_page_html( $options ) {
        $optionsHTML = "";

        if( is_array( $options[ 'tabs' ] ) && count( $options[ 'tabs' ] ) > 0 ) {
            reset( $options[ 'tabs' ] );
            $this->active_tab = key( $options[ 'tabs' ] );

            if( isset( $_GET[ 'tab' ] ) ) {
                $this->active_tab = $_GET[ 'tab' ];
            }

            $tabsSettings = $options[ 'tabs' ];

            $optionsHTML .= $this->get_tabs_html( $tabsSettings );
        }

        if( is_array( $options[ 'options' ] ) && count( $options[ 'options' ] ) > 0 ) {
            $optionsHTML .= '<div class="alchemy__options">';

            $optionsHTML .= $this->get_options_html( $options[ 'options' ] );

            $optionsHTML .= '</div>';
        }

        return $optionsHTML;
    }

    public function get_tabs_html( $tabs ) {
        $tabsHTML = "";

        $tabsHTML .= '<div class="nav-tab-wrapper">';

        foreach ( $tabs as $tabID => $tabDetails ) {
            $tabsHTML .= '<a class="nav-tab' . ( $this->active_tab == $tabID ? " nav-tab-active" : "" ) . '" href="?page=alchemy-options&tab=' . esc_attr( $tabID ) . '">' . $tabDetails[ 'title' ] . '</a>';
        }

        $tabsHTML .= "</div>";

        return $tabsHTML;
    }

    public function get_options_html( $options ) {
        $optionsHTML = "";

        $filteredOptions = array_filter( $options, function( $option ) {
            return $option[ 'tab' ] === $this->active_tab;
        } );

        //todo: various checks when tab info is missing or tab is not supplied
        $optionFields = new Alchemy_Fields_Loader();

        $optionsHTML .= '<form action="?page=alchemy-options&action=save-alchemy-options" id="jsAlchemyForm">';
        $optionsHTML .= '<button type="submit" class="alchemy__btn alchemy__btn--submit button button-primary">' . __( 'Save options', 'alchemy-options' ) . '</button><span class="spinner"></span>';

        $optionsHTML .= '<div class="alchemy__fields">';
        $optionsHTML .= $optionFields->get_fields_html( $filteredOptions );
        $optionsHTML .= '</div>';

        $optionsHTML .= '<button type="submit" class="alchemy__btn alchemy__btn--submit button button-primary">' . __( 'Save options', 'alchemy-options' ) . '</button><span class="spinner"></span>';
        $optionsHTML .= '</form>';

        return $optionsHTML;
    }
}