(function($){
	"use strict";

	// create lightbox over content
	function gdlr_lms_lightbox(content){
		var lightbox = $('<div class="gdlr-lms-lightbox-wrapper"></div>').appendTo('body');
		var overlay = $('<div class="gdlr-lms-lightbox-overlay" ></div>');

		// close lightbox
		overlay.on('click', function(){
			lightbox.fadeOut(200, function(){ 
				$(this).remove();
			});
		});
		
		lightbox.append(overlay).append(content);
		lightbox.fadeIn(200);
		content.css('margin-top', -content.height()/2);
	}

	// create the alert message
	function gdlr_lms_confirm(options){
        var settings = $.extend({
			text: 'Are you sure you want to do this ?',
			success:  function(){}
        }, options);

		var confirm_button = $('<a class="gdlr-lms-button blue">Yes</a>');
		var decline_button = $('<a class="gdlr-lms-button red">No</a>');
		var confirm_box = $('<div class="gdlr-lms-confirm-wrapper"></div>');
		
		confirm_box.append('<span class="head">' + settings.text + '</span>');			
		confirm_box.append(confirm_button);
		confirm_box.append(decline_button);

		$('body').append(confirm_box);
		
		// center the alert box position
		confirm_box.css({ 'margin-left': -(confirm_box.outerWidth() / 2), 'margin-top': -(confirm_box.outerHeight() / 2)});
				
		// animate the alert box
		confirm_box.animate({opacity:1},{duration: 200});
		
		confirm_button.on('click', function(){
			if(typeof(settings.success) == 'function'){ settings.success(); }
			confirm_box.fadeOut(200, function(){ $(this).remove(); });
		});
		decline_button.on('click', function(){
			confirm_box.fadeOut(200, function(){ $(this).remove(); });
		});
	}

	$(document).ready(function(){	
	
		// init the lightbox
		$('[data-rel="gdlr-lms-lightbox"]').on('click', function(){
			var content = $(this).siblings('.' + $(this).attr('data-lb-open')).clone(true);
			if(content.length > 0){ gdlr_lms_lightbox(content); }
		});
		
		// confirmation button
		$('.gdlr-lms-evidence-confirmation .gdlr-lms-button').on('click', function(){
			var current_button = $(this);
			gdlr_lms_confirm({
				success: function(){
					$.ajax({
						type: 'POST',
						url: current_button.attr('data-ajax'),
						data: {
							'action':current_button.attr('data-action'), 
							'invoice': current_button.attr('data-invoice'), 
							'value':current_button.attr('data-value'),
							'code':current_button.attr('data-code'),
							'email':current_button.attr('data-email')
						},
						dataType: 'json',
						error: function(a, b, c){ console.log(a, b, c); },
						success: function(data){
							if( data.status == 'success' ){
								location.reload();
							}else{
								alert(data.message);
							}
						}
					});						
				}
			});
		});

		// date picker
		$('input.gdlr-lms-date-picker').datepicker({
			dateFormat : 'yy-mm-dd'
		});
		
		// transaction page
		$('.mark-as-pending').on('click', function(){
			var submit_value = '<input type="hidden" name="tid" value="' + $(this).attr('data-id') +'" />';
			submit_value += '<input type="hidden" name="action" value="mark-as-pending" />';
			
			$(this).closest('form').append(submit_value).submit();
		});
		$('.mark-as-paid').on('click', function(){
			var submit_value = '<input type="hidden" name="tid" value="' + $(this).attr('data-id') +'" />';
			submit_value += '<input type="hidden" name="action" value="mark-as-paid" />';
			
			$(this).closest('form').append(submit_value).submit();
		});
		$('.delete-transaction').on('click', function(){
			var d_btn = $(this);
			gdlr_lms_confirm({ 
				text: "This action could not be undone, are you sure you want to do this ?",
				success: function(){
					var submit_value = '<input type="hidden" name="tid" value="' + d_btn.attr('data-id') +'" />';
					submit_value += '<input type="hidden" name="action" value="delete" />';
					
					d_btn.closest('form').append(submit_value).submit();
				}
			});
		});
		
	});
	
})(jQuery);