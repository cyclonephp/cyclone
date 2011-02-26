(function($){

    $.cyform = $.fn.cyform = function(method) {

        this.load = function(form, onLoaded) {
            $.getJSON('cyform/load', {'form' : form}
                , function(response){
                    onLoaded(response.html, response.css, response.js);
                });
        }

        var _method = arguments[0];

        var args = new Array();
        if (arguments.length > 1) {
            for (var i = 1; i < arguments.length; ++i) {
                args[i - 1] = arguments[i];
            }
        }

        debug('method: ' + _method);
        debug('arguments: ');
        debug(args);

        this[_method].apply(this, args);
    }
    
})(jQuery);

function debug(str) {
    console && console.log && console.log(str);
}