jQuery(document).ready(function() {
 

    jQuery('#make-adm-button').after(jQuery('#make-desc-button'));


    jQuery('#make-adm-button').click(function(event){
	event.preventDefault();
	jQuery('#updateDialogDiv').dialog('open');
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

    jQuery('#updateDialogDiv').dialog({
        autoOpen: false,
        buttons: {
            'Update': function() {
		jQuery('#desc-meta option:selected').each( function() {
		    jQuery('#adm-meta').append("<option value='"+jQuery(this).val()+"'>"+jQuery(this).text()+"</option>");
		    jQuery(this).remove();
		    jQuery('form').append('<input type="hidden" name="adm_type_'+jQuery(this).text().replace(/ /g,'')+'" value="'+jQuery("input[name=updateDialog]").val()+'"/>');
		});
		jQuery(this).dialog('close');
	    },

            'Cancel': function () {
                jQuery(this).dialog('close');
            }
        },
        width: '800px'
    }); //end update dialog  


});
