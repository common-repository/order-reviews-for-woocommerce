<div id="orfw-popup" class="orfw-use-<?php echo esc_attr( get_option( 'orfw_template_use_custom_colors', 'no' ) == 'yes' ) ? 'custom' : 'default'; ?>-colors hide">
    <div class="orfw-popup-inner">
        <div class="orfw-popup-header">
            
            <div class="orfw-popup-order-info">
                <h3><?php $customText = get_option( 'orfw_text_last_order_heading', '' ); echo (empty($customText)) ? esc_html__( 'Your Last Order', 'order-reviews-for-woocommerce' ) : esc_html( $customText ); ?></h3>
                <h1 data-order-id="<?php echo intval( $this->orderID ); ?>" id="order-id">#<?php echo intval( $this->orderID ); ?></h1>
                <?php if ( get_option( 'orfw_template_show_time', 'yes' ) == 'yes' ) : ?>
                <p><?php echo esc_html( time_elapsed_string( '@' . strtotime($this->order->get_date_completed()) ) ); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="orfw-popup-order-products">
                <div class="owl-carousel owl-loaded">
                    <?php
                    $order = $this->order;

                    foreach ( $order->get_items() as $item )
                    {
                        $product = $item->get_product();

                        if ( ! is_object($product) )
                            continue;
                    ?>

                    <div class="orfw-product item" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
                        <?php 
                        echo wp_kses( 
                            $product->get_image('thumbnail'), 
                            array(
                                'img' => array(
                                    'src' => array()
                                )
                            ) 
                        );
                        ?>
                    </div>

                    <?php 
                    } 
                    ?>
                </div>
            </div>
        </div>

        <div class="orfw-popup-footer">
            <h2><?php $customText = get_option( 'orfw_text_rate_order_heading', '' ); echo (empty($customText)) ? esc_html__( 'Rate the order', 'order-reviews-for-woocommerce' ) : esc_html( $customText ); ?></h2>

            <div class="orfw-popup-rating">
                
                <div class="feedback">
                    <div class="rating">
                        <input type="radio" name="rating" id="rating-5" value="5">
                        <label for="rating-5"></label>
                        <input type="radio" name="rating" id="rating-4" value="4">
                        <label for="rating-4"></label>
                        <input type="radio" name="rating" id="rating-3" value="3">
                        <label for="rating-3"></label>
                        <input type="radio" name="rating" id="rating-2" value="2">
                        <label for="rating-2"></label>
                        <input type="radio" name="rating" id="rating-1" value="1">
                        <label for="rating-1"></label>
                        <div class="emoji-wrapper">
                            <div class="emoji">
                                <img class="rating-0" src="<?php echo esc_url(ORFW_RESOURCES . '/images/rating-0.png') ?>">
                                <img class="rating-1" src="<?php echo esc_url(ORFW_RESOURCES . '/images/rating-1.png') ?>">
                                <img class="rating-2" src="<?php echo esc_url(ORFW_RESOURCES . '/images/rating-2.png') ?>">
                                <img class="rating-3" src="<?php echo esc_url(ORFW_RESOURCES . '/images/rating-3.png') ?>">
                                <img class="rating-4" src="<?php echo esc_url(ORFW_RESOURCES . '/images/rating-4.png') ?>">
                                <img class="rating-5" src="<?php echo esc_url(ORFW_RESOURCES . '/images/rating-5.png') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <textarea name="rating_comment " id="orfw-popup-comment" cols="20" rows="5" placeholder="<?php $customText = get_option( 'orfw_text_write_feedback', '' ); echo (empty($customText)) ? esc_html__( 'Write Feedback', 'order-reviews-for-woocommerce' ) : esc_html( $customText ); ?>"></textarea>
            </div>

            <p class="orfw-popup-error-wrapper">
                <span class="dashicons dashicons-info-outline"></span> <span class="orfw-popup-error-text"></span>
            </p>

            <button id="orfw-template-submit-button">
                <span><?php echo esc_html__( 'Submit', 'order-reviews-for-woocommerce' ); ?></span>
                <span id="orfw-popup-submit-save-icon" class="dashicons"></span>
            </button>
            
            <h4 class="disclaimer"><?php $customText = get_option( 'orfw_text_footer', '' ); echo (empty($customText)) ? esc_html__( 'Please provide your honest feedback!', 'order-reviews-for-woocommerce' ) : esc_html( $customText ); ?></h4>
            
            <?php if ( get_option( 'orfw_force_review', 'no' ) != 'yes' ) { ?>
            <a id="orfw-popup-skip" href=""><?php echo esc_html__( 'Skip', 'order-reviews-for-woocommerce' ); ?></a>
            <?php } ?>
        </div>

    </div>
</div>