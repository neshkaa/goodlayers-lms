(function($){

	// get kot using ajax
	function gdlr_kot_ajax(port_holder, ajax_info, category, paged){

		var args = new Object();
		args['num-fetch'] = ajax_info.attr('data-num-fetch');
		args['order'] = ajax_info.attr('data-order');
		args['orderby'] = ajax_info.attr('data-orderby');
		args['thumbnail-size'] = ajax_info.attr('data-thumbnail-size');
		args['kot-style'] = ajax_info.attr('data-port-style');
		args['kot-size'] = ajax_info.attr('data-port-size');
		args['kot-layout'] = ajax_info.attr('data-port-layout');
		args['category'] = (category)? category: ajax_info.attr('data-category');
		args['paged'] = (paged)? paged: 1;

		// hide the un-used elements
		var animate_complete = false;
		port_holder.slideUp(500, function(){
			animate_complete = true;
		});
		port_holder.siblings('.gdlr-pagination').slideUp(500, function(){
			$(this).remove();
		});

		var now_loading = $('<div class="gdlr-now-loading"></div>');
		now_loading.insertBefore(port_holder);
		now_loading.slideDown();

		// call ajax to get kot item
		$.ajax({
			type: 'POST',
			url: ajax_info.attr('data-ajax'),
			data: {'action': 'gdlr_get_kot_ajax', 'args': args},
			error: function(a, b, c){ console.log(a, b, c); },
			success: function(data){
				console.log(data);

				now_loading.css('background-image','none').slideUp(function(){ $(this).remove(); });

				var port_item = $(data).hide();
				if( animate_complete ){
					gdlr_bind_kot_item(port_holder, port_item);
				}else{
					setTimeout(function() {
						gdlr_bind_kot_item(port_holder, port_item);
					}, 500);
				}
			}
		});

	}

	function gdlr_bind_kot_item(port_holder, port_item){
		if( port_holder ){
			port_holder.replaceWith(port_item);
		}
		port_item.slideDown();

		// bind events
		port_item.each(function(){
			if( $(this).hasClass('gdlr-pagination') ){
				$(this).children().gdlr_bind_kot_pagination();
			}
		});
		port_item.gdlr_fluid_video();
		port_item.find('.gdlr-kot-item').gdlr_kot_hover();
		port_item.find('.flexslider').gdlr_flexslider();
		port_item.find('.gdlr-isotope').gdlr_isotope();
		port_item.find('[data-rel="fancybox"]').gdlr_fancybox();

		if( port_item.closest('.gdlr-kot-link-lightbox').length > 0 ){
			port_item.find('a[data-lightbox]').click(function(){
				$(this).gdlr_kot_lightbox(); return false;
			});
		}
		port_item.find('img').load(function(){ $(window).trigger('resize'); });
	}

	$.fn.gdlr_bind_kot_pagination = function(){
		$(this).click(function(){
			if($(this).hasClass('current')) return;
			var port_holder = $(this).parent('.gdlr-pagination').siblings('.kot-item-holder');
			var ajax_info = $(this).parent('.gdlr-pagination').siblings('.gdlr-ajax-info');

			var category = $(this).parent('.gdlr-pagination').siblings('.kot-item-filter');
			if( category ){
				category = category.children('.active').attr('data-category');
			}

			gdlr_kot_ajax(port_holder, ajax_info, category, $(this).attr('data-paged'));
			return false;
		});
	}

	$.fn.gdlr_kot_hover = function(){
		$(this).each(function(){
			if( $(this).hasClass('gdlr-modern-kot') ){
				var port_item = $(this);

				$(this).find('.kot-thumbnail').hover(function(){
					$(this).find('.kot-overlay').animate({opacity: 0.6}, 200);
					$(this).find('.kot-overlay-content').animate({opacity: 1}, 200);
				}, function(){
					$(this).find('.kot-overlay').animate({opacity: 0}, 200);
					$(this).find('.kot-overlay-content').animate({opacity: 0}, 200);
				});

				//function set_kot_height(){
				//
				//	port_item.find('.kot-overlay-content').each(function(){
				//		$(this).css('margin-top', -($(this).height()/2));
				//	});
				//}
				//set_kot_height();
				//$(window).resize(function(){ set_kot_height(); });
			}else{
				$(this).find('.kot-thumbnail').hover(function(){
					$(this).find('.kot-overlay').animate({opacity: 0.8}, 200);
					$(this).find('.kot-icon').animate({opacity: 1}, 200);
				}, function(){
					$(this).find('.kot-overlay').animate({opacity: 0}, 200);
					$(this).find('.kot-icon').animate({opacity: 0}, 200);
				});
			}
		});
	}

	/*--- Single Kot ---*/
	$.fn.gdlr_kot_lightbox = function(){
		var lightbox = $('<div class="gdlr-single-lightbox"></div>').hide();
		$('body').css({'overflow': 'hidden', 'margin-right': '18px'}).append(lightbox.fadeIn(200))
		lightbox.append('<div class="gdlr-single-lightbox-overlay gdlr-exit"></div>');

		gdlr_get_single_port_content(lightbox, null, $(this).attr('data-lightbox'), $(this).closest('[data-ajax]').attr('data-ajax'));

		// bind exit event
		lightbox.find('.gdlr-exit').click(function(){
			lightbox.fadeOut(200, function(){ $(this).remove(); $('body').css({'overflow': 'auto', 'margin-right': '0px'});  });
		});
	}

	function gdlr_get_single_port_content(lightbox, old_container, post_id, ajax_url){
		if( old_container ){
			old_container.fadeOut(200);
		}

		$.ajax({
			type: "POST",
			url: ajax_url,
			data: {'action':'gdlr_get_single_port', 'port_id':post_id },
			success: function(data){
				var container_wrapper = $('<div class="gdlr-single-lightbox-wrapper container"></div>').hide();
				lightbox.append(container_wrapper);
				container_wrapper.append('<div class="gdlr-single-lightbox-close"><div class="gdlr-exit"></div></div>');
				container_wrapper.append('<div class="clear"></div>');

				var container = $('<div class="gdlr-single-lightbox-container"></div>');
				container.html(data);
				container_wrapper.append(container).fadeIn(200);

				// bind events
				container_wrapper.find('a[data-lightbox]').click(function(){
					gdlr_get_single_port_content(lightbox, container_wrapper, $(this).attr('data-lightbox'), ajax_url);
					return false;
				});
				container_wrapper.gdlr_fluid_video();
				container_wrapper.find('.flexslider').gdlr_flexslider();
				container_wrapper.find('[data-rel="fancybox"]').gdlr_fancybox();

				// bind exit event
				lightbox.find('.gdlr-exit').click(function(){
					lightbox.fadeOut(200, function(){ $(this).remove(); $('body').css({'overflow': 'auto', 'margin-right': '0px'});  });
				});
			},
		  dataType: 'text'
		});
	}

	$(document).ready(function(){

		// single lightbox
		$('.gdlr-kot-link-lightbox a[data-lightbox]').click(function(){
			$(this).gdlr_kot_lightbox(); return false;
		});

		// script for kot item
		$('.gdlr-kot-item').gdlr_kot_hover();

		// script for calling ajax kot when selecting category
		$('.kot-item-filter a').click(function(){
			if($(this).hasClass('active')) return false;
			$(this).addClass('active').siblings().removeClass('active');

			var port_holder = $(this).parent('.kot-item-filter').siblings('.kot-item-holder');
			var ajax_info = $(this).parent('.kot-item-filter').siblings('.gdlr-ajax-info');

			gdlr_kot_ajax(port_holder, ajax_info, $(this).attr('data-category'));
			return false;
		});

		// script for calling ajax kot when using pagination
		$('.gdlr-pagination.gdlr-ajax .page-numbers').gdlr_bind_kot_pagination();
	});

})(jQuery);
