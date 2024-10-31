<?php
namespace ORFW\Admin;

class Lists
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
        add_filter( 'manage_orfw_review_posts_columns',       array( $this, 'columns') );
        add_action( 'manage_orfw_review_posts_custom_column', array( $this, 'content'), 10, 2 );
        add_action( 'post_row_actions',                       array( $this, 'actions'), 10, 2 );
        add_filter( 'bulk_actions-edit-orfw_review',          array( $this, 'bulkActions'), 10, 2 );
    }

    public function columns( $columns )  
    {
        return array(
            'cb'       => true,
            'order_id' => esc_html__( 'Order ID', 'order-reviews-for-woocommerce' ),
            'feedback' => esc_html__( 'Feedback', 'order-reviews-for-woocommerce' ),
            'rating'   => esc_html__( 'Rating',   'order-reviews-for-woocommerce' ),
            'customer' => esc_html__( 'Customer', 'order-reviews-for-woocommerce' ),
            'time'     => esc_html__( 'Time',     'order-reviews-for-woocommerce' )
        );
    }

    public function content( $column, $reviewID )
    {
        $orderID = get_post_meta( $reviewID, 'orfw_order_id', true );
        $review  = get_post( $reviewID );
        $order   = wc_get_order( $orderID );

        switch ( $column )
        {
            case 'order_id':
                echo sprintf( '<a href="%1$s"><strong>%2$s</strong></a>',
                    esc_url( get_edit_post_link( $orderID ) ),
                    esc_html( "#{$orderID} {$order->get_customer_first_name()} {$order->get_customer_last_name()}")
                );
                break;

            case 'feedback':
                echo empty($review->post_content) ? '-' : esc_html( $review->post_content );
                break;

            case 'rating':
                $stars = intval(get_post_meta( $reviewID, 'orfw_rating', true ));
                for ( $i = 1; $i <= 5; $i++ )
                { 
                    echo ($i > $stars) ? '<span class="orfw-star-empty"></span>' : '<span class="orfw-star"></span>';
                }
                break;

            case 'customer':
                echo sprintf( '<a href="%1$s">%2$s</a>',
                    get_edit_user_link( $order->get_customer_id() ),
                    esc_html( "{$order->get_customer_first_name()} {$order->get_customer_last_name()}", 'order-reviews-for-woocommerce' )
                );
                break;
                
            case 'time':
                echo esc_html( date('M j Y, h:i A', strtotime($review->post_date)));
                break;
        }
    }

    public function actions( $actions, $post )
    {
        global $current_screen;

        if ( 'orfw_review' != $current_screen->post_type )
            return $actions;

        return array();
    }

    public function bulkActions( $actions )
    {
        return array();
    }
}
