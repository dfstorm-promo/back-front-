function notify_email(id){
	URL = window.location.host;
	product_id = $('#'+id+' [name=product_id]').val();
	$.ajax({
		url: 'https://' + URL +'/index2.php?page=shop.waiting_list&product_id='+product_id+'&only_page=1',
		type:'POST',
		success: function(data){
			$('#addModal .modal-content').html(data);
			$('#addModal').modal('show');
		}
	});
}

function sendNotifyEmail(id){
	$.ajax({
		url: "index.php",
		cache: false,
		data: $('#'+id).serialize(),
		success: function(data){
            $('#notyfyModal .modal-header h2').html(data.text);
			$('#notyfyModal .modal-body').html(data.info);
		}
	})
	return false;
}

function handleAddToCart( formId, parameters ) {
	$.ajax({
		url: "index.php",
		cache: false,
		data: $('#'+formId).serialize(),
		success: function(data){
			$('#addModal .modal-content').html(data);
			$('#addModal').modal('show');
            getFlyBasket();
		}
	});
}

function handleClickBut( formId, parameters ) {
	$.ajax({
		url: "index.php",
		cache: false,
		data: $('#'+formId).serialize(),
		success: function(data){
            $("#clickbuy").modal('show');
            $('#clickbuy .modal-header h2').html(data.text);
			$('#clickbuy .modal-body').html(data.info);
		}
	});
}

function getFlyBasket(){
    $.ajax({
        url: 'index.php',
        cache: false,
        dataType: 'json',
        data:{
            'func': 'getInfoForProductBasket'
        },
        success: function(data){
            let count = data.products_count;
            let sum = data.final_sum;
            let discount = data.discount_amount;
            let div;

            $('.float-basket-products-ajax').empty();

            if(data.products_count > 0){
                for(i=0;i<data.products_count;i++){
                    div =  '<div class="float-basket-product-item">';
                    div += '<div class="col-lg-4 float-basket-product-thumb">';
                    div += '<img src="/components/com_virtuemart/shop_image/product/'+data[i].thumb+'" /></div>';
                    div += '<div class="col-lg-10 float-basket-product-name">'+data[i].quantity+' * ';
                    div += '<a href="'+data[i].url+'">'+data[i].name+'</a><p>'+data[i].price+' Р</p></div>';
                    div += '<div class="col-lg-2 float-basket-product-close">';
                    div += '<a href="#" data-id="'+data[i].product_id+'" onclick="return false;">';
                    div += '<i class="fa fa-times-circle" aria-hidden="true"></i></a></div></div>';
                    $('.float-basket-products-ajax').append(div);
                }
            }else{
                $('.float-basket-products-ajax').append('<div class="float-basket-no-products">Нет товаров</div>');
            }

            $('.float-basket-prices-sum-ajax').empty().append(sum+' Р');
            $('.float-basket-prices-discount-ajax').empty().append(discount+' Р');
            $('.float-basket-quantity-ajax').empty().append(count);
            $('.cart-count span').empty().append(count);

            $('.float-basket-product-close a').on('click', function(){
                let product_id = $(this).attr('data-id');
                removeFromBasket(product_id);
            });    
        },
        error: function(){
            console.log('error');
        }
    });
    
function removeFromBasket(product_id){
    if(product_id){
        $.ajax({
            url: 'index.php',
            cache: false,
            data:{
                'func': 'cartDelete',
                'page': 'shop.cart',
                'product_id': product_id
            },
            success: function(){
                if(window.location.pathname =='/magazin/view-your-cart-contents' || window.location.pathname =='/index.php' || window.location.pathname =='/magazin/check-out' || window.location.search.indexOf('checkout.index') != -1){
                    location.reload();
                }else{                
                    getFlyBasket();
                }    
            }
        });
    }    
}
    
}

$(function(){
    
    //Fly basket block start
    getFlyBasket();
    
    $(window).scroll(function () {
        if ($(this).scrollTop() > 150) {
            $('.float-basket').show();
        } else {
            $('.float-basket').hide();
        }
    });
    
    $('.float-basket').hover(function(){
        $('.float-basket-block').slideDown('slow');
    }, function(){
        $('.float-basket-block').slideUp('slow');
    });    
    //Fly basket block end
    
    $('input#inputPhoneNum, input#inputNameKey, input#notify_phone, input#notify_email, input#inputTelNum').blur(check_inputs);
        
    function check_inputs(){    
        let type = $(this).attr('type');
        let val = $(this).val();

        if(type == 'tel'){
            let phone_mask = /^\+7\([^8]\d{2}\)\s\d{3}-\d{2}-\d{2}$/;
            if (phone_mask.test(val)){
                $(this).removeClass('error_clickbuy').addClass('not_error_clickbuy');
                $(this).next('.error-box').html('');
            }else{
                $(this).removeClass('not_error_clickbuy').addClass('error_clickbuy');
                $(this).next('.error-box').html('Введите корректный номер').css('color', '#EF7500');
            }
        }    
        if(type == 'email'){
            let email_mask = /^([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+/;
            if (val !== '' && email_mask.test(val)){
                $(this).addClass('not_error_clickbuy');
                $(this).next('.error-box').html('');
            }else{
                $(this).removeClass('not_error_clickbuy').addClass('error_clickbuy');
                $(this).next('.error-box').html('Введите корректный email').css('color', '#EF7500')
            }
        }
    }

    $('#click_buy_button').on('click', function(btn){
        btn.preventDefault();
        if($('input#inputPhoneNum').hasClass('not_error_clickbuy')){
            $(this).trigger('submit');
        }else{
            return false;
        }
    });
    
    $('form#waiting').submit(function(btn){
        let id = $(this).attr('id');
        btn.preventDefault();
        if($('.not_error_clickbuy').length >= 1){
           sendNotifyEmail( this.id );  
        }else{
          return false;
       }
    });
    
    $('#buy_with_help_a').on('click', function(){
        if($('#inputTelNum').hasClass('not_error_clickbuy')){
            let telNum = $('#inputTelNum').val();
            $('#input_Tel_Num').val(telNum);
            $('#buy_with_help_button').click();
            ym(14125342, 'reachGoal', 'GET_HELP');
        }
        return false;

    });

});