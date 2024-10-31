<?php
namespace ORFW\Front;

class Popup
{
    public static $instance;
    public $orderID = null;
    public $order;

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
        if ( ! $this->hasOrdered() )
            return;

        add_action( 'wp_head',                           array( $this, 'colorVariables' ), 0 );
        add_action( 'wp_footer',                         array( $this, 'view') );
        add_action( 'wp_ajax_orfw_review_submit',        array( $this, 'onSubmit' ));
        add_action( 'wp_ajax_nopriv_orfw_review_submit', array( $this, 'onSubmit' ));
    }

    public function hasOrdered()
    {
        if ( !wp_get_current_user()->exists() )
            return false;
        
        $lastOrder = get_posts( array(
            'numberposts' => 1,
            'meta_key'    => '_customer_user',
            'meta_value'  => get_current_user_id(),
            'order'       => 'DESC',
            'post_type'   => 'shop_order',
            'post_status' => 'wc-completed',
            'fields'      => 'ids',
            'meta_query'  => array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'orfw_reviewed',
                        'value'   => true,
                        'compare' => '!=',
                    ),
                    array(
                        'key'     => 'orfw_reviewed',
                        'compare' => 'NOT EXISTS',
                    )
                ),
                array(
                    'key'     => 'orfw_is_order',
                    'value'   => true,
                )
            ),
        ) );

        if ( !count($lastOrder) )
        {
            $lastOrder = get_posts( array(
                'numberposts' => 1,
                'meta_key'    => '_customer_user',
                'meta_value'  => get_current_user_id(),
                'order'       => 'DESC',
                'post_type'   => 'shop_order',
                'post_status' => 'wc-completed',
                'fields'      => 'ids',
                'meta_query'  => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'orfw_reviewed',
                        'value'   => true,
                        'compare' => '!=',
                    ),
                    array(
                        'key'     => 'orfw_reviewed',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
                'date_query' => array(
                    array(
                        'after'     => date('Y-m-d', strtotime('-3 days')),  
                        'inclusive' => true,
                    ),
                ),
            ) );
        }

        if ( !isset($lastOrder[0]) )
            return false;
    
        $this->orderID = $lastOrder[0];
        $this->order   = wc_get_order( $this->orderID );

        return true;
    }

    public function view()
    {   
        if ( $this->isWaitPeriod() || $this->isAgainPeriod() )
            return;
      
        include_once ORFW_RENDER_FRONT . '/markup/popup-design-1.php';
    }

    private function isWaitPeriod()
    {
        return ( strtotime( '+' . get_option( 'orfw_template_wait_period', 3 ) . ' hours', strtotime( $this->order->get_date_completed() ) ) > current_time( 'timestamp' ) ) ? true : false;
    }

    private function isAgainPeriod()
    {
        return ( isset($_COOKIE['orfw-template-again-period']) ) ? true : false;
    }

    public function colorVariables()
    {   
        if ( 'yes' !== get_option( 'orfw_template_use_custom_colors', 'no' ) ) 
            return;
    ?>
    <style>
    :root {
        --orfw-template-header-background-color: <?php echo wp_kses( get_option( 'orfw_template_header_background_color', '#f4b248' ), array() ); ?>;
        --orfw-template-body-background-color: <?php echo wp_kses( get_option( 'orfw_template_body_background_color', '#ffffff' ), array() ); ?>;
        --orfw-template-header-text-color: <?php echo wp_kses( get_option( 'orfw_template_header_text_color', '#442a00' ), array() ); ?>;
        --orfw-template-header-highlighted-text-color: <?php echo wp_kses( get_option( 'orfw_template_header_highlight_text_color', '#ffffff' ), array() ); ?>;
        --orfw-template-body-text-color: <?php echo wp_kses( get_option( 'orfw_template_body_text_color', '#442a00' ), array() ); ?>;
        --orfw-template-submit-background-color: <?php echo wp_kses( get_option( 'orfw_template_submit_background_color', '#f4b248' ), array() ); ?>;
        --orfw-template-submit-text-color: <?php echo wp_kses( get_option( 'orfw_template_submit_text_color', '#ffffff' ), array() ); ?>;
        --orfw-template-small-text-color: <?php echo wp_kses( get_option( 'orfw_template_small_text_color', '#888888' ), array() ); ?>;
        --orfw-template-skip-text-color: <?php echo wp_kses( get_option( 'orfw_template_skip_text_color', '#f4b248' ), array() ); ?>;
    }
    </style>
    <?php
    }

    public function onSubmit()
    {
        $customer  = wp_get_current_user();

        if ( ! $customer->exists() )
            $this->errorJson();

        if ( ! isset( $_POST['order_id'], $_POST['product_ids'], $_POST['feedback'], $_POST['rating'] ) )
            $this->errorJson();
        
        $orderID    = intval( $_POST['order_id'] );
        $productIDs = orfw_sanitize_array($_POST['product_ids']);

        if ( empty( $orderID ) || get_post_type($orderID) != 'shop_order' || !is_array($productIDs) || empty( $productIDs ) )
            $this->errorJson();

        update_post_meta( intval($orderID), 'orfw_products', $productIDs );

        $reviewIDs    = array();
        $orfwReviewID = wp_insert_post( array(
            'post_title'   => sanitize_title("#{$orderID} {$customer->user_login}"),
            'post_content' => sanitize_text_field( $_POST['feedback'] ),
            'post_status'  => 'publish',
            'post_type'    => 'orfw_review'
        ) );

        if ( ! $orfwReviewID )
            $this->errorJson();
        
        update_post_meta( intval($orderID),      'orfw_reviewed', true );
        update_post_meta( intval($orfwReviewID), 'orfw_order_id', intval($orderID) );
        update_post_meta( intval($orfwReviewID), 'orfw_rating',   intval($_POST['rating']) );

        foreach ( $productIDs as $productID )
        {
            if ( !comments_open( $productID ) )
                continue;

            $reviewID = wp_insert_comment( array(
                'comment_post_ID'      => intval($productID),
                'comment_type'         => 'review',
                'comment_content'      => sanitize_text_field( $_POST['feedback'] ),
                'comment_parent'       => 0,
                'comment_date'         => date('Y-m-d H:i:s'),
                'user_id'              => $customer->ID,
                'comment_author'       => $customer->user_login,
                'comment_author_email' => $customer->user_email,
                'comment_author_url'   => $customer->user_url,
                'comment_meta'         => array(
                    'rating'           => intval($_POST['rating']),
                    'orfw_order_id'    => intval($orderID),
                ),
                'comment_approved'     => 1,
            ) );

            if ( is_wp_error( $reviewID ) )
                continue;
            
            $reviewIDs[] = intval($reviewID);
        }
                
        $this->successJson();
    }

    private function successJson( $array = array() )
    {
        echo wp_json_encode( array_merge( array(
            'success' => true,
            'error'   => false
        ), $array ) );
        wp_die();
    }

    private function errorJson( $message = '' )
    {
        echo wp_json_encode( array(
            'success' => false,
            'error'   => true,
            'message' => esc_html($message)
        ) );
        wp_die();
    }
}
