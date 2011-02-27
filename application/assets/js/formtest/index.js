$(document).ready(function(){

    $('#btnShowAjax').click(function() {
        /*$.cyform('load', 'examples/complex', function(form, css, js){
            $('#form-cnt').html(form);

            $('#form-cnt form [type=submit]').click(function(event){
                $('#form-cnt form').cyform('submit', {
                    'url' : 'formtest/ajaxsave'
                })
            });
        });*/

        $('#form-cnt').cyform('load', 'examples/complex');
    });
    
});


