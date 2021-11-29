(function() {
    "use strict";

    var $html = $('html');


    $(function(){
        $(window).scroll(makeLazy);
        $(window).resize(makeLazy);
        makeLazy();
    });

    function makeLazy(){
        var $images = $html.find('img[data-src]');
        var params = $("#newmark_lazyload-params").data('params');

        if(!$images)
            return;

        $images.each(function(i, $image){
            $image = $($image);
            if(come($image)){
                var dataSrc = $image.data('src');
				if(!!dataSrc){
	                $image.attr('src', dataSrc);
	                $image.removeAttr('data-src');
	                if($image.attr('srcset') && $image.data('srcset')){
	                    var dataSrcset = $image.data('srcset');
	                    $image.attr('srcset', dataSrcset);
	                    $image.removeAttr('data-srcset');
	                }
					if(params['animation'] == 'Y')
	                    $image.addClass('newmark-lazyload-loaded');

					$image.on("load",function(){
						$image.removeClass('newmark-lazyload-loading');
					}).each(function(){
							if(this.complete)
								$image.trigger("load")
						}
					);
				}
            }
        });
    }


    function come(elem) {
        var docViewTop = $(window).scrollTop(),
            docViewBottom = docViewTop + $(window).outerHeight(),
            elemTop = $(elem).offset().top,
            elemBottom = elemTop;

        return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop) || docViewTop >= elemTop);
    }


})();
