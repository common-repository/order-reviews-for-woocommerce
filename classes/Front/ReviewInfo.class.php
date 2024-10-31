<?php
namespace ORFW\Front;

class ReviewInfo
{
    public static $instance;
    private $enabled;

    public static function getInstance()
    {
        if ( !self::$instance instanceof self )
            self::$instance = new self();
        
        return self::$instance;
    }

    private function __construct()
    {
        add_action( 'wp_head',                               array( $this, 'colorVariables' ), 0 );
        add_action( 'woocommerce_review_after_comment_text', array( $this, 'view' ) );

        $this->enabled = true;
    }

    public function colorVariables()
    {
        if ( 'yes' !== get_option( 'orfw_template_use_custom_colors', 'no' ) ) 
            return;

        switch( get_option( 'orfw_sri_font_size', 'medium' ) )
        {
            case 'large':
                $fontSize = '2rem';

            case 'small':
                $fontSize = '1rem';

            default:
                $fontSize = '1.25rem';
        }

        ?>
        <style>
            :root {
                --orfw-review-font-size: <?php echo esc_html( $fontSize ); ?>;
                --orfw-review-font-style: <?php echo wp_kses( get_option( 'orfw_sri_font_style', 'normal' ), array()); ?>;
                --orfw-review-font-color: <?php echo wp_kses( get_option( 'orfw_sri_font_color', '#28303d' ), array() ); ?>;
                --orfw-review-font-link-color: <?php echo wp_kses( get_option( 'orfw_sri_link_color', '#28303d' ), array() ); ?>;
                --orfw-review-background: <?php echo wp_kses( get_option( 'orfw_sri_card_bg', 'transparent' ), array() ); ?>;
            }
        </style>
        <?php
    }

    public function view( $comment )
    {
        $reviewID = intval($comment->comment_ID);
        $orderID  = get_comment_meta( $reviewID, 'orfw_order_id', true );
        $products = get_post_meta( $orderID, 'orfw_products', true );

        if ( !$products )
            return;

        $key = array_search( intval($comment->comment_post_ID), $products );

        if ( $key !== false )
            unset( $products[ $key ] );

        $i  = 0;
        $t  = count($products);
        $px = array();
        
        if (! $products || $t == 0)
            return;

        foreach ($products as $productID)
        {
            $i++;

            $product = \wc_get_product($productID);

            if ( !$product )
                continue;

            $px[]  = array(
                'id'   => $product->get_id(),
                'name' => $product->get_name(),
                'url'  => $product->get_permalink(),
            );

            if ($i > 3)
                break;
        }

        shuffle($px);
    ?>
    <div class="orfw-review-info">
    <?php
        switch ($t)
        {
            case 0:
                break;
            
            case 1:
                echo sprintf(
                    esc_html__('This user also bought %s.', 'order-reviews-for-woocommerce'),
                    '<a href="' . esc_url($px[0]['url']) . '" target="_blank">' . esc_html($px[0]['name']) . '</a>'
                );
                break;
            
            case 2:
                echo sprintf(
                    esc_html__('This user also bought %s and %s.', 'order-reviews-for-woocommerce'),
                    '<a href="' . esc_url($px[0]['url']) . '" target="_blank">' . esc_html($px[0]['name']) . '</a>',
                    '<a href="' . esc_url($px[1]['url']) . '" target="_blank">' . esc_html($px[1]['name']) . '</a>'
                );
                break;
            
            case 3:
                echo sprintf(
                    esc_html__('This user also bought %s, %s, and %s.', 'order-reviews-for-woocommerce'),
                    '<a href="' . esc_url($px[0]['url']) . '" target="_blank">' . esc_html($px[0]['name']) . '</a>',
                    '<a href="' . esc_url($px[1]['url']) . '" target="_blank">' . esc_html($px[1]['name']) . '</a>',
                    '<a href="' . esc_url($px[2]['url']) . '" target="_blank">' . esc_html($px[2]['name']) . '</a>'
                );
                break;
            
            default:
                echo sprintf(
                    esc_html__('This user also bought %s, %s, %s and %s other items.', 'order-reviews-for-woocommerce'),
                    '<a href="' . esc_url($px[0]['url']) . '" target="_blank">' . esc_html($px[0]['name']) . '</a>',
                    '<a href="' . esc_url($px[1]['url']) . '" target="_blank">' . esc_html($px[1]['name']) . '</a>',
                    '<a href="' . esc_url($px[2]['url']) . '" target="_blank">' . esc_html($px[2]['name']) . '</a>',
                    ($t - 3)
                );
                break;
        }
    ?>
    </div>
    <?php
    }
}
