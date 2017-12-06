(function () {
    var $modal = $('.image-viewer-container'),
        $modalTop = $modal.find('.image-viewer_top'),
        $modalMiddle = $modal.find('.image-viewer_middle'),
        $modalBottom = $modal.find('.image-viewer_bottom'),
        $arrowLeft = $('.image-viewer_middle-arrow_left'),
        $arrowRight = $('.image-viewer_middle-arrow_right'),
        $modalTopTitle = $modal.find('.image-viewer_top-title'),
        $modalTopDownloadLink = $modal.find('.image-viewer_top-download a'),
        $modalBottomText = $modal.find('.image-viewer_bottom-number'),
        curIndex = 0,
        $postImages,
        imageItems,
        preventDef = function (e) {
            e.preventDefault();
        };

    function basename(path, prefix) {
        path = path.split(prefix);

        return path[path.length-1];
    }

    // Предотвращение прокрутки
    $modal.on('scroll', preventDef);
    $modal.on('mousewheel', preventDef);
    $modal.on('touchmove', preventDef);

    // Показать/скрыть шапку и подвал
    $modalMiddle.click(function (e) {
        $modalTop.toggle();
        $modalBottom.toggle();
    });

    // Подготовка массива данных
    $postImages = $('.postCont a.lightbox');
    if ($postImages.length) {
        // Старый формат
        imageItems = [].slice.call($postImages).map(function ($image, index) {
            return {
                src: $image.href,
                title: $image.title
            };
        });
    } else {
        // Новый формат
        $postImages = $('.postCont img');
        imageItems = [].slice.call($postImages).map(function ($image, index) {
            return {
                index: index,
                src: $image.src,
                title: ''
            };
        });
    }

    function showModal($modal, arr, index) {
        var countText = (index + 1) + ' из ' + arr.length,
            item = arr[index];

        $modalTopTitle.text(basename(item.title, '/'));
        $modalTopDownloadLink.attr('href', item.src);

        $modalMiddle.css('background-image', 'url(' + item.src + ')');

        $modalBottomText.text(countText);

        if (arr.length === 1) {
            $arrowLeft.hide();
            $arrowRight.hide();
        } else if (index === 0) {
            $arrowLeft.hide();
            $arrowRight.show();
        } else if (index === arr.length - 1) {
            $arrowLeft.show();
            $arrowRight.hide();
        } else {
            $arrowLeft.show();
            $arrowRight.show();
        }

        $modal.show();
    }

    function initCurrentImage(items, src) {
        var curIndex = 0,
	    i;

	for (i = 0; i < items.length; i++) {
		if ((items[i].src === src || items[i].src.indexOf(src) !== -1)) {
			curIndex = i;
			break;
		}
	}
	
        showModal($modal, items, curIndex);
    }

    // Old
    $('.postCont .lightbox').click(function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        initCurrentImage(imageItems, $(this).attr('href'));
    });

    // New, img only
    $('.postCont img.fr-fic').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        initCurrentImage(imageItems, $(this).attr('src'));
    });

    // New, span with img
    $('.postCont .fr-img-wrap').click(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        initCurrentImage(imageItems, $(this).find('img').attr('src'));
    });

    $('.image-viewer_top-close').click(function (e) {
        $modal.hide();
    });

    // Показать предыдущее изображение
    $arrowLeft.click(function (e) {
        e.preventDefault();
        e.stopPropagation();

        curIndex--;

        if (curIndex < 0) {
            curIndex = 0;
        }

        showModal($modal, imageItems, curIndex);
    });

    // Показать следующее изображение
    $arrowRight.click(function (e) {
        e.preventDefault();
        e.stopPropagation();

        curIndex++;
        if (curIndex > imageItems.length - 1) {
            curIndex = imageItems.length - 1;
        }

        showModal($modal, imageItems, curIndex);
    });
})();
