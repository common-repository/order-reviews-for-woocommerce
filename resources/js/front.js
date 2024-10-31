; jQuery(document).ready(function () {
    'use strict';

    if (!jQuery('#orfw-popup').length)
        return;
    
    var orfwSliderItems = jQuery('.owl-carousel').children('.item'),
        orfwSetSliderItems = (orfwSliderItems.length <= 4) ? {items: orfwSliderItems.length, width: true} : { items: 4, width: false },
        orfwSliderLoop = ( orfwSliderItems.length > 3 ) ? true : false,
        orfwSliderCenter = (orfwSliderItems.length > 3 || orfwSliderItems.length == 1) ? true : false;

    jQuery('.owl-carousel').owlCarousel({
        loop: orfwSliderLoop,
        margin: 20,
        center: orfwSliderCenter,
        items: orfwSetSliderItems.items,
        autoplay: orfwSliderLoop,
        autoWidth: orfwSetSliderItems.width,
        autoHeight: true,
        autoplayTimeout:1000,
        autoplayHoverPause: true,
        dots: false,
    });

    if ( orfwSliderItems.length === 2 )
        orfwSliderItems.last().parent('.owl-item').addClass('mrzero');
    

    var orfwPopup = jQuery('#orfw-popup'),
        orfwPopupError = jQuery('.orfw-popup-error-wrapper'),
        ratingStars = jQuery('#orfw-popup .feedback input'),
        feedback = jQuery('#orfw-popup #orfw-popup-comment'),
        submitButton = jQuery('#orfw-template-submit-button'),
        orfwFrequency = orfw_data.template_view_frequency == '' ? 3 : parseInt( orfw_data.template_view_frequency ),
        orfwFrequencyCount = orfwGetCookie('orfw-template-view-frequency') == null ? 0 : parseInt( orfwGetCookie('orfw-template-view-frequency') );

    ratingStars.on('click', function() 
    {
        feedback.fadeIn();
        orfwPopupError.hide();
    });


    //rating
    ratingStars.on('change', function ()
    {
        var orfwRating = jQuery(this).val();
        var orfwFeedback = feedback.val();
        orfwPopupError.hide();
    });

    //feedback box
    feedback.on('keyup', function ()
    {
        var orfwFeedback = jQuery(this).val();
        orfwPopupError.hide();
    });

    //submit button
    submitButton.on('click', function() 
    {
        if ( !jQuery('#orfw-popup .feedback input:checked').length )
        {
            orfwPopupError
                .find('.orfw-popup-error-text')
                    .text(orfw_data.text_rate_order)
            .end()
            .show();
            return;
        }
        
        var orfwOrderId          = jQuery('#order-id').data('order-id'),
            orfwProductIds       = [],
            orfwFeedback         = feedback.val(),
            orfwRating           = parseInt(jQuery('#orfw-popup .feedback input:checked').val()),
            orfwForceFeedback    = (orfw_data.template_force_feedback == 'yes') ? true : false,
            orfwForceBadFeedback = (orfw_data.template_force_bad_feedback == 'yes') ? true : false;

        if ( orfwForceFeedback && !orfwFeedback.length)
        {
            orfwPopupError
                .find('.orfw-popup-error-text')
                    .text(orfw_data.text_write_feedback)
            .end()
            .show();
            
            feedback.addClass('orfw-shakeX').focus();
            return;
        }

        if (orfwForceBadFeedback && jQuery('#orfw-popup .feedback input:checked').val() < 4 && !orfwFeedback.length)
        {
            orfwPopupError
                .find('.orfw-popup-error-text')
                    .text(orfw_data.text_write_feedback)
            .end()
            .show();

            feedback.addClass('orfw-shakeX').focus();
            return;
        }
        
        jQuery('.orfw-popup-order-products').find('.orfw-product').each(function()
        {
            if ( orfwProductIds.includes( jQuery(this).data('product-id') ) )
                return;
            
            orfwProductIds.push( jQuery(this).data('product-id') );
        });

        jQuery.ajax({
            type: 'post',
            url: orfw_data.ajaxurl,
            data: {
                action: 'orfw_review_submit',
                order_id: orfwOrderId,
                product_ids: orfwProductIds,
                feedback: orfwFeedback,
                rating: orfwRating,
            },
            beforeSend()
            {
                jQuery('#orfw-popup-submit-save-icon').addClass('dashicons-arrow-right-alt2 orfw-horizontal-bounce');
                submitButton.prop('disabled', true);
            },
            dataType: 'json',
            success: function(response)
            {
                submitButton.css('background', '#29af81');
                    
                jQuery('#orfw-popup-submit-save-icon').addClass('dashicons-yes-alt');
                
                setTimeout(() => {
                    //orfwPopup.fadeOut();
                }, 3000);
            },
            complete: function()
            {
                jQuery('#orfw-popup-submit-save-icon').removeClass('dashicons-arrow-right-alt2 orfw-horizontal-bounce');
            }
        });
    });

    if ( orfwFrequency == 0 || orfwFrequencyCount < orfwFrequency )
        orfwPopup.removeClass('hide');

    jQuery(document).on('click', '#orfw-popup-skip', function (e)
    {   
        e.preventDefault();

        var orfwPopupContainer = jQuery('#orfw-popup');
        orfwPopupContainer.fadeOut();

        if ( '' == orfw_data.template_again_period )
            orfwSetCookie( 'orfw-template-again-period', 'yes', (parseInt(orfw_data.template_again_period) * 60 * 60) );

        if ( orfwFrequency > 0 )
            orfwSetCookie( 'orfw-template-view-frequency', (orfwFrequencyCount + 1), ( 30 * 24 * 60 * 60 ) );
    });
});
