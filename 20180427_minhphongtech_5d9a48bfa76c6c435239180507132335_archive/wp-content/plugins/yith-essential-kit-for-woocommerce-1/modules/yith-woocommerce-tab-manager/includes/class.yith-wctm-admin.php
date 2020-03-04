<?php
if( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
if( !class_exists('YITH_WCTM_Admin')){

    class YITH_WCTM_Admin{
        /**
         * @var YITH_WCTM_Admin $instance
         */
        protected static $instance;
        /**
         * @var Panel $_panel
         */
        protected $_panel;

        /**
         * @var $_premium string Premium tab template file name
         */
        protected $_premium = 'premium.php';

        /**
         * @var string Premium version landing link
         */
        protected $_premium_landing_url = '//yithemes.com/themes/plugins/yith-woocommerce-tab-manager/';

        /**
         * @var string Plugin official documentation
         */
        protected $_official_documentation = '//yithemes.com/docs-plugins/yith-woocommerce-tab-manager/';

        protected $_premium_live_demo = '//plugins.yithemes.com/yith-woocommerce-tab-manager';

        /**
         * @var string Yith WooCommerce Tab manager panel page
         */
        protected $_panel_page = 'yith_wc_tab_manager_panel';



        public function __construct()
        {

            //Add action links
            add_filter('plugin_action_links_' . plugin_basename(YWTM_DIR . '/' . basename(YWTM_FILE)), array(
                $this,
                'action_links'
            ));
            add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 4);
            add_action('yith_tab_manager_premium', array($this, 'premium_tab'));

            //  Add action menu
            add_action('admin_menu', array($this, 'add_menu_page'), 5);

            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_style' ) );
	        //register metabox to tab manager
	        add_action( 'admin_init', array( $this, 'add_tab_metabox' ), 1 );

        }
        /**
         * Add a panel under YITH Plugins tab
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
        public function add_menu_page()
        {
            if( !empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs = array(
                'settings' => __( 'Settings', 'yith-woocommerce-tab-manager' ),
            );

            if( !defined( 'YWTM_PREMIUM' ) ) {
                $admin_tabs['premium-landing'] = __( 'Premium Version', 'yith-woocommerce-tab-manager' );
            }

            $args = array(
                'create_menu_page' => true,
                'parent_slug' => '',
                'page_title' => __( 'Tab Manager', 'yith-woocommerce-tab-manager' ),
                'menu_title' => __( 'Tab Manager', 'yith-woocommerce-tab-manager' ),
                'capability' => 'manage_options',
                'parent' => '',
                'parent_page' => 'yit_plugin_panel',
                'page' => $this->_panel_page,
                'admin-tabs' => $admin_tabs,
                'options-path' => YWTM_DIR . '/plugin-options'
            );

            $this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
        }

        /**
         * plugin_row_meta
         *
         * add the action links to plugin admin page
         *
         * @param $plugin_meta
         * @param $plugin_file
         * @param $plugin_data
         * @param $status
         *
         * @return   Array
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use plugin_row_meta
         */
        public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status )
        {
            if( ( defined( 'YWTM_INIT' ) && ( YWTM_INIT == $plugin_file ) ) ||
                ( defined( 'YWTM_FREE_INIT' ) && ( YWTM_FREE_INIT == $plugin_file ) )
            ) {

                $plugin_meta[] = '<a href="' . $this->_official_documentation . '" target="_blank">' . __( 'Plugin Documentation', 'yith-woocommerce-tab-manager' ) . '</a>';
            }

            return $plugin_meta;
        }

        /**
         * Premium Tab Template
         *
         * Load the premium tab template on admin page
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  void
         */
        public function premium_tab()
        {
            $premium_tab_template = YWTM_TEMPLATE_PATH . '/admin/' . $this->_premium;
            if( file_exists( $premium_tab_template ) ) {
                include_once( $premium_tab_template );
            }
        }

        /**
         * Action Links
         *
         * add the action links to plugin admin page
         *
         * @param $links | links plugin array
         *
         * @return   mixed Array
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return mixed
         * @use plugin_action_links_{$plugin_file_name}
         */
        public function action_links( $links )
        {

            $links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-woocommerce-tab-manager' ) . '</a>';

            $premium_live_text = defined( 'YWTM_FREE_INIT' ) ? __( 'Premium live demo', 'yith-woocommerce-tab-manager' ) : __( 'Live demo', 'yith-woocommerce-tab-manager' );

            $links[] = '<a href="' . $this->_premium_live_demo . '" target="_blank">' . $premium_live_text . '</a>';

            if( defined( 'YWTM_FREE_INIT' ) ) {
                $links[] = '<a href="' . $this->get_premium_landing_uri() . '" target="_blank">' . __( 'Premium Version', 'yith-woocommerce-tab-manager' ) . '</a>';
            }

            return $links;
        }

        /**
         * Get the premium landing uri
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function get_premium_landing_uri()
        {
            return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing_url . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing_url;
        }

        public function enqueue_admin_style(){

            wp_register_style( 'yit-tab-style', YWTM_ASSETS_URL . 'css/yith-tab-manager-admin.css', array(), YWTM_VERSION );

            $current_screen = get_current_screen();

            if( isset( $current_screen->post_type ) &&  ( 'ywtm_tab' == $current_screen->post_type || 'product' == $current_screen->post_type ) ){

                wp_enqueue_style( 'yit-tab-style' );

            }

        }


	    /**
	     * add_tab_metabox
	     * Register metabox for global tab
	     * @author YITHEMES
	     * @since 1.0.0
	     */
	    public function add_tab_metabox()
	    {
		    $args = include_once( YWTM_INC . '/metabox/tab-metabox.php' );

		    if( !function_exists( 'YIT_Metabox' ) ) {
			    require_once( YWTM_DIR . 'plugin-fw/yit-plugin.php' );
		    }
		    $metabox = YIT_Metabox( 'yit-tab-manager-setting' );
		    $metabox->init( $args );

	    }

        /**
         * Returns single instance of the class
         * @author Salvatore Strano
         * @return YITH_WCTM_Admin
         * @since 2.0.0
         */
        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }

            return self::$instance;
        }



    }
}


/**
 * @return YITH_WCTM_Admin| YITH_WCTM_Admin_Premium
 */
function YITH_Tab_Manager_Admin(){

    if( defined('YWTM_PREMIUM' ) && class_exists('YITH_WCTM_Admin_Premium' ) ){
        return  YITH_WCTM_Admin_Premium::get_instance();
    }else{
        return YITH_WCTM_Admin::get_instance();
    }
}