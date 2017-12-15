require('./index.scss');
var toast = require('COMMON/toast.js');
toast.show();

//读URL参数
function urlKey(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return encodeURI(r[2]); return null;
}

var URL = 'http://www.suiyiyou.net/index.php/Weixin/List/',
// var URL = 'http://localhost/tour/index.php/Weixin/List/',

    whichPage = $('#dataType').attr('data-type'),
    
    hrefJson = {
        GroupList : 'p_route',        //跟团游
        SceneryList : 'p_hotel',      //酒店
        TickList : 'p_ticket'         //门票
    },

    bool = true,
    page = 1;

var content = $('#contentItem'),
    noMore = $('#noMore'),          //没有数据
    loadMore = $('#loadMore');

function AjaxGetList(key) {

    var key = key || '';

    $.ajax({
        url: URL + whichPage,
        data: {
            page: page,
            search: key
        },
        success: function (res) {
            var code = res.code,
                data = res.data;

            if (code == 200) {
                renderRoute(data);
            }
        }
    })
}

//数据渲染页面
function renderRoute(data) {

    if (bool) {
        content.html('');
        toast.hide();
        bool = false;
    }

    loadMore.hide();

    var len = data.length,
        html = '';

    for (var i = 0; i < len; i++) {
        html += '<a href=' + controller + '/' + hrefJson[whichPage] + '?shopCode=' + data[i].code + '&shopType=' + data[i].shop_type + '><div class="item">' +
            '<img class="item-pic" src=' + data[i].imgFile + '><div class="item-mess">';

        //门票的名字拼接规则不一样
        if (whichPage == 'TickList') {
            html += '<div class="item-name">' + data[i].name + '+' + data[i].t_tick_cat + '+' + data[i].t_tick_spot + '</div>';
        } else {
            html += '<div class="item-name">' + data[i].name + '</div>';
        }

        html += '<p class="item-sales">销量 ：' + data[i].sell + '</p>' +
            '<p class="item-price">售价 :<span class="price">￥' + data[i].price + '</span> 起</p>';
        
        if(data[i].js_price){
            html += '<p class="item-js-price">结算价 :<span class="price">￥' + data[i].js_price + '</span></p>';
        }
        
        html += '</div></div></a>';
    }

    if(len < 10){
        noMore.show();
    }

    content.append(html);
}

//滚动条到底部
$(window).scroll(function () {
    if ($(document).scrollTop() >= $(document).height() - $(window).height()) {

        if (noMore.css('display') == 'block' || loadMore.css('display') == 'block') {   //出现没有更多数据 不加载
            return false;
        }

        page++;

        loadMore.show(); //加载动画出现

        AjaxGetList();

    }
})

AjaxGetList();


//搜索
var searchInp = $('#searchInp');
searchInp.on('input', function () {

    var key = $(this).val();
    page = 1;
    bool = true;
    toast.show();

    AjaxGetList(key);
})


var clear = $('#clear');
searchInp.focus(function () {
    clear.show();
})

clear.click(function () {

    searchInp.val('');
    $(this).hide();

    searchInp.trigger('input');
})

var allDocument = $(document),
    scrollTop = $('#scrollTop');

allDocument.scroll(function () {

    var top = allDocument.scrollTop();
    
    if(top <= 200){
        scrollTop.css('opacity', top/200);
    }

    if(top > 200){
        scrollTop.css('opacity', '0.9');
    }
})

scrollTop.click(function(){
    var top = allDocument.scrollTop();
    var up = setInterval(function () {
        top -= 30;
        window.scrollTo(0, top);
        if (top <= 0) {
            clearInterval(up);
        }
    }, 10)
})

$('#eye').click(function(){
    var _this = $(this);

    if(_this.hasClass('icon-open')){
        _this.removeClass('icon-open').addClass('icon-close');
        $('.item-js-price').hide();
    }else{
        _this.removeClass('icon-close').addClass('icon-open');
        $('.item-js-price').show();
    }

})


function getCookie(name) {
    var arr = document.cookie.split('; ');
    var i = 0;
    for (i = 0; i < arr.length; i++) {
        var arr2 = arr[i].split('=');

        if (arr2[0] == name) {
            var getC = decodeURIComponent(arr2[1]);
            return getC;
        }
    }
    return '';
}

CONFIG.jsApiList = [    //需要调用的接口
    'onMenuShareTimeline',
    'onMenuShareAppMessage'
]
wx.config(CONFIG);
var link;
if(urlKey('pid')){
    link = location.href;
}else{
    link = location.href + '?pid=' + getCookie('pid');
}
wx.ready(function () {
    //分享朋友圈
    wx.onMenuShareTimeline({
        title: document.title,
        link: link,
        imgUrl: public + '/Weixin/image/wxbg.png'
    });
    //分享朋友
    wx.onMenuShareAppMessage({
        title: document.title,
        desc: '我的旅游小店，随我心，游天下',
        link: link,
        imgUrl: public + '/Weixin/image/wxbg.png',
    });
})