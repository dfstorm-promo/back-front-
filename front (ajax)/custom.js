$(function(){
    if($('input').is('#input-model') && !$('#input-model').val()){
        let token = getUrlParam();
	$.ajax({                
            url: 'index.php?route=extension/export_import/getLastSku&user_token='+token.user_token,
            type: 'post',
            dataType: 'json',
            success: function(json) {
                    $('#input-model').attr("value", json['sku']);
                    $('#input-model').after('<span class="input-model-help">Используйте только числовые значения. Если это не вариация другого товара, то используйте автоматически установленное значение кода товара: '+json['sku']+'</span>');
            },
            error: function(){
                console.log('error');
            }
	});
    }
    $('#input-name1').bind('change click keyup', function(){
        if(!$('span').is('.input-name1-help')){
            $('#input-name1').after('<span class="input-name1-help">Нажмите на ссылку, чтобы <a href="#" onclick="return false;" id="generate_seo_url_a">сгенерировать новый SEO URL</a></span>');
            $('#generate_seo_url_a').on('click', function(){
                let name = $('#input-name1').val();
                let token = getUrlParam();
                $.ajax({                
                    url: 'index.php?route=extension/export_import/generateNewSeoUrl&user_token='+token.user_token,
                    type: 'post',
                    dataType: 'json',
                    data: {'name': name},
                    success: function(json) {
                        $('input[name="product_seo_url[0][1]"]').attr('value', json);
                        if($('input[name="product_seo_url[0][1]"]').val() === json){
                            alert('Успешно сгенерировано!');
                        }    
                    },
                    error: function(){
                        console.log('error');
                    }
                });
            });
        }
    });
});

function getUrlParam(){
    let param = window.location.search;
    let params, values, result;
    params = values = result = [];

    params = (param.substr(1)).split('&');
    if(params[0] == "") return false;
    for (i = 0; i < params.length; i ++){
        values = params[i].split('=');
        result[values[0]] = values[1];
    }

    return result;
}
