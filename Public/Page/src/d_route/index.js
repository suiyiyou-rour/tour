require('./index.scss');

$('.item').click(function(){

    var _this = $(this);
    _this.addClass('on').find('.item-inp').focus();;

})

$('.item-inp').blur(function(){
    if(!$(this).val()){
        $(this).parents('.item').removeClass('on');
    }
})
