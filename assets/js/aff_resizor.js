jQuery(function($){
    let     rFilter = /^(?:image\/jpg|image\/JPG|image\/jpeg|image\/png|image\/svg\+xml)$/i,
            gifFilter = /^(image\/gif)$/i;
    var     thumb="",
            oFReader = "",
            Height="",
            Width="",
            TypF="",
            imagesource='',
            preview = document.querySelector('.preview'),
            usepreview=false;   // par defaut pour ne pas utiliser le preview auto

           $.extend({
            testfile:function(oFile){
                if (!rFilter.test(oFile.type)) {
                    alert("le format d'image n'est pas valide!");
                    return false;
                }
                return true;
            },
           testgif:function(oFile){
               if (gifFilter.test(oFile.type)) {
                   alert("l'image est un gif");
                   return true;
               }
               return false;
           },
            resizor:function(options) {
                return new Promise((resolve, reject) => {
                    oFReader = new FileReader();
                    thumb = new Image();
                    imagesource = new Image();
                    Height = options.h;
                    Width = options.l
                    usepreview = options.p
                    oFReader.onload = function () {
                        imagesource.addEventListener("load", function () {
                            $.reduceImage(imagesource)
                            resolve(thumb)
                        });
                        imagesource.src = oFReader.result;
                    };
                    oFReader.readAsDataURL(options.f);
                })
            },
            reduceImage:function(){
                let sizes = $.resizeWithSameRatio({
                    height: imagesource.naturalHeight,
                    maxHeight:Height,
                    width: imagesource.naturalWidth,
                    maxWidth: Width
                });
                $.thumbnailWithCanvas({
                    width: sizes.width,
                    height: sizes.height
                });
            },
            resizeWithSameRatio:function(options){
                let width = options.width || 0,
                    height = options.height || 0,
                    maxWidth = options.maxWidth || 400,
                    maxHeight = options.maxHeight || 300;
                if (width > height) {
                    if (width > maxWidth) {
                        height *= maxWidth / width;
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width *= maxHeight / height;
                        height = maxHeight;
                        console.log (height,width)
                    }
                }
                return {
                    width: width,
                    height: height
                }
            },
            thumbnailWithCanvas:function (options) {
                let width = options.width || 0,
                    height = options.height || 0,
                    canvas = document.createElement("canvas"),
                    context;
                canvas.width = width;
                canvas.height = height;
                context = canvas.getContext("2d");
                context.drawImage(imagesource, 0, 0, width, height);
                if(usepreview){
                    thumb.addEventListener("load", () => {
                        $.controlpreview();
                    });
                }
                thumb.src = datatoAjax = canvas.toDataURL(); // rasteriser à une seule image
            },
            controlpreview:function () {
                if(preview.children.length>0){
                    preview.children[0].replaceWith(thumb)
                }else{
                    preview.appendChild(thumb);
                }
            },
            datathumb:function(){
                return datatoAjax;
            }
        })
});