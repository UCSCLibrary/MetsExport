jQuery(document).ready(function() {
 

    jQuery('#make-adm-button').after(jQuery('#make-desc-button'));


    jQuery('#make-adm-button').click(function(event){
	event.preventDefault();
        jQuery('#desc-meta option:selected').each( function() {
            jQuery('#adm-meta').append("<option value='"+jQuery(this).val()+"'>"+jQuery(this).text()+"</option>");
            jQuery(this).remove();
        });
    });



    jQuery('#make-desc-button').click(function(event){
	event.preventDefault();
        jQuery('#adm-meta option:selected').each( function() {
            jQuery('#desc-meta').append("<option value='"+jQuery(this).val()+"'>"+jQuery(this).text()+"</option>");
            jQuery(this).remove();
        });
    });



    jQuery('#install_plugin').click(function(event){
	if(jQuery('#adm-meta').length > 0) {
	    event.preventDefault();
	    jQuery("#adm-meta > option").each(function() {
    		jQuery('form').append('<input type="hidden" name="admElements[]" value="'+jQuery(this).text()+'"/>');
	    });
	    jQuery("#adm-meta > optgroup").children('option').each(function() {
    		jQuery('form').append('<input type="hidden" name="admElements[]" value="'+jQuery(this).text()+'"/>');
	    });
	    jQuery('form').submit();
	}
    });



});
