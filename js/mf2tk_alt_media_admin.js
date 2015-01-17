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

function mf2tkInsertHowToUse(root){
    if(typeof mf2tkDisableHowToUse === "undefined"||!mf2tkDisableHowToUse){
        var template=
       '<div style="clear:both;"></div>\
        <div class="mf2tk-field-input-optional">\
            <button class="mf2tk-field_value_pane_button">Open</button>\
            <h6>How to Use with the Toolkit\'s Shortcode</h6>\
            <div class="mf2tk-field_value_pane" style="display:none;clear:both;">\
                <input type="text" class="mf2tk-how-to-use" size="50" readonly\
                    value=\'[show_custom_field field="$#fieldName#$#index#"$#filter#$#separator#$#before#$#after#$#field_before#$#field_after#]\'><br>\
                - <button class="mf2tk-how-to-use">select,</button> copy and paste this into editor above in\
                    <strong>Text</strong> mode\
            </div>\
        </div>';
        jQuery("div.mf-field-ui",root).each(function(){
            jQuery(this).find("div.text_field_mf").each(function(){
                var $this=jQuery(this);
                if($this.parents("div.mf-field-ui").find("div.mf2tk-field-input-optional").length){return;}
                var name=$this.find("input[type='text']")[0].name;
                var matches=name.match(/magicfields\[(\w+)\]\[(\d+)\]\[(\d+)\]/);
                var groupIndex=parseInt(matches[2]);
                var fieldIndex=parseInt(matches[3]);
                var args={
                    fieldName:matches[1],
                    index:groupIndex===1&&fieldIndex===1?'':"<"+groupIndex+","+fieldIndex+">",
                    filter:'',
                    separator:'',
                    before:'',
                    after:''
                };
                var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                    if(args.hasOwnProperty(match1)){return args[match1];}
                    return '';
                });
                jQuery(this.parentNode).append(html);
            });
            jQuery(this).find("div.image_wrap").each(function(){
                var $this=jQuery(this);
                if($this.parents("div.mf-field-ui").find("div.mf2tk-field-input-optional").length){return;}
                var name=$this.parents("div.image_layout").find("div.image_input div.mf_custom_field input[type='hidden']")[0].name;
                var matches=name.match(/^magicfields\[(\w+)\]\[(\d+)\]\[(\d+)\]$/);
                var groupIndex=parseInt(matches[2]);
                var fieldIndex=parseInt(matches[3]);
                var args={
                    fieldName:matches[1],
                    index:groupIndex===1&&fieldIndex===1?"":"<"+groupIndex+","+fieldIndex+">",
                    before:' before="<img src=&#39;"',
                    after:' after="&#39;>"'
                };
                var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                    if(args.hasOwnProperty(match1)){return args[match1];}
                    return '';
                });
                $this.parents("div.image_layout").append(html);
            });
            jQuery(this).find("div.file_input").each(function(){
                var $this=jQuery(this);
                if($this.parents("div.mf-field-ui").find("div.mf2tk-field-input-optional").length){return;}
                var name=$this.find("input[type='hidden']")[0].name;
                var matches=name.match(/^magicfields\[(\w+)\]\[(\d+)\]\[(\d+)\]$/);
                if(!matches){return;}
                var groupIndex=parseInt(matches[2]);
                var fieldIndex=parseInt(matches[3]);
                var args={
                    fieldName:matches[1],
                    index:groupIndex===1&&fieldIndex===1?"":"<"+groupIndex+","+fieldIndex+">",
                    filter:' filter="url_to_link2"'
                };
                var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                    if(args.hasOwnProperty(match1)){return args[match1];}
                    return '';
                });
                jQuery(this.parentNode).append(html);
            });
            jQuery(this).find("div.mf-checkbox-list-box").each(function(){
                var $this=jQuery(this);
                if($this.parents("div.mf-field-ui").find("div.mf2tk-field-input-optional").length){return;}
                var name=$this.find("input.checkbox_list_mf[type='checkbox']")[0].name;
                var matches=name.match(/magicfields\[(\w+)\]\[(\d+)\]\[(\d+)\]/);
                var groupIndex=parseInt(matches[2]);
                var fieldIndex=parseInt(matches[3]);
                var args={
                    fieldName:matches[1],
                    index:groupIndex===1&&fieldIndex===1?'':"<"+groupIndex+","+fieldIndex+">",
                    filter:'',
                    separator:' separator=", "',
                    before:'',
                    after:''
                };
                var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                    if(args.hasOwnProperty(match1)){return args[match1];}
                    return '';
                });
                jQuery(this.parentNode).append(html);
            });
            jQuery(this).find("div.mf-dropdown-box").each(function(){
                var $this=jQuery(this);
                if($this.parents("div.mf-field-ui").find("div.mf2tk-field-input-optional").length){return;}
                var select=$this.find("select.dropdown_mf");
                var name=select[0].name;
                var matches=name.match(/magicfields\[(\w+)\]\[(\d+)\]\[(\d+)\]\[\]/);
                if(!matches){return;}
                var groupIndex=parseInt(matches[2]);
                var fieldIndex=parseInt(matches[3]);
                var args={
                    fieldName:matches[1],
                    index:groupIndex===1&&fieldIndex===1?'':"<"+groupIndex+","+fieldIndex+">",
                    filter:'',
                    separator:select.attr('multiple')?' separator=", "':'',
                    before:'',
                    after:''
                };
                var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                    if(args.hasOwnProperty(match1)){return args[match1];}
                    return '';
                });
                jQuery(this.parentNode).append(html);
            });
            jQuery(this).find("div.mf-dropdown-box").each(function(){
                var $this=jQuery(this);
                if($this.parents("div.mf-field-ui").find("div.mf2tk-field-input-optional").length){return;}
                var select=$this.find("select.dropdown_mf");
                var name=select[0].name;
                var matches=name.match(/^magicfields\[(\w+)\]\[(\d+)\]\[(\d+)\]$/);
                if(!matches){return;}
                var option=select.find("option[value!='']").first();
                var groupIndex=parseInt(matches[2]);
                var fieldIndex=parseInt(matches[3]);
                var args={
                    fieldName:matches[1],
                    index:groupIndex===1&&fieldIndex===1?'':"<"+groupIndex+","+fieldIndex+">",
                    filter:jQuery.isNumeric(option.val())?' filter="url_to_link2"':''
                };
                var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                    if(args.hasOwnProperty(match1)){return args[match1];}
                    return '';
                });
                jQuery(this.parentNode).append(html);
            });
            jQuery(this).find("label.mf-radio-field").first().each(function(){
                var $this=jQuery(this);
                if($this.parents("div.mf-field-ui").find("div.mf2tk-field-input-optional").length){return;}
                var name=$this.find("input[type='radio']")[0].name;
                var matches=name.match(/^magicfields\[(\w+)\]\[(\d+)\]\[(\d+)\]$/);
                if(!matches){return;}
                var groupIndex=parseInt(matches[2]);
                var fieldIndex=parseInt(matches[3]);
                var args={
                    fieldName:matches[1],
                    index:groupIndex===1&&fieldIndex===1?'':"<"+groupIndex+","+fieldIndex+">",
                    filter:'',
                    separator:'',
                    before:'',
                    after:''
                };
                var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                    if(args.hasOwnProperty(match1)){return args[match1];}
                    return '';
                });
                jQuery(this.parentNode.parentNode).append(html);
            });
            jQuery(this).find("input.datepicker_mf[type='text']").parent().each(function(){
                var $this=jQuery(this);
                if($this.parents("div.mf-field-ui").find("div.mf2tk-field-input-optional").length){return;}
                var name=$this.find("input[type='hidden']")[0].name;
                var matches=name.match(/^magicfields\[(\w+)\]\[(\d+)\]\[(\d+)\]$/);
                if(!matches){return;}
                var groupIndex=parseInt(matches[2]);
                var fieldIndex=parseInt(matches[3]);
                var args={
                    fieldName:matches[1],
                    index:groupIndex===1&&fieldIndex===1?'':"<"+groupIndex+","+fieldIndex+">",
                    filter:'',
                    separator:'',
                    before:'',
                    after:''
                };
                var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                    if(args.hasOwnProperty(match1)){return args[match1];}
                    return '';
                });
                jQuery(this.parentNode).append(html);
            });
            jQuery(this).find("div.multiline_custom_field").each(function(){
                var $this=jQuery(this);
                if($this.parents("div.mf-field-ui").find("div.mf2tk-field-input-optional").length){return;}
                var name=$this.find("textarea.mf_editor")[0].name;
                var matches=name.match(/^magicfields\[(\w+)\]\[(\d+)\]\[(\d+)\]$/);
                if(!matches){return;}
                var groupIndex=parseInt(matches[2]);
                var fieldIndex=parseInt(matches[3]);
                var args={
                    fieldName:matches[1],
                    index:groupIndex===1&&fieldIndex===1?'':"<"+groupIndex+","+fieldIndex+">",
                    before:' before="<div style=&#39;border:2px solid black;padding:5px;&#39;>"',
                    after:' after="</div>"'
                };
                var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                    if(args.hasOwnProperty(match1)){return args[match1];}
                    return '';
                });
                jQuery(this.parentNode).append(html);
            });
            jQuery(this).find("div.markItUp").each(function(){
                var $this=jQuery(this);
                if($this.parents("div.mf-field-ui").find("div.mf2tk-field-input-optional").length){return;}
                var name=$this.find("textarea.markdowntextboxinterface")[0].name;
                var matches=name.match(/^magicfields\[(\w+)\]\[(\d+)\]\[(\d+)\]$/);
                if(!matches){return;}
                var groupIndex=parseInt(matches[2]);
                var fieldIndex=parseInt(matches[3]);
                var args={
                    fieldName:matches[1],
                    index:groupIndex===1&&fieldIndex===1?'':"<"+groupIndex+","+fieldIndex+">",
                    before:' before="<div style=&#39;border:2px solid black;padding:5px;&#39;>"',
                    after:' after="</div>"'
                };
                var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                    if(args.hasOwnProperty(match1)){return args[match1];}
                    return '';
                });
                jQuery(this.parentNode).append(html);
            });
            jQuery(this).find("input.checkbox_mf[type='checkbox']").each(function(){
                var $this=jQuery(this);
                if($this.parents("div.mf-field-ui").find("div.mf2tk-field-input-optional").length){return;}
                var matches=this.name.match(/^magicfields\[(\w+)\]\[(\d+)\]\[(\d+)\]$/);
                var groupIndex=parseInt(matches[2]);
                var fieldIndex=parseInt(matches[3]);
                var args={
                    fieldName:matches[1],
                    index:groupIndex===1&&fieldIndex===1?'':"<"+groupIndex+","+fieldIndex+">",
                    filter:' filter="tk_value_as_checkbox"',
                    field_before:' field_before="<!--$Field-->:"'
                };
                var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                    if(args.hasOwnProperty(match1)){return args[match1];}
                    return '';
                });
                jQuery(this.parentNode.parentNode).append(html);
            });
            jQuery(this).find("input.clrpckr").each(function(){
                var $this=jQuery(this);
                if($this.parents("div.mf-field-ui").find("div.mf2tk-field-input-optional").length){return;}
                var matches=this.name.match(/^magicfields\[(\w+)\]\[(\d+)\]\[(\d+)\]$/);
                var groupIndex=parseInt(matches[2]);
                var fieldIndex=parseInt(matches[3]);
                var args={
                    fieldName:matches[1],
                    index:groupIndex===1&&fieldIndex===1?'':"<"+groupIndex+","+fieldIndex+">",
                    before:' before="<div style=&#39;display:inline-block;width:0.66em;height:0.66em;padding:0;border:1px solid black;background-color:"',
                    after:' after=";&#39></div>"',
                    field_before:' field_before="<!--$Field-->:"'
                };
                var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                    if(args.hasOwnProperty(match1)){return args[match1];}
                    return '';
                });
                jQuery(this.parentNode).append(html);
            });
            jQuery(this).find("div.image_input.audio_frame").each(function(){
                var $this=jQuery(this);
                if($this.parents("div.mf-field-ui").find("div.mf2tk-field-input-optional").length){return;}
                var name=$this.find("input[type='hidden']")[0].name;
                var matches=name.match(/^magicfields\[(\w+)\]\[(\d+)\]\[(\d+)\]$/);
                if(!matches){return;}
                var groupIndex=parseInt(matches[2]);
                var fieldIndex=parseInt(matches[3]);
                var args={
                    fieldName:matches[1],
                    index:groupIndex===1&&fieldIndex===1?'':"<"+groupIndex+","+fieldIndex+">",
                    filter:' filter="tk_value_as_audio"'
                };
                var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                    if(args.hasOwnProperty(match1)){return args[match1];}
                    return '';
                });
                jQuery(this.parentNode).append(html);
            });
            jQuery(this).find("div.mf_slider_field").parent().each(function(){
                var $this=jQuery(this);
                if($this.parents("div.mf-field-ui").find("div.mf2tk-field-input-optional").length){return;}
                var name=$this.find("input[type='hidden']")[0].name;
                var matches=name.match(/^magicfields\[(\w+)\]\[(\d+)\]\[(\d+)\]$/);
                if(!matches){return;}
                var groupIndex=parseInt(matches[2]);
                var fieldIndex=parseInt(matches[3]);
                var args={
                    fieldName:matches[1],
                    index:groupIndex===1&&fieldIndex===1?'':"<"+groupIndex+","+fieldIndex+">",
                };
                var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                    if(args.hasOwnProperty(match1)){return args[match1];}
                    return '';
                });
                jQuery(this).append(html);
            });
        });
    }
}

jQuery(document).ready(function(){
    mf2tkInsertHowToUse(document.body);
    if(typeof mf2tkDisableHowToUse==="undefined"||!mf2tkDisableHowToUse){
        var template=
       '<div style="clear:both;"></div>\
        <div class="mf2tk-field-input-optional">\
            <button class="mf2tk-field_value_pane_button">Open</button>\
            <h6>How to Use with the Toolkit\'s Shortcode</h6>\
            <div class="mf2tk-field_value_pane" style="display:none;clear:both;">\
                <input type="text" class="mf2tk-how-to-use" size="50" readonly\
                    value=\'[show_custom_field field="$#fieldName#$#index#"$#filter#$#separator#$#before#$#after#$#field_before#$#field_after#]\'><br>\
                - <button class="mf2tk-how-to-use">select,</button> copy and paste this into editor above in\
                    <strong>Text</strong> mode\
            </div>\
        </div>';
        
        jQuery("div#postbox-container-1").find("div.postbox[id^='tagsdiv-']").each(function(){
            var args={
                fieldName:this.id.substr(8),
                separator:' separator=", "'
            };
            var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                if(args.hasOwnProperty(match1)){return args[match1];}
                return '';
            });
            jQuery(this).append(html);            
        });
        jQuery("div#postbox-container-1").find("div.postbox[id$='div']").each(function(){
            if(this.id==="submitdiv"){return;}
            var args={
                fieldName:this.id.substr(0,this.id.length-3),
                separator:' separator=", "'
            };
            var html=template.replace(/\$#(\w+)#/g,function(match,match1){
                if(args.hasOwnProperty(match1)){return args[match1];}
                return '';
            });                
            jQuery(this).append(html);            
        });
    }
    // Show/Hide panes
    jQuery("button.mf2tk-field_value_pane_button").click(function(event){
        if(jQuery(this).text()=="Open"){
            jQuery(this).text("Hide");
            jQuery("div.mf2tk-field_value_pane",this.parentNode).css("display","block");
        }else{
            jQuery(this).text("Open");
            jQuery("div.mf2tk-field_value_pane",this.parentNode).css("display","none");
        }
        return false;
    });
    jQuery("button.mf2tk-how-to-use").click(function(){
        jQuery(this.parentNode).find("input.mf2tk-how-to-use, textarea.mf2tk-how-to-use")[0].select();
        return false;
    });
    jQuery("button.mf2tk-refresh-table-shortcode").click(function(){
        var inputId="input"+this.id.substr(6);
        var fields="";
        var filters="";
        jQuery(this).parents("div.mf2tk-field_value_pane").each(function(){
            jQuery(this).find("fieldset.mf2tk-configure.mf2tk-fields input[type='checkbox']").each(function(){
                var input=jQuery(this);
                if(input.prop("checked")){
                    if(fields){fields+=";";}
                    fields+=input.prop("value");
                }
            });
            jQuery(this).find("fieldset.mf2tk-configure.mf2tk-filters input[type='checkbox']").each(function(){
                var input=jQuery(this);
                if(input.prop("checked")){
                    var name=input.prop("value");
                    if(name==="tk_value_as_image__"||name==="tk_value_as_video__"){
                        if(jQuery(this.parentNode.parentNode).find("input[type='radio'][value='width']").prop("checked")){
                            name+="w";
                        }else{
                            name+="h";
                        }
                        name+=jQuery(this.parentNode.parentNode).find("input[type='number']").val().trim();
                    }
                    if(filters){filters+=";";}
                    filters+=name;
                }
            });
        });
        var textarea=jQuery(this).parents("div.mf-field-ui").find("textarea.mf2tk-how-to-use.mf2tk-table-shortcode");
        var text=textarea[0].textContent;
        text=text.replace(/field="([\w;]*)"/,function(match,old){
            return 'field="'+fields+'"';
        });
        text=text.replace(/filter="([\w;]*)"/,function(match,old){
            return 'filter="'+filters+'"';
        });
        textarea[0].textContent=text;
        jQuery("input#"+inputId).val("field="+fields+"|filter="+filters);
        return false;
    });
    jQuery("div.mf2tk-dragable-field").draggable({cursor:"crosshair",revert:true});
    jQuery("div.mf2tk-dragable-field-after").droppable({accept:"div.mf2tk-dragable-field",tolerance:"touch",
        hoverClass:"mf2tk-hover",drop:function(e,u){
            jQuery(this.parentNode).after(u.draggable);
    }});
});

function mf2tk_resize_mejs_video_elements(selector){
  var f=function(){
    var v=jQuery(selector);
    if(!v.length){return;}
    if(v[0].videoWidth&&v[0].videoHeight){
      var e=v.parents("div.mejs-container");
      if(e.length){
        v=v[0];
        v.height=(v.videoHeight/v.videoWidth)*v.width;
        e[0].style.height=v.height+"px";
        e.parents("div.wp-video")[0].style.height=v.height+"px";
        e.find("div.mejs-layer").css("height",v.height+"px");
        return;
      }
    }
    window.setTimeout(f,1000);
  };
  f();
}

// adapted from magic-fields-2/js/mf_field_base.js

jQuery(document).ready(function($) {    
  $(document).on("click",'a.duplicate-field',function(){
    id = jQuery(this).attr("id");
    pattern =  /mf\_field\_repeat\-(([0-9]+)\_([0-9]+)\_([0-9]+)\_([0-9]+))/i;
    var item =  pattern.exec(id);

    group_id = item[2];
    group_index = item[3];
    field_id = item[4];
    counter_id = '#mf_counter_'+group_id+'_'+group_index+'_'+field_id;
    
    var field=$($(counter_id)[0].parentNode);
    var count=field.find("div.mf-field-ui").length;
    var check=function(){
        var duplicates=field.find("div.mf-field-ui");
        if(duplicates.length>count){
            duplicate=$(duplicates[count]);
            if(!duplicate.find("div.mf2tk-field-input-main").length){
                mf2tkInsertHowToUse(duplicate[0].parentNode);
            }
            duplicate.find("button.mf2tk-field_value_pane_button").click(function(event){
                if(jQuery(this).text()=="Open"){
                    jQuery(this).text("Hide");
                    jQuery("div.mf2tk-field_value_pane",this.parentNode).css("display","block");
                }else{
                    jQuery(this).text("Open");
                    jQuery("div.mf2tk-field_value_pane",this.parentNode).css("display","none");
                }
                return false;
            });
            duplicate.find("button.mf2tk-how-to-use").click(function(){
                jQuery(this.parentNode).find("input.mf2tk-how-to-use, textarea.mf2tk-how-to-use")[0].select();
                return false;
            });
            return false;
        }
        window.setTimeout(check,1000);
    };
    window.setTimeout(check,1000);
  });
});
