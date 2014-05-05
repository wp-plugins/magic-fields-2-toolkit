jQuery(document).ready(function(){
    jQuery("button.mf2tk-delete-mf-files").click(function(e){
        if(jQuery(this).text()==="Select All"){
            jQuery("input[type='checkbox'].mf2tk-delete-mf-files").prop("checked",true);
        }if(jQuery(this).text()==="Clear All"){
            jQuery("input[type='checkbox'].mf2tk-delete-mf-files").prop("checked",false);
        }
        return false;
    });
});