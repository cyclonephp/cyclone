(function($){

    $.cyform = $.fn.cyform = function(method) {

        this.load = function(form, options) {

            var opts = {
                'ajaxify' : true,
                'onLoaded' : function(response) {
                    jcyform.html(response.html);
                }
            };

            $.extend(opts, options);

            $.getJSON('cyform/load', {'form' : form}, function(response){
                opts['onLoaded'](response);
                opts['ajaxify'] && cyform.ajaxify();
            });
        }

        this.ajaxify = function() {
            var formElem = $('form', cyform);
            var method = formElem.attr('method').toUpperCase();
            var action = formElem.attr('action');
            $('form input[type=submit]', cyform).click(function(event){
                event.preventDefault();
                var formData = cyform.serialize();
                debug(formData == null);
                if (formData != null) {
                    // should take care about method (use $.ajax() instead)
                    $.getJSON(action, formData, function(response) {
                        debug(response)
                    });
                }
            })
        }

        this.serialize = function() {
            var rval = new Object();
            var isEmpty = true;

            $('form input, form select, form textarea').each(function(){
                debug($(this).attr('name') + ': ' + $(this).val());
            });

            debug(rval);
            return rval;
        }

        var _method = arguments[0];

        var args = new Array();
        if (arguments.length > 1) {
            for (var i = 1; i < arguments.length; ++i) {
                args[i - 1] = arguments[i];
            }
        }

        var cyform = this;
        var jcyform = $(cyform);

        this[_method].apply(this, args);
    }
    
})(jQuery);

function debug(str) {
    console && console.log && console.log(str);
}