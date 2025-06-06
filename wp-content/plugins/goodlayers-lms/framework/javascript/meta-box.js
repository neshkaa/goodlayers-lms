(function($){
	"use strict";

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
	
    //////////////////////////////////////////////
	// FOR LECTURE
	//////////////////////////////////////////////

	// update the current lecture then set the new lecture
	$.fn.gdlr_update_lecture = function(options, old_tab, new_tab){
		var old_tab_num = (old_tab)? parseInt(old_tab.html()) - 1: 0;
		var new_tab_num = (new_tab)? parseInt(new_tab.html()) - 1: 0;
	
		$(this).find('[data-slug], textarea.wp-editor-area').each(function(){
			if( $(this).parent('.gdlr-lecture-wrapper').length > 0 ){ return; }
			if( old_tab ){ options[old_tab_num][$(this).attr('data-slug')] = $(this).gdlr_get_option_value(); }
			if( new_tab ){ $(this).gdlr_set_option_value(options[new_tab_num][$(this).attr('data-slug')]); }
		});
		$(this).children('textarea').val(JSON.stringify(options));
	}	
	
	$.fn.gdlr_update_current_lecture = function(){
		var current_tab = $(this).closest('.gdlr-lecture-wrapper');
		var options = null;
		if( $(this).val() ){
			options = $.parseJSON($(this).val());
		}
		
		options = (options)? options: [new Object()];
		
		current_tab.gdlr_update_lecture(options, current_tab.find('.lecture-tab-title .active'), null);
	}
	
	// initiate lecture item
	$.fn.gdlr_init_lecture = function(){
		var textarea = $(this);
		var current_tab = $(this).closest('.gdlr-lecture-wrapper');
		var options = null;
		if( $(this).val() ){
			options = $.parseJSON($(this).val());
		}
		options = (options)? options: [new Object()];
		
		// textarea changed update value
		textarea.change(function(){
			if( textarea.val() ){
				options = $.parseJSON(textarea.val());
			}else{
				options = null;
			}
			options = (options)? options: [new Object()];
			console.log(options);
			
			var lecture_tabs = current_tab.children('.lecture-tab-title');
			var lecture_tabs_num = (options.length > 0)? options.length: 1;
			
			lecture_tabs.children().remove();
			for( var i=0; i<lecture_tabs_num; i++ ){
				lecture_tabs.append('<span ' + (i==0? 'class="active"': '') + '>' + (i+1) + '</span>');
			}
			current_tab.gdlr_update_lecture( options, null, lecture_tabs.children(':first-child'));
		});
		
		// new tab event
		current_tab.find('.lecture-tab-add-new > .head').on('click', function(){
			options[options.length] = new Object();
			current_tab.gdlr_update_lecture(options, 
				$(this).parent().siblings('.lecture-tab-title').children('.active').removeClass('active'), 
				$('<span class="active">' + (options.length) + '</span>').appendTo($(this).parent().siblings('.lecture-tab-title')));
			tinymce.activeEditor.undoManager.clear();
			tinymce.activeEditor.undoManager.add();
		});
		
		// delete tab event
		current_tab.find('.lecture-tab-content > .lecture-tab-remove').on('click', function(){
			gdlr_lms_confirm({success: function(){
				var next_tab;
				var remove_tab = current_tab.children('.lecture-tab-title').children('.active');
				options.splice(parseInt(remove_tab.html()) - 1, 1);
				
				// set the nearby tab active
				if( remove_tab.prev().length ){
					next_tab = remove_tab.prev().addClass('active');
					remove_tab.remove();
				}else if( remove_tab.next().length ){
					next_tab = remove_tab.next().addClass('active');
					remove_tab.remove();
				}else{
					next_tab = remove_tab;
					options[options.length] = new Object();
				}
				current_tab.gdlr_refresh_tab_title();
				current_tab.gdlr_update_lecture(options, null, next_tab);
			}});
		});

		// tab changing event
		var lecture_tab_title = current_tab.children('.lecture-tab-title');
		lecture_tab_title.on('click', 'span', function(){
			if($(this).hasClass('active')) return;

			current_tab.gdlr_update_lecture(options, 
				$(this).siblings('.active').removeClass('active'), 
				$(this).addClass('active'));

			tinymce.activeEditor.undoManager.clear();
			tinymce.activeEditor.undoManager.add();
		});	

		// order the lecture
		var old_order = 0;
		lecture_tab_title.sortable({
			placeholder: "lecture-tab-title-placeholder",
			start: function(event, ui){
				old_order = $(ui.item).index();

			},
			update: function(event, ui){
				var new_order = $(ui.item).index();

				// order the list
				var temp = options[old_order];
				if( old_order > new_order ){
					for( var i = old_order; i > new_order; i-- ){
						options[i] = options[i - 1];
					}
					
				}else{
					for( var i = old_order; i < new_order; i++ ){
						options[i] = options[i + 1];
					}
				}
				options[new_order] = temp;

				// save the list
				current_tab.gdlr_refresh_tab_title();
				current_tab.gdlr_update_lecture(options, null, null);
			}
		});

	}	
	
    //////////////////////////////////////////////
	// FOR TAB
	//////////////////////////////////////////////	
	
	// update the current tab then set the new tab
	$.fn.gdlr_update_tab = function(options, old_tab, new_tab){
		var old_tab_num = (old_tab)? parseInt(old_tab.html()) - 1: 0;
		var new_tab_num = (new_tab)? parseInt(new_tab.html()) - 1: 0;
	
		$(this).find('[data-slug], textarea.wp-editor-area').each(function(){
			if( $(this).closest('.lecture-tab-content').length > 0 ) return;
			if( old_tab ){ 
				if( $(this).hasClass('gdlr-lms-lecture-content') ){ 
					$(this).gdlr_update_current_lecture();
				}
				options[old_tab_num][$(this).attr('data-slug')] = $(this).gdlr_get_option_value(); 
			}
			if( new_tab ){ 
				$(this).gdlr_set_option_value(options[new_tab_num][$(this).attr('data-slug')]); 
				if( $(this).hasClass('gdlr-lms-lecture-content') ){
					$(this).trigger('change'); 
				}
			}
		});
		$(this).children('textarea').val(JSON.stringify(options));
	}
	
	// refresh the tab title
	$.fn.gdlr_refresh_tab_title = function(){
		var num = 1;
		$(this).children('.course-tab-title, .lecture-tab-title').children('span').each(function(){
			$(this).html(num);
			num++;
		});
	}	
	
	// initiate tab item
	$.fn.gdlr_init_tab = function(){
		var current_tab = $(this);
		var options = null;
		try{
			if( $(this).children('textarea').val() ){
				options = $.parseJSON($(this).children('textarea').val());
			}
			
		}catch(err){ 
			console.log(err); 
		}		
		options = (options)? options: [new Object()];
		
		// set data-slug for wp editor textarea then init the value
		$(this).find('textarea.wp-editor-area').each(function(){
			$(this).attr('data-slug', $(this).attr('id'));
			if( $(this).parents('.wp-editor-wrap').hasClass('html-active') ){
				$(this).val( window.switchEditors.pre_wpautop($(this).val()) );
			}
		});
		
		// initiate the lecture section
		$(this).find('textarea.gdlr-lms-lecture-content').gdlr_init_lecture();
			
		// initiate the quiz question item
		if( $.isFunction($.fn.gdlr_lms_question_box) ){
			$(this).find('.quiz-question-holder').gdlr_lms_question_box();
		}
	
		// new tab event
		$(this).find('.course-tab-add-new > .head').on('click', function(){
			options[options.length] = new Object();
			current_tab.gdlr_update_tab(options, 
				$(this).parent().siblings('.course-tab-title').children('.active').removeClass('active'), 
				$('<span class="active">' + (options.length) + '</span>').appendTo($(this).parent().siblings('.course-tab-title')));
		});
		
		// delete tab event
		$(this).find('.course-tab-content > .course-tab-remove').on('click', function(){
			gdlr_lms_confirm({success: function(){
				var next_tab;
				var remove_tab = current_tab.children('.course-tab-title').children('.active');
				options.splice(parseInt(remove_tab.html()) - 1, 1);
				
				// set the nearby tab active
				if( remove_tab.prev().length ){
					next_tab = remove_tab.prev().addClass('active');
					remove_tab.remove();
				}else if( remove_tab.next().length ){
					next_tab = remove_tab.next().addClass('active');
					remove_tab.remove();
				}else{
					next_tab = remove_tab;
					options[options.length] = new Object();
				}
				current_tab.gdlr_refresh_tab_title();
				current_tab.gdlr_update_tab(options, null, next_tab);
			}});
		});
		
		// tab changing event
		var course_tab_title = $(this).children('.course-tab-title');
		course_tab_title.on('click', 'span', function(){
			if($(this).hasClass('active')) return;

			current_tab.gdlr_update_tab(options, 
				$(this).siblings('.active').removeClass('active'), 
				$(this).addClass('active'));
		});

		// order the lecture
		var old_order = 0;
		course_tab_title.sortable({
			placeholder: "course-tab-title-placeholder",
			start: function(event, ui){
				old_order = $(ui.item).index();
			},
			update: function(event, ui){
				var new_order = $(ui.item).index();

				// order the list
				var temp = options[old_order];
				if( old_order > new_order ){
					for( var i = old_order; i > new_order; i-- ){
						options[i] = options[i - 1];
					}
					
				}else{
					for( var i = old_order; i < new_order; i++ ){
						options[i] = options[i + 1];
					}
				}
				options[new_order] = temp;

				// save the list
				current_tab.gdlr_refresh_tab_title();
				current_tab.gdlr_update_tab(options, null, null);
			}
		});		
		
		// save page event
		$('#post-preview, #publish, #save-post').on('click', function(){
			current_tab.gdlr_update_tab(options, current_tab.find('.course-tab-title .active'), null);
		});
	}
	
	// get and set option value depends on each option type
	$.fn.gdlr_get_option_value = function(){
		if( $(this).is('input[type="checkbox"]') ){
			return ($(this).is(':checked'))? 'enable': 'disable';
		}else if( $(this).is('textarea.wp-editor-area') ){
			if( $(this).parents('.wp-editor-wrap').hasClass('tmce-active') ){
				var editor = tinyMCE.get($(this).attr('id'));
				return editor.getContent();
			}else{
				return window.switchEditors.wpautop($(this).val());
			}
		}else{
			return $(this).val();
		}
	}
	$.fn.gdlr_set_option_value = function(value){
		value = (value)? value: '';
	
		if( $(this).is('input[type="checkbox"]') ){
			if( value && value == 'enable' ){ 
				$(this).prop('checked', true); 
				$(this).siblings('.checkbox-appearance').addClass('enable');
			}else{ 
				$(this).prop('checked', false); 
				$(this).siblings('.checkbox-appearance').removeClass('enable');
			}
		}else if( $(this).is('select') ){
			if( value ){ $(this).val(value); }
			else{ $(this).children(':first-child').attr("selected", "selected"); }
		}else if( $(this).is('textarea.wp-editor-area') ){
			if( $(this).parents('.wp-editor-wrap').hasClass('tmce-active') ){
				var editor = tinyMCE.get($(this).attr('id'));
				editor.setContent(value);
			}else{
				$(this).val( window.switchEditors.pre_wpautop(value) );
			}
		}else{
			$(this).val(value);
		}
		
		if( $(this).hasClass('gdlr-trigger') ){
			$(this).trigger('change');
		}
	}	
	
	// update normal meta box to textarea
	function gdlr_update_meta_box(){
		$('.gdlr-lms-meta-wrapper').each(function(){
			if( ! $(this).hasClass('gdlr-tabs') ){
				// save option
				var options = new Object();
				
				$(this).find('[data-slug]').each(function(){
					options[$(this).attr('data-slug')] = $(this).gdlr_get_option_value();
				});
				$(this).children('textarea').val(JSON.stringify(options));
			}
		});
	}
	
	$(document).ready(function(){
		
		// fill default certificate shortcode
		$('#fill-default').on('click', function(){
			var wp_editor = tinyMCE.activeEditor;
			var shortcode = '[gdlr_cer_wrapper border="yes" background="IMAGE_URL" class="CLASS" student_code="true" ]\
				<br><img src="IMAGE_URL" />\
				<br>[gdlr_cer_caption font_size="19px" class="CSS_CLASS"]This is to certify that[/gdlr_cer_caption]\
				<br>[gdlr_cer_student_name font_size="34px" class="CSS_CLASS"]\
				<br>[gdlr_cer_caption font_size="19px" class="CSS_CLASS"]has successfully completed the course[/gdlr_cer_caption]\
				<br>[gdlr_cer_course_name font_size="25px" class="CSS_CLASS"]\
				<br>[gdlr_cer_mark font_size="19px" margin_top="20px" class="CSS_CLASS"]With Marks[/gdlr_cer_mark]\
				<br>[gdlr_cer_date format="j/n/Y" font_size="15px" margin_top="0px" class="CSS_CLASS"]Date[/gdlr_cer_date]<img src="IMAGE_URL" />[gdlr_cer_signature image="IMAGE_URL" font_size="15px" class="CSS_CLASS"]Sam White, Course Instructor[/gdlr_cer_signature]\
				<br>[/gdlr_cer_wrapper]';
				
			if( wp_editor ){
				wp_editor.setContent(wp_editor.getContent() + shortcode);
			}else{
				$('#content').val($('#content').val() + shortcode);
			}
		});
		
		// upload image button
		$('.gdlr-lms-upload-button').on('click', function(){
			var upload_button = $(this);
			var custom_uploader = wp.media({
				title: 'Upload',
				button: { text: 'Upload' },
				multiple: false
			}).on('select', function() {
				var attachment = custom_uploader.state().get('selection').first().toJSON();
				upload_button.siblings('input[type="text"]').val(attachment.url);
			}).open();
		});
		
		// date picker
		$('.gdlr-lms-meta-option input.gdlr-date-picker').datepicker({ dateFormat : 'yy-mm-dd' });
		
		// checkbox
		$('.gdlr-lms-meta-option input[type="checkbox"]').each(function(){
			var show = '.' + $(this).attr('data-slug'); var hide = show;
			
			if( $(this).siblings('.checkbox-appearance').hasClass('enable') ){
				show += '-enable'; hide += '-disable';
			}else{
				show += '-disable'; hide += '-enable';
			}
			
			$(this).parents('.gdlr-lms-meta-option').siblings(hide).hide();
			$(this).parents('.gdlr-lms-meta-option').siblings(show).show();			
		});
		$('.gdlr-lms-meta-option input[type="checkbox"]').on('click', function(){
			var show = '.' + $(this).attr('data-slug'); var hide = show;
		
			if( $(this).siblings('.checkbox-appearance').hasClass('enable') ){
				show += '-disable'; hide += '-enable';
				$(this).siblings('.checkbox-appearance').removeClass('enable');
			}else{
				show += '-enable'; hide += '-disable';
				$(this).siblings('.checkbox-appearance').addClass('enable');
			}
			
			$(this).parents('.gdlr-lms-meta-option').siblings(hide).slideUp();
			$(this).parents('.gdlr-lms-meta-option').siblings(show).slideDown();
		});
		
		// course tab content 
		$('.gdlr-lms-meta-wrapper.gdlr-tabs').gdlr_init_tab();
		
		// save changes
		$('#post-preview, #publish, #save-post').on('click', function(){
			gdlr_update_meta_box();
		});
		
		// bkup content
		$('#gdlr-lms-bkup-wrapper').each(function(){
			var post_id = $(this).attr('data-id');
			var ajax_url = $(this).attr('data-ajax');
			var action = $(this).attr('data-action');	
			
			$(this).children('input[type="button"]').on('click', function(){
				gdlr_lms_confirm({ success: function(){
					$.ajax({
						type: 'POST',
						url: ajax_url,
						data: {'action': action, 'post_id':post_id},
						dataType: 'json',
						error: function(a, b, c){
							console.log(a, b, c);
						},
						success: function(data){
							location.reload();
						}
					});	
				}});	
			});
		});
		
		
	});
	
})(jQuery);