// This file contains the JavaScript for the toolkit's Administrator's Page

jQuery(document).ready(function(){
    jQuery("div#mf2tk-unreferenced-files").tabs({active:2});
    jQuery("button.mf2tk-delete-mf-files").click(function(e){
        if(jQuery(this).text()==="Select All"){
            jQuery("input[type='checkbox'].mf2tk-delete-mf-files").prop("checked",true);
        }if(jQuery(this).text()==="Clear All"){
            jQuery("input[type='checkbox'].mf2tk-delete-mf-files").prop("checked",false);
        }
        return false;
    });
    
    // clicking the "input#mf2tk-sync-fields" button will send an AJAX request to synchronize the toolkit's fields
    // with the fields of "Magic Fields 2"
    
    jQuery("input#mf2tk-sync-fields").click(function(e){
        jQuery.post(ajaxurl,{action:"mf2tk_sync_fields"},function(r){alert(r);});
        e.preventDefault();
        return false;
    });
});