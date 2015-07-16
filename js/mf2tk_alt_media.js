// WordPress uses MediaElement.js to implement its [video] and [audio] shortcodes
// mf2tkResizeVideo() resizes the MediaElement.js video element and its containers
// aspectRatio is used only if the video does not have values for width and height

function mf2tkResizeVideo(id,aspectRatio,doWidth){
  aspectRatio=typeof aspectRatio==="undefined"?3/4:aspectRatio;
  doWidth=typeof doWidth==="undefined"?false:doWidth;
  var jqVideo=jQuery(id);
  if(!jqVideo.length){return;}
  var loadedmetadata=false;
  jqVideo.on("loadedmetadata",function(){
    loadedmetadata=true;
  });
  var f=function(){
    var container=jqVideo.parents("div.mejs-container");
    if(!container.length){
      // mediaelement.js hasn't run yet so retry later
      window.setTimeout(f,1000);
      return;
    }
    var v=jqVideo[0];
    if(v.videoWidth&&v.videoHeight){   // get the actual aspect ratio if the video element has width and height
      aspectRatio=v.videoWidth/v.videoHeight;
      if(loadedmetadata){var done=true;}
    }
    if(!doWidth){
      var width=v.width;
      var height=Math.floor(width/aspectRatio);
      v.height=height;
    }else{
      var height=v.height;
      var width=Math.floor(height*aspectRatio);
      v.width=width;
    }
    var pxWidth=width+"px";
    var pxHeight=height+"px";
    var pxHeight1=(height-30)+"px";   // div.mejs-layer.mejs-overlay-play is 30px undersized
    container.css({height:pxHeight,width:pxWidth});
    container.parents("div.wp-video").css({height:pxHeight,width:pxWidth});
    container.find("div.mejs-layer").css({height:pxHeight,width:pxWidth});
    container.find("div.mejs-layer.mejs-overlay-play").css({height:pxHeight1,width:pxWidth});   // div.mejs-layer.mejs-overlay-play is 30px undersized
    container.find("div.me-cannotplay").css({height:pxHeight,width:pxWidth});
    var embed=container.find("div.me-plugin embed");
    if(embed.length){   // this is a Flash video
      embed.prop({width:width,height:height}).css({width:pxWidth,height:pxHeight});
      var flashvars=embed.attr("flashvars")
      var flashvars1=flashvars.replace(/&width=\d+&/,"&width="+width+"&").replace(/&height=\d+&/,"&height="+height+"&");
      if(flashvars1!==flashvars){
        embed.attr("flashvars",flashvars1);
      }
      var done=true;
    }else if(container.find("div.me-cannotplay").length){
      var done=true;
    }
    //container.parents("div.wp-video").css("margin","0");   // remove the bottom margin since it puts too much space between caption
    if(!done){window.setTimeout(f,1000);}
  };
  f();
};

jQuery(document).ready(function(){
    // this is the mouse-over alt_image_field popup handler 
    jQuery("div.mf2tk-hover").hover(
        function(e){
            // center the overlay element over the mouse-overed element and show it
            var jqThis=jQuery(this);
            var overlay=jqThis.find("div.mf2tk-overlay");
            var parent=jqThis.offsetParent();
            var position=jqThis.position();
            var overlayWidth=overlay.outerWidth();
            var parentWidth=parent.outerWidth();
            if(overlayWidth<parentWidth){
                var x=position.left+(jqThis.outerWidth()-overlayWidth)/2;
                if(x<0){
                    x=0;
                }else{
                    var overflow=(x+overlayWidth)-parentWidth;
                    if(overflow>0){x-=overflow;}
                }
            }else{
            }
            overlay[0].style.left=x+"px";
            overlay[0].style.top=(position.top+20)+"px";
            overlay.show();
        },
        function(){
            jQuery(this).find("div.mf2tk-overlay").hide();
        }
    );
    // propagate clicks on overlay to the mouse-overed element
    jQuery("div.mf2tk-hover div.mf2tk-overlay").click(function(e){
        jQuery(this.parentNode).find("a")[0].click();
    });
});

