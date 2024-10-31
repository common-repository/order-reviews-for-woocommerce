<?php
namespace ORFW\Front;

class Initialize
{
    public static $instance;

    public static function getInstance()
    {
        if ( !self::$instance instanceof self )
            self::$instance = new self();
        
        return self::$instance;
    }

    private function __construct()
    {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
    }
    
    public function enqueue()
    {
        $this->styles();
        $this->scripts();
    }

    private function styles()
    {   
        wp_enqueue_style( 'owl-carousel' );
        wp_enqueue_style( 'orfw-font-roboto' );
        wp_enqueue_style( 'orfw' );
        wp_enqueue_style( 'orfw-front' );
    }

    private function scripts()
    {
        wp_enqueue_script( 'owl-carousel' );
        wp_enqueue_script( 'orfw' );
        wp_enqueue_script( 'orfw-front' );
    }

}
