jQuery('.poke-flip').click( function() {
    jQuery('.poke-image').toggleClass('activate');
    jQuery('.swap-button').toggleClass('activate');
})

$profileMatchHeight = jQuery('.poke-image-placeholder').outerHeight();
jQuery('.poke-image-description').height($profileMatchHeight);
jQuery('.poke-image-placeholder').height($profileMatchHeight);