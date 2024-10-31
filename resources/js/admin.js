;jQuery(document).ready(function()
{
    'use strict';
    
    if ( jQuery('.jmph-icons input').val() !== '' )
        jQuery('.jmph-icons li .' + jQuery('.jmph-icons').find('input').val() ).parent('li').addClass('active');
    
    jQuery('.jmph-icons-selector').find('li').on('click', function()
    {
        var icon   = jQuery(this),
            icons  = icon.parents('.jmph-icons'),
            iconId = icon.find('span').attr('class');

        icons.find('li').removeClass('active');
        icon.addClass('active');

        icons.find('input').val( iconId );
    });
});