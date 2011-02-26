$(document).ready(function(){

    $('#btnShowAjax').click(function() {
        $.cyform('load', 'examples/complex', function(form, css, js){
            $('#form-cnt').html(form);
        });
    });
    
});


