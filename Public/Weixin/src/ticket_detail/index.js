require('./index.scss');
var Alert = require('COMMON/Alert-mb.js');
var toast = require('COMMON/toast.js');

//读URL参数
function urlKey(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return encodeURI(r[2]); return null;
}
//日期计算星期
function getDateWeek(year, month, day) {
    var tmpdate = new Date(year, month - 1, day);
    var json = {
        0: '周日',
        1: '周一',
        2: '周二',
        3: '周三',
        4: '周四',
        5: '周五',
        6: '周六'
    }
    return json[tmpdate.getDay()];
}

function dataRender(data) {

    $('#statusTitle').html(statusJson[status]);
    $('#orderPic').attr('src', data.img);
    $('#orderNum').html(data.t_order_sn);
    $('#proNum').html(data.t_tick_code);
    $('#orderName').html(data.t_tick_name);

    var timeArr = data.t_go_date.split('-');
    $('#orderTime').html(data.t_go_date + '(' + getDateWeek(timeArr[0], timeArr[1], timeArr[2]) + ')');

    $('#num').html('￥' + data.t_tick_my_price + ' x ' + data.t_tick_num);

    $('#connectName').html(data.t_order_user_name);
    $('#connectMobile').html(data.t_order_user_mobile);
    $('#connectIdent').html(data.g_identification);

    $('#totalNum').html(data.t_tick_price);

    if (data.playerInfo) {

        if(data.playerInfo.length > 0){
            var html = '';
            for (var i = 0; i < data.g_identity_info.length; i++) {
                html += '<div class="identify">' +
                    '<p class="title-mess"><span class="title">游客姓名 : </span><span class="mess">' + data.g_identity_info[i].name + '</span></p>' +
                    '<p class="title-mess"><span class="title">身份信息 : </span><span class="mess">' + data.g_identity_info[i].identify + '</span></p>' +
                    '</div>';
            }
    
            $('#identifyBox').append(html);
        }

    }

    if (data.g_remark) {
        $('#remarks').html(data.g_remark);
    }

}

var orderSn = urlKey('orderSn'),
    type = urlKey('shopType'),
    status = urlKey('status'),
    URL = 'http://www.suiyiyou.net/index.php/weixin/order/getOrderDetails',
    data = {
        orderSn: orderSn,
        shopType: type
    },
    statusJson = {
        4: '未付款',
        8: '待确认',
        2: '已确认',
        1: '已出行',
        3: '已取消'
    };

$.ajax({
    url: URL,
    data: data,
    type: 'POST',
    success: function (res) {
        var code = res.code,
            data = res.data;
        if (code == 200) {
            toast.hide();
            dataRender(data);
        } else {
            Alert(res.msg);
        }
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
wx.ready(function () {
    //分享朋友圈
    wx.onMenuShareTimeline({
        title: document.title,
        link: location.href + '?pid=' + getCookie('pid'),
        imgUrl: public + '/Weixin/image/wxbg.png'
    });
    //分享朋友
    wx.onMenuShareAppMessage({
        title: document.title, // 分享标题
        desc: '我的旅游小店，随我心，游天下', // 分享描述
        link: location.href + '?pid=' + getCookie('pid'), // 分享链接，该链接域名必须与当前企业的可信域名一致
        imgUrl: public + '/Weixin/image/wxbg.png', // 分享图标
    });
})


