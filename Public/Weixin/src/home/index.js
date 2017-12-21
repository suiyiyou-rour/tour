require('./index.scss');
require('COMMON/bottom-menu.js');
var AlloyFinger = require('COMMON/alloy_finger.js');
var Alert = require('COMMON/Alert-mb.js');

// var URL = 'http://localhost/tour/',
var URL = 'http://www.suiyiyou.net/',
    json = {
        group : 'p_route',
        tick : 'p_ticket'
    }

//读URL参数
function urlKey(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return encodeURI(r[2]); return null;
}

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

//轮播图
function carousel(data) {
    var len = data.length,
        html = '';
    for (var i = 0; i < len; i++) {
        if(i == 0){
            html += '<a href="' + window.controller + '/p_ticket?shopCode=' + data[i].code + '&shopType=' + data[i].shop_type + '" class="item-bg on"><img src=' + data[i].imgFile + '></a>';
            continue;
        }
        html += '<a href="' + window.controller + '/p_ticket?shopCode=' + data[i].code + '&shopType=' + data[i].shop_type + '" class="item-bg"><img src=' + data[i].imgFile + '></a>'
    }
    $('#carouselBg').append(html);

    //轮播
    var picArr = $('#carouselBg'),
        itemBg = $('.item-bg'),
        pointers = $('.pointer'),
        nowNum = 0;

    function left() {
        itemBg.eq(nowNum).animate({ left: '-100%' }, 300).next().animate({ left: '0%' }, 300);
        pointers.eq(nowNum).removeClass('on').next().addClass('on');
        nowNum += 1;
    }

    var timer = setInterval(function () {

        if (nowNum == 2) {

            itemBg.eq(0).animate({ left: '0' }, 300);
            itemBg.eq(2).animate({ left: '100%' }, 300);
            itemBg.eq(1).css('left', '100%');
            pointers.eq(nowNum).removeClass('on');
            pointers.eq(0).addClass('on');
            nowNum = 0;

        } else {
            left();
        }

    }, 3000);

    new AlloyFinger(picArr[0], {

        swipe: function (evt) {

            var dir = evt.direction;

            if (dir == "Left" && nowNum < 2) {

                left();

            } else if (dir == "Right" && nowNum > 0) {

                itemBg.eq(nowNum).animate({ left: '100%' }, 300).prev().animate({ left: '0%' }, 300);
                pointers.eq(nowNum).removeClass('on').prev().addClass('on');
                nowNum -= 1;

            }
        },
    })
}

//首页6商品展示数据
function homePro(data) {
    var len = data.length,
        html = '';
    for (var i = 0; i < len; i++) {

        html += '<a href=' + window.controller + '/'+json[data[i].shop_type]+'?shopCode=' + data[i].code + '&shopType=' + data[i].shop_type + '><div class="item">';

        html += '<img class="pro-pic" src=' + data[i].imgFile + '>';
        html += '<p class="pro-name">' + data[i].name + '</p>';
        html += '</div></a>';
    }

    $('#productBox').append(html);
}

//首页轮播
$.ajax({
    url: URL + '/index.php/Weixin/List/DynamicFigure',
    success: function (res) {
        var code = res.code,
            data = res.data;

        if (code == 200) {
            carousel(data);
        }
    }
})

//首页商品
$.ajax({
    url: URL + '/index.php/weixin/List/RecommendList',
    success: function (res) {
        var code = res.code,
            data = res.data;

        if (code == 200) {
            homePro(data);
        }
    }
})

function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}

$('#searchInp').click(function(){
    window.location.href = window.controller + "/s_route";
})

CONFIG.jsApiList = [    //需要调用的接口
    'onMenuShareTimeline',
    'onMenuShareAppMessage'
]
wx.config(CONFIG);
var link = location.origin + location.pathname + '?pid=' + getCookie('pid');
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

