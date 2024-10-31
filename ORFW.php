<?php
/**
 * ORFW class
 *
 * @class ORFW The class that holds the entire plugin
 */
final class ORFW
{
    public static $instance;
    public $version = '1.0.0';

    /**
     * Singleton Pattern
     *
     * @return object
     */
    public static function getInstance()
    {
        if ( !self::$instance instanceof self )
            self::$instance = new self();

        return self::$instance;
    }

    /**
     * Constructor for the ORFW class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    public function __construct()
    {
        $this->defineConstants();
        $this->includes();

        register_activation_hook( __FILE__,   array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'plugins_loaded', array( $this, 'bootSystem' ) );
        add_action( 'plugins_loaded', array( $this, 'run' ) );

        add_action( 'admin_menu', array( $this, 'activeMenu' ) );
        
        add_action( 'activated_plugin',      array( $this, 'activationRedirect' ) );
    
        add_filter( 'plugin_row_meta', array( $this, 'helpLinks' ), 10, 2 );
        add_filter( 'plugin_action_links_' . plugin_basename(__DIR__) . '/order-reviews-for-woocommerce.php', array( $this, 'settingLink' ) );
    }

    /**
     * Define the constants
     * @return void
     */
    public function defineConstants()
    {
        define( 'ORFW_VERSION',       $this->version );
        define( 'ORFW_FILE',          __FILE__ );
        define( 'ORFW_PATH',          dirname( ORFW_FILE ) );
        define( 'ORFW_CLASSES',       ORFW_PATH . '/classes' );
        define( 'ORFW_ADMIN_CLASSES', ORFW_CLASSES . '/Admin' );
        define( 'ORFW_FRONT_CLASSES', ORFW_CLASSES . '/Front' );
        define( 'ORFW_URL',           plugins_url( '', ORFW_FILE ) );
        define( 'ORFW_RESOURCES',     ORFW_URL . '/resources' );
        define( 'ORFW_RENDER',        ORFW_PATH . '/render' );
        define( 'ORFW_RENDER_FRONT',  ORFW_RENDER . '/Front' );
    }

    /**
     * Boots System
     */
    public function bootSystem()
    {
        if ( !class_exists('woocommerce') )
        {
            add_action( 'admin_notices', array( $this, 'requiredWoocommerce' ) );
            return;
        }
    }

    /**
     * Load the plugin after all plugis are loaded
     *
     * @return void
     */
    public function run()
    {
        if ( !class_exists('woocommerce') )
            return;
        
        add_action( 'init', array( $this, 'init_classes' ) );
        add_action( 'init', array( $this, 'localization_setup' ) );
        add_action( 'init', array( $this, 'register_new_post_types' ) );
    }

    /**
     * Include the required files
     *
     * @return void
     */
    public function includes()
    {
        include_once ORFW_ADMIN_CLASSES . '/Initialize.class.php';
        include_once ORFW_ADMIN_CLASSES . '/Settings.class.php';
        include_once ORFW_ADMIN_CLASSES . '/Lists.class.php';

        include_once ORFW_FRONT_CLASSES . '/Initialize.class.php';
        include_once ORFW_FRONT_CLASSES . '/Popup.class.php';
        include_once ORFW_FRONT_CLASSES . '/ReviewInfo.class.php';
        include_once ORFW_FRONT_CLASSES . '/Order.class.php';

        include_once ORFW_CLASSES       . '/Resources.class.php';
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes()
    {
        if ( $this->is_request( 'admin' ) )
        {
            \ORFW\Admin\Initialize::getInstance();
            \ORFW\Admin\Settings::getInstance();
            \ORFW\Admin\Lists::getInstance();
        }

        if ( $this->is_request( 'front' ) )
        {
            \ORFW\Front\Initialize::getInstance();
            \ORFW\Front\Popup::getInstance();
            \ORFW\Front\ReviewInfo::getInstance();
            \ORFW\Front\Order::getInstance();
        }

        \ORFW\Resources::getInstance();
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup()
    {
        load_plugin_textdomain('order-reviews-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Create a non-editable custom post type after plugin activated.
     *
     * @uses load_plugin_textdomain()
     */
    public function register_new_post_types()
    {
        register_post_type( 'orfw_review', array(
            'label'               => esc_html__( 'Order Reviews', 'order-reviews-for-woocommerce' ),
            'description'         => esc_html__( 'Reviews of orders posted by customers.', 'order-reviews-for-woocommerce' ),
            'labels'              => array(
                'name'               => esc_html__( 'Order Reviews', 'order-reviews-for-woocommerce' ),
                'singular_name'      => esc_html__( 'Order Review', 'order-reviews-for-woocommerce' ),
                'menu_name'          => esc_html__( 'Order Reviews', 'order-reviews-for-woocommerce' ),
                'name_admin_bar'     => esc_html__( 'Order Review', 'order-reviews-for-woocommerce' ),
                'parent_item_colon'  => esc_html__( 'Parent Order Review:', 'order-reviews-for-woocommerce' ),
                'all_items'          => esc_html__( 'Reviews', 'order-reviews-for-woocommerce' ),
                'add_new_item'       => esc_html__( 'Add New review', 'order-reviews-for-woocommerce' ),
                'add_new'            => esc_html__( 'Add New review', 'order-reviews-for-woocommerce' ),
                'new_item'           => esc_html__( 'New review', 'order-reviews-for-woocommerce' ),
                'edit_item'          => esc_html__( 'Edit review', 'order-reviews-for-woocommerce' ),
                'update_item'        => esc_html__( 'Update review', 'order-reviews-for-woocommerce' ),
                'view_item'          => esc_html__( 'View review', 'order-reviews-for-woocommerce' ),
                'search_items'       => esc_html__( 'Search review', 'order-reviews-for-woocommerce' ),
                'not_found'          => esc_html__( 'No Reviews found', 'order-reviews-for-woocommerce' ),
                'not_found_in_trash' => esc_html__( 'Not Reviews found in Trash', 'order-reviews-for-woocommerce' ),
            ),
            'supports'            => array( 'title', 'editor', 'author' ),
            'show_in_rest'        => true,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'show_in_menu' => 'orfw-settings'
        ) );
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate()
    {
        $installed = get_option( 'orfw_installed' );

        if ( ! $installed )
            update_option( 'orfw_installed', time() );

        update_option( 'orfw_version', ORFW_VERSION );
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {}

    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or front.
     *
     * @return bool
     */
    private function is_request( $type )
    {
        switch ( $type )
        {
            case 'admin' :
                return is_admin();

            case 'ajax' :
                return defined( 'DOING_AJAX' );

            case 'rest' :
                return defined( 'REST_REQUEST' );

            case 'cron' :
                return defined( 'DOING_CRON' );

            case 'front' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

    function requiredWoocommerce()
    {
        if ( !class_exists('woocommerce') )
        { 
            ?>
                <div class="orfw-plugin-required-notice notice notice-warning">
                    <div class="orfw-admin-notice-content">
                    <h2><?php echo esc_html__('ORFW Required dependency.', 'order-reviews-for-woocommerce'); ?></h2>
                    <p><?php echo esc_html__('Please ensure you have the WooCommerce plugin installed and activated.', 'order-reviews-for-woocommerce'); ?></p>
                    </div>
                </div>
            <?php 
        }
    }

    /**
     * Redirect to plugin page on activation
     *
     */
    public function activationRedirect( $plugin ) 
    {
        if ( plugin_basename(__DIR__) . '/order-reviews-for-woocommerce.php' == $plugin && class_exists('woocommerce') )
            exit( wp_redirect( admin_url( '/admin.php?page=orfw-settings' ) ) );
    }

    /**
     * Setting page link in plugin list
     *
     */
    public function settingLink( $links ) 
    {
	    $links[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( '/admin.php?page=orfw-settings' ) ), esc_html__( 'Settings','order-reviews-for-woocommerce' ) );
	    return $links;
	}

    /**
     * Plugin row links
     *
     */
    public function helpLinks( $links, $plugin )
    {
        if ( plugin_basename( __DIR__ ) . '/order-reviews-for-woocommerce.php' != $plugin )
            return $links;
        
        $links[] = sprintf( '<a href="%s">%s</a>', esc_url( '//docs.jompha.com/order-reviews-for-woocommerce' ), esc_html__( 'Docs','order-reviews-for-woocommerce' ) );
        $links[] = sprintf( '<a href="%s">%s</a>', esc_url( '//forum.jompha.com' ), esc_html__( 'Community support','order-reviews-for-woocommerce' ) );

        return $links;
    }

    /**
     * License menu after activation
     */
    function activeMenu()
    {
        add_menu_page(
			esc_html__( 'Order Reviews for WooCommerce', 'order-reviews-for-woocommerce' ), 
			esc_html__( 'ORFW', 'order-reviews-for-woocommerce' ), 
			'manage_woocommerce', 
			'orfw-settings', 
			array( \ORFW\Admin\Settings::getInstance(), 'renderPage' ), 
			'dashicons-feedback', 
			56
		);
    }
}
