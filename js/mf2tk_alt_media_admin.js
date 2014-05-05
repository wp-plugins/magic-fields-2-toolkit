console.log("mf2tk_alt_media_admin.js");

function mf2tk_refresh_media(e){
    console.log("mf2tk_alt_media_admin.js:mf2tk_refresh_media():e.val()="+e.val());
    var m=jQuery("div.mf2tk-media",e.get(0).parentNode);
    var v=jQuery("div.mf2tk-media video",e.get(0).parentNode);
    var s=jQuery("div.mf2tk-media audio",e.get(0).parentNode);
    var p=jQuery("img.mf2tk-poster",e.get(0).parentNode);
    console.log("mf2tk_alt_media_admin.js:mf2tk_refresh_media():m.length="+m.length);
    console.log("mf2tk_alt_media_admin.js:mf2tk_refresh_media():v.length="+v.length);
    console.log("mf2tk_alt_media_admin.js:mf2tk_refresh_media():s.length="+s.length);
    console.log("mf2tk_alt_media_admin.js:mf2tk_refresh_media():p.length="+p.length);
    if(m.length){
        if(v.length){console.log("mf2tk_alt_media_admin.js:mf2tk_refresh_media():v.attr(\"src\")="+v.attr("src"));}
        if(s.length){console.log("mf2tk_alt_media_admin.js:mf2tk_refresh_media():s.attr(\"src\")="+s.attr("src"));}
        //if(v.length){v.attr("src",a.url);}
        //if(s.length){s.attr("src",a.url);}
        console.log("mf2tk_alt_media_admin.js:mf2tk_refresh_media():m.html()="+m.html());
        jQuery.post(ajaxurl,{action:'mf2tk_alt_media_admin_refresh',field:e.attr("name"),url:e.val()},function(r){
            console.log("action:'mf2tk_alt_media_admin_refresh':r="+r);
            m.html(r);
            v=jQuery("video",m.get(0));
            s=jQuery("audio",m.get(0));
            if(v.length){console.log("mf2tk_alt_media_admin.js:mf2tk_refresh_media():v.attr(\"src\")="+v.attr("src"));}
            if(s.length){console.log("mf2tk_alt_media_admin.js:mf2tk_refresh_media():s.attr(\"src\")="+s.attr("src"));}
        });
    }else if(p.length){
        console.log("mf2tk_alt_media_admin.js:mf2tk_refresh_media():p.attr(\"src\")="+p.attr("src"));
        p.attr("src",e.val());
        console.log("mf2tk_alt_media_admin.js:mf2tk_refresh_media():p.attr(\"src\")="+p.attr("src"));
    }
}

jQuery(document).ready(function(){
    // Select media from Media Library
    // adapted from http://stackoverflow.com/questions/13847714/wordpress-3-5-custom-media-upload-for-your-theme-options
    jQuery('button.mf2tk-media-library-button').click(function(){
        var i=jQuery(this).attr("id").replace(".media-library-button","");
        console.log("mf2tk_alt_media_admin.js:.mf2tk-media-library-button:click:i="+i);
        var e=jQuery("#"+i);
        n=e.attr("name").replace("magicfields[","").replace("mf2tk_","").replace(/\]/,"");
        console.log("mf2tk_alt_media_admin.js:.mf2tk-media-library-button:click:n="+n);
        var t=null;
        if(e.hasClass("mf2tk-video")){t="video";}
        else if(e.hasClass("mf2tk-audio")){t="audio";}
        else if(e.hasClass("mf2tk-img")){t="img";}
        console.log("mf2tk_alt_media_admin.js:.mf2tk-media-library-button:click:t="+t);
        var custom_uploader = wp.media({title:"Select "+t+" for "+n,button:{text:"Set "+n+" to Selected"},multiple:false})
        .on('select',function(){
            var a=custom_uploader.state().get('selection').first().toJSON();
            console.log("mf2tk_alt_media_admin.js:.on('select'):a.url="+a.url);
            e.val(a.url);
            //mf2tk_refresh_media(e);
        })
        .open();
        return false;
    });
    // Reload media using URL from input box
    jQuery("button.mf2tk-alt_media_admin-refresh").click(function(e){
        var i=jQuery(this).attr("id").replace(".refresh-button","");
        console.log("mf2tk_alt_media_admin.js:button.mf2tk-alt_media_admin-refresh:click:i="+i);
        var e=jQuery("#"+i);
        mf2tk_refresh_media(e);
        return false;
    });
    // Show/Hide panes
    jQuery("button.mf2tk-field_value_pane_button").click(function(event){
        console.log("button.mf2tk-field_value_pane_button:click");
        if(jQuery(this).text()=="Show"){
            console.log("button.mf2tk-field_value_pane_button:click:Show");
            jQuery(this).text("Hide");
            jQuery("div.mf2tk-field_value_pane",this.parentNode).css("display","block");
        }else{
            console.log("button.mf2tk-field_value_pane_button:click:Hide");
            jQuery(this).text("Show");
            jQuery("div.mf2tk-field_value_pane",this.parentNode).css("display","none");
        }
        return false;
    });
});

jQuery(document).ready(function(){
    jQuery("button.mf2tk-alt_embed_admin-refresh").click(function(){
        var embed=jQuery("div.mf2tk-alt_embed_admin-embed",this.parentNode);
        console.log("button.mf2tk-alt_embed_admin-refresh:click:embed.html()="+embed.html());
        jQuery.post(ajaxurl,{action:'mf2tk_alt_embed_admin_refresh',
            field:jQuery("input.mf2tk-alt_embed_admin-url",this.parentNode).attr("name"),
            url:jQuery("input.mf2tk-alt_embed_admin-url",this.parentNode).val()},function(response){
                console.log("action:'mf2tk_alt_embed_admin_refresh':response="+response);
                embed.html(response);
            });
        return false;
    });
});