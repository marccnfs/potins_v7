// function recuperer sur le web a revoir pour analyse format image todo
function initLandscapeClass(oImgSource, oCibleClass, sPrefix){
    var oImgTmp= new Image(), sUrlImg = '', sLandscape = 'landscape', sPortrait = 'portrait'
        , oCibleClass = (oCibleClass == null || oCibleClass == undefined)? oImgSource : oCibleClass
        , sPrefix = (sPrefix == undefined)? '' : sPrefix, sDisplayCss = 'display:block;width:auto;height:auto';
    oImgTmp.style='display:none';
    if(typeof(oImgTmp.naturalWidth) != 'undefined' && false){
        if(oImgSource.tagName.toUpperCase() === 'IMG'){
            if(oImgSource.complete === true){
                addClass(oCibleClass, sPrefix + ((oImgSource.naturalWidth <= oImgSource.naturalHeight)? sPortrait : sLandscape));
            }else{
                oImgSource.addEventListener('load', function(oEvent){
                    var oImg = oEvent.currentTarget;
                    addClass(oCibleClass, sPrefix + ((oImg.naturalWidth <= oImg.naturalHeight)? sPortrait : sLandscape));
                });
            }//else
            return true;
        }//if
    }//if
    if(oImgSource.tagName.toUpperCase() == 'IMG'){
        sUrlImg = oImgSource.src;
    }else{
        sUrlImg = getvalueCSS(oImgSource, 'background-image');
        sUrlImg = sUrlImg.replace(/(|)|"|'/gi,'');
        sUrlImg = sUrlImg.replace('url','');
    }//else
    document.body.appendChild(oImgTmp);
    oImgTmp.src = sUrlImg;
    if(oImgTmp.complete == true){
        oImgTmp.style = sDisplayCss;
        addClass(oCibleClass,sPrefix + ((oImgTmp.width <= oImgTmp.height)? sPortrait : sLandscape));
        oImgTmp.remove();
    }else{
        oImgTmp.addEventListener("load", function(oEvent){
            var oImg = oEvent.currentTarget;
            oImg.style = sDisplayCss;
            addClass(oCibleClass,sPrefix + ((oImg.width <= oImg.height)? sPortrait : sLandscape));
            oImgTmp.remove();
        });
    }//else
}