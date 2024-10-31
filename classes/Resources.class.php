<?php
namespace ORFW;

class Resources
{
    public static $instance;

    public static function getInstance()
    {
        if ( !self::$instance instanceof self )
            self::$instance = new self();

        return self::$instance;
    }

    /**
     * Singleton Pattern
     *
     * @return object
     */
    private function __construct()
    {
        if ( is_admin() )
            add_action( 'admin_enqueue_scripts', array( $this, 'register' ), 5 );
        else
            add_action( 'wp_enqueue_scripts', array( $this, 'register' ), 5 );
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function register()
    {
        $this->registerScripts( $this->scripts() );
        $this->registerStyles( $this->styles() );
    }

    /**
     * Register scripts
     *
     * @param  array $scripts
     *
     * @return void
     */
    private function registerScripts( $scripts )
    {
        foreach ( $scripts as $handle => $script )
        {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : ORFW_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
        }

        $this->localize();

    }

    private function localize()
    {
        $fields = \ORFW\Admin\Settings::fields();
        $prefix = \ORFW\Admin\Settings::$optPrefix;
        $data = array( 
            'ajaxurl'             => admin_url( 'admin-ajax.php' ),
            'text_write_feedback' => esc_html__( 'Please write a feedback.', 'order-reviews-for-woocommerce' ),
            'text_rate_order'     => esc_html__( 'Please add a rating.', 'order-reviews-for-woocommerce' ),
        );
        
        
        foreach( $fields as $field )
        {
            if( ! $field['show_in_js'] )
                continue;
            
            $data[ $field['id'] ] = get_option( $prefix . $field['id'], (isset($field['value'])) ? $field['value'] : '' );
        }

        wp_localize_script( 'orfw-front', 'orfw_data', $data);
    }


    /**
     * Register styles
     *
     * @param  array $styles
     *
     * @return void
     */
    public function registerStyles( $styles )
    {
        foreach ( $styles as $handle => $style )
        {
            $deps = isset( $style['deps'] ) ? $style['deps'] : false;
            wp_register_style( $handle, $style['src'], $deps, $style['version'] );
        }
    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    
    public function scripts()
    {
        $scripts = array(
            'orfw' => array(
                'src'       => ORFW_RESOURCES . '/js/orfw.js',
                'deps'      => array( 'jquery' ),
                'version'   => filemtime( ORFW_PATH . '/resources/js/orfw.js' ),
                'in_footer' => true
            ),
            'orfw-admin' => array(
                'src'       => ORFW_RESOURCES . '/js/admin.js',
                'deps'      => array( 'jquery' ),
                'version'   => filemtime( ORFW_PATH . '/resources/js/admin.js' ),
                'in_footer' => true
            ),
            'owl-carousel' => array(
                'src'       => ORFW_RESOURCES . '/js/owl.carousel.min.js',
                'deps'      => array( 'jquery' ),
                'version'   => filemtime( ORFW_PATH . '/resources/js/owl.carousel.min.js' ),
                'in_footer' => true
            ),
            'orfw-front' => array(
                'src'       => ORFW_RESOURCES . '/js/front.js',
                'deps'      => array( 'jquery' ),
                'version'   => filemtime( ORFW_PATH . '/resources/js/front.js' ),
                'in_footer' => true
            )
        );

        return $scripts;
    }

    /**
     * Get registered styles
     *
     * @return array
     */
    public function styles()
    {
        $styles = array(
            'owl-carousel' => array(
                'src'     => ORFW_RESOURCES . '/css/owl.carousel.min.css',
                'version' => filemtime( ORFW_PATH . '/resources/css/owl.carousel.min.css' )
            ),
            'orfw-font-roboto' => array(
                'src'     => '//fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;1,300&display=swaps',
                'version' => ORFW_VERSION
            ),
            'jompha-admin-core' => array(
                'src'     => ORFW_RESOURCES . '/css/jompha-admin-core.css',
                'version' => filemtime( ORFW_PATH . '/resources/css/jompha-admin-core.css' ),
            ),
            'jompha-icons' => array(
                'src'     => ORFW_RESOURCES . '/css/icons.css',
                'version' => filemtime( ORFW_PATH . '/resources/css/icons.css' ),
            ),
            'orfw' => array(
                'src'     => ORFW_RESOURCES . '/css/orfw.css',
                'version' => filemtime( ORFW_PATH . '/resources/css/orfw.css' ),
            ),
            'orfw-front' => array(
                'src'     => ORFW_RESOURCES . '/css/front.css',
                'version' => filemtime( ORFW_PATH . '/resources/css/front.css' ),
            ),
        );

        return $styles;
    }
}
