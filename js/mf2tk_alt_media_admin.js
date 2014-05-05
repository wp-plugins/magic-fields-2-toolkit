function mf2tk_refresh_media(e){
    var m=jQuery("div.mf2tk-media",e.get(0).parentNode);
    var v=jQuery("div.mf2tk-media video",e.get(0).parentNode);
    var s=jQuery("div.mf2tk-media audio",e.get(0).parentNode);
    var p=jQuery("img.mf2tk-poster",e.get(0).parentNode);
    if(m.length){
        jQuery.post(ajaxurl,{action:'mf2tk_alt_media_admin_refresh',field:e.attr("name"),url:e.val()},function(r){
            m.html(r);
            v=jQuery("video",m.get(0));
            s=jQuery("audio",m.get(0));
        });
    }else if(p.length){
        p.attr("src",e.val());
    }
}

jQuery(document).ready(function(){
    // Select media from Media Library
    // adapted from http://stackoverflow.com/questions/13847714/wordpress-3-5-custom-media-upload-for-your-theme-options
    jQuery('button.mf2tk-media-library-button').click(function(){
        var i=jQuery(this).attr("id").replace(".media-library-button","");
        var e=jQuery("#"+i);
        n=e.attr("name").replace("magicfields[","").replace("mf2tk_","").replace(/\]/,"");
        var t=null;
        if(e.hasClass("mf2tk-video")){t="video";}
        else if(e.hasClass("mf2tk-audio")){t="audio";}
        else if(e.hasClass("mf2tk-img")){t="img";}
        var custom_uploader = wp.media({title:"Select "+t+" for "+n,button:{text:"Set "+n+" to Selected"},multiple:false})
        .on('select',function(){
            var a=custom_uploader.state().get('selection').first().toJSON();
            e.val(a.url);
            //mf2tk_refresh_media(e);
        })
        .open();
        return false;
    });
    // Reload media using URL from input box
    jQuery("button.mf2tk-alt_media_admin-refresh").click(function(e){
        var i=jQuery(this).attr("id").replace(".refresh-button","");
        var e=jQuery("#"+i);
        mf2tk_refresh_media(e);
        return false;
    });
    // Show/Hide panes
    jQuery("button.mf2tk-field_value_pane_button").click(function(event){
        if(jQuery(this).text()=="Show"){
            jQuery(this).text("Hide");
            jQuery("div.mf2tk-field_value_pane",this.parentNode).css("display","block");
        }else{
            jQuery(this).text("Show");
            jQuery("div.mf2tk-field_value_pane",this.parentNode).css("display","none");
        }
        return false;
    });
});

jQuery(document).ready(function(){
    jQuery("button.mf2tk-alt_embed_admin-refresh").click(function(){
        var embed=jQuery("div.mf2tk-alt_embed_admin-embed",this.parentNode);
        jQuery.post(ajaxurl,{action:'mf2tk_alt_embed_admin_refresh',
            field:jQuery("input.mf2tk-alt_embed_admin-url",this.parentNode).attr("name"),
            url:jQuery("input.mf2tk-alt_embed_admin-url",this.parentNode).val()},function(response){
                embed.html(response);
            });
        return false;
    });
});