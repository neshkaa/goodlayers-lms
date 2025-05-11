(function($){
	
	$.fn.gdlrAddSidebar = function(options){

        var settings = $.extend({
			title: 'Create New Sidebar',
			nonce: '',
			ajax: '',
			action: ''
        }, options);	

		var gdlr_form = $(' <form action="#" > \
								<input type="text" name="sidebar-name" /> \
								<input type="submit" value="+" /> \
							</form>');
		
		var gdlr_object = $('<div class="gdlr-sidebar-generator" style="text-align: center" id="gdlr-sidebar-generator"></div>');
		gdlr_object.append('<span class="gdlr-title">' + settings.title + '</span>');
		gdlr_object.append(gdlr_form).insertAfter($(this));

		// $(this).append('<div class="clear" style="height: 15px;" ></div>');
		// $(this).append(gdlr_object);
		
		gdlr_form.submit(function(){
			
			// check if the name is empty
			var sidebar_name = $(this).children('input[name="sidebar-name"]').val();
			if( sidebar_name.length <= 0 ){ 

				$('body').gdlr_alert({
					text: '<span class="head">Sidebar Name Is Blank</span>', 
					status: 'failed',
					duration: 1000
				});			
				return false; 
			}
			
			// if not empty add the new sidebar to the theme
			$.ajax({
				type: 'POST',
				url: settings.ajax,
				data: { 'security': settings.nonce, 'action': settings.action, 'sidebar_name': sidebar_name  },
				dataType: 'json',
				error: function(a, b, c){
					console.log(a, b, c);
					$('body').gdlr_alert({
						text: '<span class="head">Sending Error</span> Please refresh the page and try this again.', 
						status: 'failed'
					});
				},
				success: function(data){
					if( data.status == 'success' ){
						location.reload();
					}else if( data.status == 'failed' ){
						$('body').gdlr_alert({
							text: data.message, 
							status: data.status
						});					
					}
				},
				
			});		
			
			return false;
		});		
		
	};
	
	// add and bind the delete sidebar button
	$.fn.gdlrDeleteSidebar = function(options){
        var settings = $.extend({
			nonce: '',
			ajax: '',
			action: ''
        }, options);	
		
		
		var widget_right = $(this);
		var t = setInterval(function(){ 

			var widget_area = widget_right.find('[data-widget-area-id]');

			if( widget_area.length ){

				// add remove button for dynamic sidebar
				widget_right.find('[data-widget-area-id]').each(function(){
					var widget_item = $(this).closest('.wp-block-widget-area').parent();
					var widget_id = $(this).attr('data-widget-area-id');
					var widget_title = widget_item.find('.components-panel__body-title');
					var sidebar_name = widget_title.text();
					
					var ignores = ['sidebar-1', 'sidebar-2', 'sidebar-3', 'sidebar-4', 'gdlr-core-sidebar-preset', 'wp_inactive_widgets'];
					if( ignores.indexOf(widget_id) >= 0 ){
						return true;
					}

					var delete_button = $('<div class="delete-sidebar-button"></div>');
					delete_button.on('click', function(){
						
						// create confirm button
						$('body').gdlr_confirm({ 
							
							success: function(){
								
								// execute ajax command after user confirm the action
								$.ajax({
									type: 'POST',
									url: settings.ajax,
									data: { 'security': settings.nonce, 'action': settings.action, 'sidebar_name': sidebar_name },
									dataType: 'json',
									error: function(a, b, c){
										console.log(a, b, c);
										$('body').gdlr_alert({
											text: '<span class="head">Deleting Error</span> Please refresh the page and try this again.', 
											status: 'failed'
										});
									},
									success: function(data){
										if( data.status == 'success' ){
											widget_item.slideUp(250, function(){
												$(this).remove();
											});
										}else if( data.status == 'failed' ){
											$('body').gdlr_alert({
												text: data.message, 
												status: data.status
											});					
										}
									},
									
								}); // ajax
								
							} // success
						});
						
						return false;
					});

					widget_title.append(delete_button);
				});

				clearTimeout(t);
			}

		}, 500);
	}
	
	// execute the script when document is ready
	$(document).ready(function(){
	
		// bind the add sidebar function
		$('.block-editor-block-list__layout.is-root-container').gdlrAddSidebar({
			title: gdlr_title,
			nonce: gdlr_nonce,
			ajax: gdlr_ajax,
			action: 'gdlr_add_sidebar'
		});
		
		// bind the delete sidebar function
		$('.block-editor-block-list__layout.is-root-container').gdlrDeleteSidebar({
			nonce: gdlr_nonce,
			ajax: gdlr_ajax,
			action: 'gdlr_remove_sidebar'		
		});
	});

})(jQuery);