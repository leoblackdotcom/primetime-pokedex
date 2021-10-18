jQuery(document).ready(function() {
    $profileMatchHeight = jQuery('.poke-image-placeholder').outerHeight();
    jQuery('.poke-image-description').height($profileMatchHeight);
    jQuery('.poke-image-placeholder').height($profileMatchHeight);

    jQuery('.poke-flip').click( function() {
        jQuery('.poke-image').toggleClass('activate');
        jQuery('.swap-button').toggleClass('activate');
    })
});



function copyToClipboard(element) {
    var $temp = jQuery("<input>");
    jQuery("body").append($temp);
    $temp.val(jQuery(element).text()).select();
    document.execCommand("copy");
    $temp.remove();
}