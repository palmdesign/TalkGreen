jQuery(document).ready(function($) {
    
    var sb_divi_cfd_div = jQuery('#poststuff').detach();
    
    sb_divi_cfd_div.insertAfter('form#post');
    
    jQuery('form#post').remove();
    
    jQuery( ".sb_divi_cfd_convert" ).wrap( '<form class="sb_divi_cfd_copy_form" method="POST"></form>' );
});