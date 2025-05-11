/**
 * @version 1.5
 * @author Roland Barker <webdesign@xnau.com>
 */
MultiFields = (function ($) {
  var confirmationBox, ui, fieldselect;
  var unsavedChanges = false;
  var dialogOptions = {
    dialogClass : 'participants-database-confirm',
    autoOpen : false,
    height : 'auto',
    minHeight : '20'
  };
  var addField = function (e) {
    var spinner = fieldselect.closest('.add-field').find('.ajax-loading').css({visibility : 'visible'});
    $.post(
            ajaxurl,
            {
              fieldname : fieldselect.val(),
              action : PDbCMS.action,
              task : 'add_field',
              _pdbnonce : PDbCMS._pdbnonce
            },
            addFieldToUI
            ).done(
            function () {
              spinner.css({visibility : 'hidden'});
              removeAvailable();
            });
  }
  var addFieldToUI = function (data) {
    $('.multi-field-config-area').append($(data.html));
  }
  var removeAvailable = function () {
    var fieldname = fieldselect.val();
    fieldselect.find('option[value=' + fieldname + ']').attr('disabled', true);
    fieldselect.val('');
    var combofield = $('#combo_field_select');
    combofield.find('[value="' + fieldname + '"]').prop('disabled', true);
    combofield.multiSelect('refresh');
  }
  var deleteField = function (event) {
    event.preventDefault();
    var el = $(this);
    var parent = el.closest('.field-editor');

    confirmationBox.html(PDbCMS.delete_confirm.replace('%s', parent.find('.field-title').html()));

    confirmationBox.dialog(dialogOptions, {
      buttons : {
        "Ok" : function () {
          parent.css('opacity', '0.3');
          $(this).dialog('close');
          $.ajax({
            type : 'post',
            url : ajaxurl,
            data : {
              fieldname : parent.data('fieldname'),
              action : PDbCMS.action,
              task : 'delete_field',
              _pdbnonce : PDbCMS._pdbnonce
            },
            beforeSend : function () {
            },
            success : function (response) {
              if (response.status === 'success') {
                parent.slideUp(600, function () {
                  parent.remove();
                  // add the field's option back to the field selector
                  fieldselect.find('option[value=' + response.fieldname + ']').attr('disabled', false);
                });
              } else {
                parent.css('opacity', 'inherit');
              }
            }
          });// ajax
        }, // ok
        "Cancel" : function () {
          $(this).dialog('close');
        } // cancel
      } // buttons
    });// dialog

    confirmationBox.dialog('open');
    return false;
  };
  var sortFields = {
    helper : fixHelper,
    handle : '.dragger',
    update : function (event, ui) {
      $.post(ajaxurl, {
        action : PDbCMS.action,
        task : 'reorder_fields',
        _pdbnonce : PDbCMS._pdbnonce,
        list : serializeList($(this))
      });
    }
  };
  var fixHelper = function (e, ui) {
    ui.children().each(function () {
      $(this).width($(this).width());
    });
    return ui;
  };
  var setChangedFlag = function () {
    unsavedChanges = true;
  };
  var clearChangedFlag = function () {
    unsavedChanges = false;
  };
  var serializeList = function (container) {
    var n = 0;
    var query = '';
    container.find('.field-editor').each(function () {
      var el = $(this);
      if (query !== '') {
        query = query + '&';
      }
      query = query + n + '=' + el.data('fieldname');
      n++;
    });
    return query;
  };
  var handleUnload = function (e) {
    if (unsavedChanges) {
      e.preventDefault();
      e.returnValue = '';
    } else {
      delete e['returnValue'];
    }
  }
  return {
    init : function () {
      confirmationBox = $('#confirmation-dialog');
      ui = $('#multi-search-fields-selector-ui');
      fieldselect = ui.find('select[name=new_field_name]');
      $('#add_multi_field').click(addField);
      $('.multi-field-config-area').sortable(sortFields);
      ui.on('click', '.editor-tools .delete-field', deleteField);
      ui.find('button[type=submit]').click(clearChangedFlag);
      ui.find('.field-editor input, .field-editor textarea').on('input', setChangedFlag);
      ui.find('.field-editor select, .field-editor input[type=checkbox]').on('change', setChangedFlag);
      $(window).on('beforeunload', handleUnload);
    }
  }
}(jQuery));
jQuery(function () {
  MultiFields.init();
});

