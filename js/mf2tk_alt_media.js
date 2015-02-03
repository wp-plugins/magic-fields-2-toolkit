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
      window.setTimeout(f,1000);
      return;
    }
    var v=jqVideo[0];
    if(v.videoWidth&&v.videoHeight){
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
    container.css({height:pxHeight,width:pxWidth});
    container.parents("div.wp-video").css({height:pxHeight,width:pxWidth});
    container.find("div.mejs-layer").css({height:pxHeight,width:pxWidth});
    container.find("div.me-cannotplay").css({height:pxHeight,width:pxWidth});
    var embed=container.find("div.me-plugin embed");
    if(embed.length){
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
    if(!done){window.setTimeout(f,1000);}
  };
  f();
};
