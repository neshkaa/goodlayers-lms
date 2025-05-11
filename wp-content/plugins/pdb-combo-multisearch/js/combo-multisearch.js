/**
 * 
 * @author Roland Barker <webdesign@xnau.com>
 * @version 1.1
 */
PDb_ComboMultisearch = (function($) {
  var termlist, search_input, setup;
  var set_autocomplete = function () {
    search_input.autocomplete(setup);
  };
  var set_config = function () {
    if (PDbCMS.alpha_auto === '1') {
      setup.source = function( request, response ) {
          var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( request.term ), "i" );
          response( $.grep( termlist, function( item ){
              return matcher.test( item );
          }) );
      };
    } else {
      setup.source = termlist;
    }
  };
  var multicheck_selection = function (e) {
    var target = $(e.target);
    any_selection(target.closest('.checkbox-group').find('input[type=checkbox]'), target);
  };
  var any_selection = function (options,target) {
    switch (target.val()) {
      case 'any':
        options.not('[value=any]').prop('checked', false);
        break;
      default:
        options.filter('[value=any]').prop('checked', false);
        break;
    }
  };
  return {
    init: function() {
      search_input = $('input[name="combo_search"]');
      $('.multi-search-controls').find('.multicheckbox .checkbox-group input').change(multicheck_selection);
      if (PDbCMS.auto) {
        termlist = PDbCMS.autocomplete_terms;
        var spinner = $(PDb_ajax.loading_indicator).clone();
        setup = {
          delay: 100,
          minLength: PDbCMS.autocomplete_min_length,
          search: function(event, ui) {
            $(this).closest('div').append(spinner);
            $(this).on('blur', function () {
              spinner.remove();
            });
          },
          open: function(event, ui) {
            spinner.remove();
          },
          response: function(event, ui) {
            if (!ui.content.length) {
              spinner.remove();
            }
          },
          select: function(event,ui){
            var term = ui.item.value;
            if ( term.indexOf(' ') >= 0 ) {
              // if the search term has a space, enclose it
              ui.item.value = '"'+term+'"';
            }
            if (PDbCMS.auto_search!=="0") {
              search_input.val(ui.item.value);
              $(event.target).closest('form').find('[data-submit=search]').trigger('click'); 
            }
          }
        }
        set_config();
        set_autocomplete();
      }
    }
  }
}(jQuery));
jQuery(function() {
  PDb_ComboMultisearch.init();
});

