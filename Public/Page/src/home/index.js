require('./index.scss');


$('.item').click(function(){
    $(this).addClass('on').siblings().removeClass('on');
    var index = $(this).index();
    $('.seach-inp').eq(index).show().siblings().hide();
})