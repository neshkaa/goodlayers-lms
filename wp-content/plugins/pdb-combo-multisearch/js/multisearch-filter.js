/**
 * Participants Database Plugin Combo Multisearch Add-On
 * 
 * @version: 2.12
 * 
 * xnau webdesign xnau.com
 * 
 * handles AJAX list filtering, paging and sorting for the PDB combo multisearch plugin
 */
PDbListFilter = (function ($) {
  "use strict";
  var isError = false;
  var errormsg = $('.multisearch .pdb-searchform .pdb-error');
  var filterform = $('.multisearch .sort_filter_form[data-ref="update"]');
  var remoteform = $('.multisearch .sort_filter_form[data-ref="remote"]');
  var submission = {};
  var searchvalues = {};
  var submission_reset = function () {
    submission = {
      filterNonce : PDb_ajax.filterNonce,
      postID : PDb_ajax.postID
    }
  }
  var submit_search = function (event, remote) {
    remote = remote || false;
    if (event.preventDefault) {
      event.preventDefault();
    } else {
      event.returnValue = false;
    }
    clear_error_messages();
    // validate and process form here
    var $pageButton = get_page_button(event.target);
    var $submitButton = $(event.target);
    var $formcontainer = $submitButton.closest('.' + PDb_ajax.prefix + 'searchform');
    var $thisform = $formcontainer.find('.sort_filter_form');
    var show_error = function (selector) {
      errormsg.show();
      $formcontainer.find(selector).slideDown(200);
    };
    var buttons = $formcontainer.find('input[type=submit]').prop('disabled', true);
    var reenable = function () {
      buttons.prop('disabled', false);
    }
    $('html').on('pdbListFilterComplete', function () {
      reenable();
    });

    submission.submit = $submitButton.data('submit');

    switch (submission.submit) {

      case 'search':
        submission.listpage = '1';
        build_search_values($submitButton);
        if (HTMLFormElement.prototype.reportValidity && !$thisform[0].reportValidity()) {
          isError = true;
        }
        if (combo_search_has_value() === false && multi_search_has_value() === false) {
          show_error('.value_error');
          isError = true;
        }
        if (isError) {
          reenable();
          return;
        }
        if (PDbCMS.restore_search === "1") {
          $thisform.saveForm();
        }
        break;

      case 'clear':
        clear_search($submitButton);
        searchvalues = {};
        add_hidden_values($formcontainer);
        submission.listpage = '1';
        submission.action = 'pdb_list_filter';
        $thisform.clearSavedForm();
        $thisform.removeClass('formsaved');
        break;

      case 'page':
        submission.target_instance = submission.instance_index
        submission.action = 'pdb_list_filter';
        submission.listpage = $pageButton.data('page');
        add_hidden_values($pageButton.closest('.pdb-list').find('.sort_filter_form'));
        break;

      case 'sort':
        submission.listpage = '1';
        build_search_values($submitButton);
        break;

      default:
        reenable();
        return;
    }
    if (remote) {
      if (submission.submit !== 'clear') {
        if ($submitButton.closest('form').find('[name=submit_button]').length === 0) {
          $submitButton.closest('form').append($('<input>', {type : 'hidden', name : 'submit_button', value : $submitButton.val()}));
        }
        $submitButton.closest('form').submit();
      }
      reenable();
      return;
    }
    jQuery.extend(submission, searchvalues);
    searchvalues = {};
    $submitButton.PDb_processSubmission();
  };
  var multi_search_has_value = function (combo) {
    var combo = combo || false;
    var fieldcheck = [];
    for (var attrname in searchvalues) {
      switch (attrname) {
        case 'combo_search':
          fieldcheck.push(combo && term_is_valid(searchvalues[attrname]));
        case 'ascdesc':
        case 'sortBy':
          break;
        default:
          fieldcheck.push(terms_are_valid(searchvalues[attrname], is_option_type(attrname)) && (searchvalues['search_field'] ? searchvalues['search_field'] !== 'none' : true));
      }
    }
    return search_is_valid(fieldcheck, PDbCMS.require_all);
  }
  var is_option_type = function (name){
    var el = $('.sort_filter_form input[name="' + name + '"]');
    if (el.prop('type') === 'text'){
      return false;
    }
    return true;
  }
  var search_is_valid = function (fields, all) {
    if (fields.length === 0) {
      return false;
    }
    var sum = true;
    for (var i = 0; i < fields.length; i++) {
      if (all == '0' && fields[i]) {
        return true;
      } else {
        sum = sum && fields[i];
      }
    }
    return sum;
  }
  var terms_are_valid = function (term, allow_any_length) {
    var any_length = allow_any_length | false;
    if (Array.isArray(term)) {
      for (var i = 0; i < term.length; i++) {
        if (term_is_valid(term[i], any_length)) {
          return true;
        }
      }
      return false;
    }
    return term_is_valid(term, any_length);
  }
  var term_is_valid = function (term, any_length) {
    any_length = any_length || false;
    // strip out wildcards, then check length
    return term.replace(/(\*|\?|_|%)/g, '').length >= ( any_length ? 1 : PDbCMS.min_term_length );
  }
  var combo_search_has_value = function () {
    return multi_search_has_value(true);
  }
  var build_search_values = function (el) {
    // collect the form values and add them to the search values object
    var $formcontainer = el.closest('form');
    if (!$formcontainer.length) {
      $formcontainer = el.closest('.pdb-list').find('.sort_filter_form');
    }
    $formcontainer.find('input[name$="[start]"]').each(function () {
      set_range_minmax($(this));
    });
    $formcontainer.find('input:not(input[type="submit"],input[type="radio"],input[type="checkbox"],input[type="hidden"], input:not([name])), select').each(function () {
      add_form_value($(this));
    });
    $formcontainer.find('input[type="radio"]:checked, input[type="checkbox"]:checked').each(function () {
      add_form_value($(this));
    });
    add_hidden_values($formcontainer);
  }
  var add_hidden_values = function (form) {
    form.find('input[type="hidden"]').each(function () {
      var el = $(this);
      submission[el.attr('name')] = el.val();
    });
  }
  var get_page_button = function (target) {
    var $button = $(target);
    if ($button.is('a'))
      return $button;
    return $button.closest('a');
  };
  var submit_remote_search = function (event) {
    submit_search(event, true);
  };
  var get_page = function (event) {
    $(event.target).data('submit', 'page');
    find_instance_index($(event.target));
    submit_search(event);
  };
  var find_instance_index = function (el) {
    var classes = el.closest('.wrap.pdb-list').prop('class');
    var match = classes.match(/pdb-instance-(\d+)/);
    submission.instance_index = match[1];
  };
  var clear_error_messages = function (init) {
    init = init || false;
    if (isError) {
      errormsg.children().slideUp(200, function () {
        errormsg.hide();
        if (init) {
          $('html').trigger('pdbcms-init');
        }
      });
    }
    isError = false;
  };
  var clear_search = function (button) {
    var form = button.closest('form');
    form.find('input:not([type=hidden],[type=submit]), select').PDb_clearInputs();
    form.find('input[name="combo_search"]').PDb_clearInputs();
    var text_search_options = form.find('[name=text_search_options]');
    if (text_search_options.length) {
      text_search_options.filter('#pdb-text_search_options-' + text_search_options.data('default')).prop('checked', true);
    }
    clear_error_messages();
  };
  var compatibility_fix = function () {
    // for backward compatibility
    if (typeof PDb_ajax.prefix === "undefined") {
      PDb_ajax.prefix = 'pdb-';
    }
    $('.wrap.pdb-list').PDb_idFix();
  };
  var encode_value = function (value) {
    if (Array.isArray(value)) {
      for (var i = 0; i < value.length; i++) {
        value[i] = encode_value(value[i]);
      }
      return value;
    } else {
      return encodeURI(value);
    }
  };
  var add_form_value = function (el) {
    var fieldname = el.attr('name');
    var multiple = el.is('[multiple]') || /\[\]$/.test(fieldname);
    var value = encode_value(el.val() || ''); // encodeURI(el.val());
    fieldname = fieldname.replace('[]', ''); // now we can remove the brackets
    if (multiple && typeof searchvalues[fieldname] === 'string') { //  && searchvalues[fieldname].length
      searchvalues[fieldname] = [searchvalues[fieldname]];
    }
    if (typeof searchvalues[fieldname] === 'object') {
      searchvalues[fieldname][searchvalues[fieldname].length] = value;
    } else if (fieldname === 'text_search_options' || value !== 'any') {
      searchvalues[fieldname] = value;
    }
  };
  var set_range_minmax = function (el) {
    var start = el;
    var end = $('input[name="' + el.prop('name').replace('[start]', '[end]') + '"]');
    if (start.val() !== '' && end.val() === '') {
      if (end.prop('max')) {
        end.val(end.prop('max'));
      } else if (end.data('default')) {
        end.val(end.data('default'));
      }
    } else if (start.val() === '' && end.val() !== '') {
      if (start.prop('min')) {
        start.val(start.prop('min'));
      } else if (start.data('default')) {
        start.val(start.data('default'));
      }
    }
  }
  var post_submission = function (button) {
    var target_instance = $('.pdb-list.pdb-instance-' + submission.instance_index);
    var container = target_instance.length ? target_instance : $('.pdb-list').first();
    var pagination = container.find('.pdb-pagination');
    var buttonParent = button.closest('fieldset, div');
    var spinner = $(PDb_ajax.loading_indicator).clone();
    $.ajax({
      type : "POST",
      url : PDb_ajax.ajaxurl,
      data : submission,
      beforeSend : function () {
        buttonParent.append(spinner);
      },
      success : function (html, status) {
        if (html.match(/^failed/)) {
          // if the session fails, post to server directly
          switch (button.data('submit')) {
            case 'page':
              var parser = document.createElement('a');
              parser.href = window.location.href;
              window.location.href = parser.protocol + '//' + parser.hostname + button.attr('href');
              break;
            default:
              button.before('<input type="hidden" name="submit" value="' + button.data('submit') + '" />');
              button.trigger('click.multisearch', true);
          }
        }
        var newContent = $(html);
        var replacePagination = newContent.find('.pdb-pagination');
        var replaceContent = newContent.find('.list-container').length ? newContent.find('.list-container') : newContent;
        newContent.PDb_idFix();
        replaceContent.find('a.obfuscate[data-email-values]').each(function () {
          $(this).PDb_email_obfuscate();
        });
        container.find('.list-container').replaceWith(replaceContent);
        pagination.remove();
        if (replacePagination.length) {
          replacePagination.each(function () {
            var builtContent = container.find('.list-container + .pdb-pagination').length ? container.find('.list-container + .pdb-pagination').last() : container.find('.list-container');
            builtContent.after(this);
          });
        }
        spinner.remove();
        submission_reset();
        // trigger a general-purpose event
        $('html').trigger('pdbListAjaxComplete');
      },
      error : function (jqXHR, status, errorThrown) {
        console.log('Participants Database JS error status:' + status + ' error:' + errorThrown);
      }
    });
  };
  $.fn.PDb_idFix = function () {
    var el = this;
    el.find('#pdb-list').addClass('list-container').removeAttr('id');
    el.find('#sort_filter_form').addClass('sort_filter_form').removeAttr('id');
  };
  $.fn.PDb_checkInputs = function (check) {
    var el = this;
    var number = el.length;
    var count = 0;
    el.each(function () {
      if ($(this).val() === check) {
        count++;
      }
    });
    return count === number;
  };
  $.fn.PDb_clearInputs = function (value) {
    value = value || '';
    this.each(function () {
      var el = $(this);
      if (el.is('[type=checkbox],[type=radio]')) {
        el.prop('checked', el.val() === 'any' || value !== '');
      } else {
        if (el.find('[value=any]').length) {
          el.val('any');
        } else {
          el.val(value);
        }
        if (el.chosen) {
          el.trigger('chosen:updated');
        }
      }
    });
  };
  $.fn.PDb_processSubmission = function () {
    post_submission(this);
  };
  return {
    run : function () {

      compatibility_fix();

      clear_error_messages(true);
      submission_reset();

      filterform.on('click.multisearch', '[type="submit"]', submit_search);
      remoteform.on('click.multisearch', '[type="submit"]', submit_remote_search);
      filterform.on('focus', '.search-control input[type!=submit],select', clear_error_messages);
      remoteform.on('focus', '.search-control input[type!=submit],select', clear_error_messages);

      var searchform = $('.pdb-searchform .sort_filter_form');
      if (PDbCMS.remote_search && PDbCMS.restore_search === "1") {
        searchform.saveForm();
      }
      searchform.hasSavedForm();
      if (PDbCMS.restore_search === "1" && searchform.hasClass('formsaved')) {
        $('html').on('pdbcms-init', function () {
          searchform.restoreForm();
          filterform.find('[data-submit=search]').trigger('click.multisearch');
        });
      }

      $('.pdb-list').on('click.page', '.pdb-pagination a', get_page);

      $('html').on('pdbListAjaxComplete', function () {
        $('html').trigger('pdbListFilterComplete');
      });
    },
    submit_search : submit_search
  };
}(jQuery));
jQuery(function () {
  "use strict";
  PDbListFilter.run();
});