(function ($) {
	"use strict";

	var ListingForm = STMListings.ListingForm = {};
	var btnAddCar;
	ListingForm.submit = function (e) {
		e.preventDefault();

		var $loader = $('.stm-add-a-car-loader'),
			$message = $('.stm-add-a-car-message');

		$.ajax({
			url: ajaxurl,
			type: "POST",
			dataType: 'json',
			context: this,
			data: $(this).serialize() + '&action=stm_ajax_add_a_car',
			beforeSend: function () {
				$loader.addClass('activated');
				$message.slideUp();
			},
			success: function (data) {
				$loader.removeClass('activated');
				btnAddCar.removeClass().addClass('enabled');
				if (data.message) {
					$message.text(data.message).slideDown();
				}

				if (data.post_id) {
					$message.text(data.message).slideDown();
					$loader.addClass('activated');

					if (typeof(ListingForm.userFiles) !== 'undefined') {
						if (!ListingForm.orderChanged) {
							ListingForm.sortImages();
						}

						ListingForm.uploadImages.call(this, data);
					}
				}
			}
		});

	};

	ListingForm.featuredId = 0;
	ListingForm.userFiles = [];
	ListingForm.orderChanged = false;

	ListingForm.uploadImages = function (data) {

		var $loader = $('.stm-add-a-car-loader'),
			$message = $('.stm-add-a-car-message');

		var fd = new FormData();

		if ($(this).closest('.stm_edit_car_form').length) {
			fd.append('stm_edit', 'update');
		}

		fd.append('action', 'stm_ajax_add_a_car_media');
		fd.append('post_id', data.post_id);

		$.each(ListingForm.userFiles, function (i, file) {
			if (typeof(file) !== undefined) {
				if (typeof(file) !== 'number') {
					fd.append('files[' + i + ']', file);
				} else {
					fd.append('media_position_' + i, file);
				}
			}
		});

		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: fd,
			contentType: false,
			processData: false,
			success: function (response) {
				if (typeof(response) != 'object') {
					var responseObj = JSON.parse(response);
				} else {
					var responseObj = response;
				}
				if (responseObj.allowed_posts) {
					$('.stm-posts-available-number span').text(responseObj.allowed_posts);
				}
				$loader.removeClass('activated');
				if (responseObj.message) {
					$message.text(responseObj.message).slideDown();
				}
				if (responseObj.url) {
					window.location = responseObj.url;
				}
			}
		});
	};

	ListingForm.sortImages = function () {
        $(".stm-media-car-gallery .stm-placeholder").each(function () {
            $(this).blur();
            $(this).find(".inner").removeClass("active");
            $(this).find(".stm-image-preview").blur();
        });

		setTimeout(function () {
			var tmpArr = [];

			$('.stm-placeholder.stm-placeholder-generated').each(function (i, e) {
				/*Get old id*/
				var oldId = $(this).find('.stm-image-preview').attr('data-id');

				/*Set new ids to preview and to delete icon*/
				$(this).find('.stm-image-preview').attr('data-id', i);
				$(this).find('.stm-image-preview .fa').attr('data-id', i);

				if (typeof(ListingForm.userFiles[oldId]) !== 'undefined') {
					tmpArr[i] = ListingForm.userFiles[oldId];
				}
			});

			ListingForm.featuredId = 0;
			ListingForm.userFiles = tmpArr;

		}, 100);
	};


    ListingForm.onImagePicked = function () {
        var wasEmpty = ListingForm.userFiles.length === 0;

        [].forEach.call($(this)[0].files, function (file) {
            if (typeof(file) === 'object' && file.type.match(/^image/)) {
                ListingForm.userFiles.push(file);
                var index = ListingForm.userFiles.length - 1;

                if (index === 0 && wasEmpty) {
                    ListingForm.featuredId = index;
                    $('.stm-media-car-main-input')
                        .find('.stm-image-preview').remove().end()
                        .append('<div class="stm-image-preview" data-id="' + index + '"></div>');
                }

                $('.stm-placeholder-native').remove();
                $('.stm-media-car-gallery')
                    .append(
                        '<div class="stm-placeholder stm-placeholder-generated"><div class="inner">' +
                        '<div class="stm-image-preview" data-id="' + index + '"><i class="fa fa-close" data-id="' + index + '"></i></div>' +
                        '</div></div>'
                    );

                loadImage(
                    file,
                    function (img) {
                        $('.stm-image-preview[data-id="' + index + '"]').css('background-image', 'url(' + img.toDataURL() + ')');

                        /*if (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream) {
                            $('.stm-media-car-gallery .stm-placeholder .inner .stm-image-preview').draggable({
                                revert: 'invalid',
                                helper: "clone",
                                stop: ListingForm.onDropped,
                                delay: 200
                            })
                        } else {*/
                            $('.stm-media-car-gallery .stm-placeholder').stop().droppable({
                                drop: ListingForm.onDropped,
                                delay: 200
                            });
                        //}
                    },
                    {
                        orientation: true,
                        canvas: true
                    }
                );
            }
        });

        if (ListingForm.userFiles.length > 0) {
            $('.stm-media-car-main-input .stm-placeholder').addClass('hasPreviews');
        } else {
            $('.stm-media-car-main-input .stm-placeholder').removeClass('hasPreviews');
        }

        $('.stm_add_car_form input[type="file"]').val('');
    };


	ListingForm.onDropped = function (event, ui) {

		var dragFrom = ui.draggable.closest('.inner');
		var dragTo = $(this).find('.inner');
		var dragToPreview = dragTo.find('.stm-image-preview');

		if (ui.draggable.length > 0 && dragToPreview.length > 0 && dragTo.length > 0 && dragFrom.length > 0) {

			if (dragFrom[0] !== dragTo[0]) {

				ui.draggable.clone().appendTo(dragTo);
				dragToPreview.clone().appendTo(dragFrom);


				/*If placed in first pos*/
				if (dragTo.closest('.stm-placeholder').index() === 0) {
					$('.stm-media-car-main-input .stm-image-preview').remove();

					ui.draggable.clone().appendTo('.stm-media-car-main-input');

					ListingForm.featuredId = ui.draggable.data('id');
				}

				/*If moving from first place*/
				if (ui.draggable.closest('.stm-placeholder').index() === 0) {
					$('.stm-media-car-main-input .stm-image-preview').remove();

					dragToPreview.clone().appendTo('.stm-media-car-main-input');

					ListingForm.featuredId = dragToPreview.data('id');
				}

				ui.draggable.remove();
				dragToPreview.remove();

				ListingForm.sortImages();
				ListingForm.orderChanged = true;
			}
		}
	};


	ListingForm.imageRemove = function () {
		var stm_id = $(this).attr('data-id');
		var stm_length = 0;
		delete ListingForm.userFiles[stm_id];
		$('.stm-placeholder .inner').removeClass('deleting');

		$(this).closest('.stm-placeholder').remove();

		$(ListingForm.userFiles).each(function (i, e) {
			if (typeof(e) !== 'undefined') {
				stm_length++;
			}
		});

		if (stm_length === 0) {
			$('.stm-media-car-main-input .stm-image-preview').remove();
			$('.stm-media-car-main-input .stm-placeholder').removeClass('hasPreviews');
			var defaultPlaceholders = '';
			for (var i = 0; i < 5; i++) {
				defaultPlaceholders += '<div class="stm-placeholder stm-placeholder-native"><div class="inner"><i class="stm-service-icon-photos"></i></div></div>';
			}

			$('.stm-media-car-gallery').append(defaultPlaceholders);
		}

		if (ListingForm.featuredId === parseInt(stm_id)) {
			var changeFeatured = $('.stm-media-car-gallery .stm-placeholder:nth-child(1)');
			ListingForm.featuredId = changeFeatured.find('.stm-image-preview').attr('data-id');

			$('.stm-media-car-main-input .stm-image-preview').remove();
			$(changeFeatured).find('.stm-image-preview').clone().appendTo('.stm-media-car-main-input');
		}

		ListingForm.sortImages();
	};


	$(document).ready(function () {

		//window.hasOwnProperty = window.hasOwnProperty || Object.prototype.hasOwnProperty;

		/*Sell a car*/
		if (typeof stmUserFilesLoaded !== 'undefined') {
			ListingForm.userFiles = stmUserFilesLoaded;
		}

		$('.stm_add_car_form input[type="file"]').on('change', ListingForm.onImagePicked);

		$(document).on('mouseenter touchstart', '.stm-media-car-gallery .stm-placeholder .inner .stm-image-preview .fa', function () {
			$(this).closest('.inner').addClass('deleting');
		});

		$(document).on('mouseleave touchend', '.stm-media-car-gallery .stm-placeholder .inner .stm-image-preview .fa', function () {
			$(this).closest('.inner').removeClass('deleting');
		});

		$(document).on('click', '.stm-media-car-gallery .stm-placeholder .inner .stm-image-preview .fa', ListingForm.imageRemove);

		/*Droppable*/
		$(document).on("mouseenter touchstart", '.stm-media-car-gallery .stm-placeholder .inner .stm-image-preview', function (e) {
			$(this).draggable({
				revert: 'invalid',
				helper: "clone"
			})
		});

		$('.stm-media-car-gallery .stm-placeholder').droppable({
			drop: ListingForm.onDropped
		});

		$(document).on("mouseenter touchstart click", ".stm-media-car-gallery .stm-placeholder .inner", function () {
			$(".stm-media-car-gallery .stm-placeholder").each(function () {
				$(this).blur();
				$(this).find(".inner").removeClass("active");
				$(this).find(".stm-image-preview").blur();
            });

			$(this).addClass("active");
        });


		var $form = $('#stm_sell_a_car_form');

		$form.submit(ListingForm.submit);

		$('.stm-form-checking-user button[type="submit"]').click(function (e) {
			e.preventDefault();
			if (!$(this).hasClass('disabled')) {
				btnAddCar = $(this);
				btnAddCar.removeClass().addClass('disabled');
				$form.submit();
			}
		});

	});

})(jQuery);