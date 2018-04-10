require('./index.scss');
require('COMMON/bottom-menu.js');
var toast = require('COMMON/toast.js');
toast.show();

var list = $('#list'),
    typeList = $('#typeList'),
    typeItem = $('#typeItem'),
    noOrder = $('#noOrder'),
    dataBox = $('#orderList'),
    dataType = 'group',    //发送ajax的两个参数
    typeJson = {
        group: 'route_pay',
        tick: 'ticket_pay',
        scenery: 'hotel_pay'
    },
    detailJson = {
        group: 'route_detail',
        tick: 'ticket_detail',
        scenery: 'hotel_detail'
    }
dataState = '8',       //发送ajax的两个参数
    statusJson = {
        4: '未付款',
        8: '待确认',
        2: '已确认',
        1: '已出行',
        3: '已取消',
        6: '已退款'
    },
    firstBool = true,
    page = 1;


//读URL参数
function urlKey(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return encodeURI(r[2]); return null;
}

//列表菜单
$('.syy.icon-liebiao').click(function (e) {
    e.stopPropagation();
    list.toggleClass('hide').toggleClass('show');
    if (typeList.hasClass('show')) {
        typeList.removeClass('show').addClass('hide');
    }
})

//订单类型
$('#orderType').click(function (e) {
    e.stopPropagation();
    typeList.toggleClass('hide').toggleClass('show');
    if (list.hasClass('show')) {
        list.removeClass('show').addClass('hide');
    }
})

$(document).click(function (e) {
    e.stopPropagation();
    list.removeClass('show').addClass('hide');
    typeList.removeClass('show').addClass('hide');
})

$('.state-item').click(function () {
    toast.show();
    $(this).addClass('on').siblings().removeClass('on');
    dataState = $(this).attr('data-status');
    if (noOrder.css('display') == 'block') {
        noOrder.hide();
    }
    page = 1;
    firstBool = true;
    dataBox.html('');

    ajaxGetData(dataType, dataState);
})

$('.type-item').click(function () {
    toast.show();
    var type = $(this).text();
    dataType = $(this).attr('data-type');
    typeItem.text(type);

    if (noOrder.css('display') == 'block') {
        noOrder.hide();
    }
    page = 1;
    firstBool = true;
    dataBox.html('');

    ajaxGetData(dataType, dataState);
})

function ajaxGetData(type, status) {

    $.ajax({
        url: 'http://www.suiyiyou.net/index.php/Weixin/OrderCenter/getType',
        // url: 'http://localhost/tour/index.php/Weixin/OrderCenter/getType',
        type: 'POST',
        dataType: 'json',
        data: {
            shopType: type,
            status: status,
            page: page
        },
        success: function (res) {
            var code = res.code,
                data = res.data;

            if (code == 200) {
                toast.hide();
                if (data.length == 0 && firstBool) {
                    noOrder.show();
                    firstBool = false;
                } else {
                    pageRender(data);
                }
            }
        }
    })
}

function pageRender(data) {
    var html = '';

    for (var i = 0; i < data.length; i++) {

        if (dataState == '4') {
            html += '<a href="http://www.suiyiyou.net/index.php/Weixin/Jsapi/index?orderSn=' + data[i].code + '&shopType=' + dataType + '&">';
        } else {
            html += '<a href="' + controller + '/' + detailJson[dataType] + '?orderSn=' + data[i].code + '&shopType=' + dataType + '&status=' + data[i].status + '">';
        }

        html += '<div class="item"><img class="pic" src=' + data[i].img + '>';

        if (data[i].userid != '1') {
            html += '<i class="syy icon-jiaobiao"></i>';
        }

        html += '<div class="pro-mess">' +
            '<p class="name">' + data[i].name + '</p>' +
            '<p class="state">订单状态: <span class="res-state">' + statusJson[data[i].status] + '</span></p>' +
            '<p class="price"><span>实付款: </span><span class="res-price">￥' + data[i].price + '</span></p>' +
            '</div></div ></a>';
    }

    dataBox.append(html);
}

ajaxGetData(dataType, dataState);

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
if (urlKey('pid')) {
    link = location.href;
} else {
    link = location.href + '&pid=' + getCookie('pid');
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