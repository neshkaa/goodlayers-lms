/*
 * Participants Database Aux Plugin settings page support
 * 
 * sets up the tab functionality on the plugin settings page
 * 
 * this is the Combo Multisearch variant
 * 
 * @version 0.6
 * 
 */
PDbAuxSettings = (function ($) {
  var tabsetup
  var lastTab = 'pdbcms-settings-page-tab'
  var effect = {
    effect : 'fadeToggle',
    duration : 200
  };
  var getcookie = function () {
    if (Cookies) {
      return Cookies.get(lastTab);
    }
    return $.cookie(lastTab);
  }
  var setcookie = function (value) {
    if (Cookies) {
      Cookies.set(lastTab, value, {
        expires : 365,
        path : ''
      });
    } else {
      $.cookie(lastTab, value, {
        expires : 365
      });
    }
  }
  if ($.versioncompare("1.9", $.ui.version) == 1) {
    tabsetup = {
      fx : {
        opacity : "show",
        duration : "fast"
      },
      cookie : {
        expires : 1
      }
    }
  } else {
    tabsetup = {
      hide : effect,
      show : effect,
      active : getcookie(),
      activate : function (event, ui) {
        setcookie(ui.newTab.index());
      }
    }
  }
  return {
    init : function () {
      var wrapped = $(".pdb-aux-settings-tabs .ui-tabs form>h2, .pdb-aux-settings-tabs .ui-tabs form>h3").wrap("<div class=\"ui-tabs-panel\">");
      var wrapclass = $('.pdb-aux-settings-tabs').attr('class');
      if (wrapped.length) {
        var submit_button = $('p.submit').first().remove();
        wrapped.each(function (index) {
          var button = submit_button.clone();
          button.find('input').attr('id','submit'+index);
          $(this).parent().append($(this).parent().nextUntil("div.ui-tabs-panel"));
          $(this).parent().append(button);
        });
        $(".ui-tabs-panel").each(function () {
          var anchor = $(this).find('a[name]');
          if (anchor.length) {
            var str = anchor.attr('name').replace(/\s/g, "_");
            $(this).attr("id", str.toLowerCase());
          }
        });
        $(".pdb-aux-settings-tabs").removeClass().addClass(wrapclass + " main");
        $('.pdb-aux-settings-tabs .ui-tabs').tabs(tabsetup).bind('tabsselect', function (event, ui) {
          var activeclass = $(ui.tab).attr('href').replace(/^#/, '');
          $(".pdb-aux-settings-tabs").removeClass().addClass(wrapclass + " " + activeclass);
        });
        $("form").attr("autocomplete", "off");
      }
    }
  }
}(jQuery));
jQuery(function () {
  PDbAuxSettings.init();
});