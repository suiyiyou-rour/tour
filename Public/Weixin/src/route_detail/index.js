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
    $('#orderNum').html(data.g_order_sn);
    $('#proNum').html(data.g_group_code);
    $('#orderName').html(data.g_group_name);

    var timeArr = data.g_go_time.split('-');
    $('#orderTime').html(data.g_go_time + '(' + getDateWeek(timeArr[0], timeArr[1], timeArr[2]) + ')');

    $('#manNum').html('￥' + data.g_man_plane_price + ' x ' + data.g_man_num);
    if (data.g_child_num != '0') {
        $('#childNum').html('￥' + data.g_child_plane_price + ' x ' + data.g_child_num);
    } else {
        $('#childNum').html(data.g_child_num + '<span class="tips"> (无儿童价产品都以成人票计算)</span>');
    }

    if(data.g_dfc_num){
        $('#dfcNum').html('￥' + data.g_dfc_plat_price + ' x ' + data.g_dfc_num);
    }

    if (data.g_zf_info) {
        
        var zfhtml = '',
            zfInfo = JSON.parse(data.g_zf_info),
            len = zfInfo.length;

        for (var i = 0; i < len; i++) {
            if(zfInfo[i].num == '0'){
                continue;
            }
            zfhtml += '<p><span>'+zfInfo[i].name+': </span><span> ￥'+zfInfo[i].price+' x '+zfInfo[i].num+'</span></p>';
        }

        if(!zfhtml){
            zfhtml = '--';
        }

        $('#zfInfo').html(zfhtml);
    }


    $('#connectName').html(data.g_name);
    $('#connectMobile').html(data.g_mobile);
    $('#connectIdent').html(data.g_identification);

    $('#totalNum').html(data.g_order_price);

    if (data.g_identity_info.length > 0) {
        var html = '';
        for (var i = 0; i < data.g_identity_info.length; i++) {
            html += '<div class="identify">' +
                '<p class="title-mess"><span class="title">游客姓名 : </span><span class="mess">' + data.g_identity_info[i].name + '</span></p>' +
                '<p class="title-mess"><span class="title">身份信息 : </span><span class="mess">' + data.g_identity_info[i].identify + '</span></p>' +
                '</div>';
        }

        $('#identifyBox').append(html);
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
var link;
if(urlKey('pid')){
    link = location.href;
}else{
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


