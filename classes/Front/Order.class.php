<?php
namespace ORFW\Front;

class Order
{
    public static $instance;
    public $orderID = null;

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

    private function __construct()
    {
        add_action( 'woocommerce_new_order', array( $this, 'onOrder' ) );
    }

    public function onOrder( $orderID )
    {
        $this->orderID = $orderID;
        update_post_meta( intval($orderID), 'orfw_is_order', true );
        return true;
    }
}
