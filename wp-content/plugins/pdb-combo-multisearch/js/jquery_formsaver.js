/**
 * 
 * @author hackprime, xnau
 * @version 0.2
 * 
 */
(function ($) {
    function formsaver(method, container) {
        function getStorageId(container) {
            return 'formdata__$url__$extra'.replace('$url', location.pathname)
                                           .replace('$extra', container.attr('id') || '');
        }

        var storageId = getStorageId(container),
            controller = {
                save: function () {
                    this._save(storageId, this.extractValues());
                },
                restore: function () {
                    this.fillFields(this._load(storageId));
                },
                clear: function () {
                    this._remove(storageId);
                },
                has_saved: function () {
                  if (!jQuery.isEmptyObject(this._load(storageId))) {
                    container.addClass('formsaved');
                  }
                },

                extractValues: function () {
                    var formData = container.find(":input[name]").not('[type=hidden]').serializeArray(),
                        preparedData = {};
                    $.each(formData, function (index, element) {
                        var name = element.name,
                            value = encodeURIComponent(element.value);
                        if (preparedData[name]) {
                            preparedData[name] = preparedData[name] instanceof Array ?
                                                 preparedData[name].concat(value) :
                                                 [preparedData[name], value];
                        } else {
                            preparedData[name] = value;
                        }
                    });
                    return preparedData;
                },
                fillFields: function (formData) {
                    $.each(formData, function (name, value) {
                        var field = container.find('[name="' + name + '"]:not([type=hidden])'),
                            inputType = field.prop('type');
                        value = value instanceof Array ? value.map(decodeURIComponent) :
                                                         decodeURIComponent(value);
                        if (inputType === 'checkbox') {
                          var values = value instanceof Array ? value : [value];
                          field.each(function(){
                            var el = $(this);
                            el.prop('checked', values.indexOf(el.val()) !== -1);
                          });
                        } else if (inputType === 'radio') {
                            field.filter("[value=" + value + "]").prop('checked', true);
                        } else {
                            field.val(value).trigger('chosen:updated');
                        }
                    });
                },

                _save: function (storageId, data) {
                    localStorage[storageId] = JSON.stringify(data);
                },
                _load: function (storageId) {
                    return localStorage[storageId] ? JSON.parse(localStorage[storageId]) : {};
                },
                _remove: function (storageId) {
                    localStorage.removeItem(storageId);
                }
            },
            methodsQueue = method instanceof Array ? method : [method];

        $.each(methodsQueue, function (index, method) {
            controller[method]();
        });
    }
    $.fn.saveForm = function () {
        formsaver('save', $(this));
    };
    $.fn.restoreForm = function () {
        formsaver(['restore', 'clear'], $(this));
    };
    $.fn.clearSavedForm = function () {
        formsaver('clear', $(this));
    };
    $.fn.hasSavedForm = function () {
      formsaver('has_saved', $(this));
    }
})(jQuery);
