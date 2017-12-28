require('./index.scss');
var Alert = require('COMMON/Alert-mb.js');
var toast = require('COMMON/toast.js');

//读参数
function urlKey(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return encodeURI(r[2]); return null;
}
//设置cookie
function setCookie(name, value, time) {
    var oDate = new Date();
    oDate.setTime(oDate.getTime() + time);
    document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + oDate.toUTCString();
}
//获取cookie
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
//验证身份证
function checkIdentity(identity) {
    var reg = /^[1-9]{1}[0-9]{14}$|^[1-9]{1}[0-9]{16}([0-9]|[xX])$/;
    if (reg.test(identity)) {
        return true;
    } else {
        return false;
    }
}


//从url取数据
var code = urlKey('shopCode'),           //商品id
    type = urlKey('shopType'),           //商品类型
    name = getCookie('name'),            //商品名
    date = getCookie('date'),            //出游日期
    onePrice = getCookie('price'),       //价格
    dateArr = date.split('-'),
    bottomTotalPri = $('#bottomTotalPri'),
    KC = getCookie('kc'),
    dataJson = {
        0: '周一',
        1: '周二',
        2: '周三',
        3: '周四',
        4: '周五',
        5: '周六',
        6: '周日'
    };

$('#onePrice').text(onePrice);
$('#totalPri').text(onePrice);
bottomTotalPri.text(onePrice);
$('#ticketTime').text(date + '(' + getDateWeek(dateArr[0], dateArr[1], dateArr[2]) + ')');

//商品名存半小时cookie，判断是否有商品名
if (name) {
    $('#ticketName').text(name);
} else {
    //没有的话跳转商品详情页面
    location.href = controller + '/p_ticket?shopCode=' + code + '&shopType=' + type;
}

toast.hide();

//减少
$('#ticketNumBox').on('click', '.js_red', function () {
    var parent = $(this).parents('.num-box'),
        pri = +$('#onePrice').text() * 100,
        tNum = parent.find('.choose-num'),
        numVal = +tNum.text();

    if (numVal == 1) {
        Alert('最少购买数量1');
        return false;
    }

    var totalPrice = +bottomTotalPri.text() * 100;
    tNum.text(numVal -= 1);
    totalPrice = ((totalPrice - pri) / 100).toFixed(2);
    Number(totalPrice) == totalPrice ? bottomTotalPri.text(Number(totalPrice)) : totalPrice;
});

//增加
$('#ticketNumBox').on('click', '.js_add', function () {
    var parent = $(this).parents('.num-box'),
        pri = +$('#onePrice').text() * 100,
        tNum = parent.find('.choose-num'),
        numVal = +tNum.text();

    KC == '不限' ? KC = 999 : KC = +KC;
    if (numVal == KC) {
        Alert('已达到库存上限');
        return false;
    }

    var totalPrice = +bottomTotalPri.text() * 100;
    tNum.text(numVal += 1);
    totalPrice = ((totalPrice + pri) / 100).toFixed(2);
    Number(totalPrice) == totalPrice ? bottomTotalPri.text(Number(totalPrice)) : totalPrice;
});

//去填写游客信息
$('#goTourBtn').click(function () {

    if ($('#reserveCheck').prop('checked')) {

        var confirmText = '是否确认' + $('#ticketTime').text() + '入住?';

        if (confirm(confirmText)) {
            $('#reserveMess').fadeOut(200, function () {
                $('#tourristMess').fadeIn(200);
            });

            var resPrice = bottomTotalPri.text();
            $('#orderTotalPri').text(resPrice);
        }
    } else {
        Alert('请先确认预定须知!!!');
    }
})

//返回
$('.iconfont.icon-zuojiantou').click(function () {
    $('#tourristMess').fadeOut(200, function () {
        $('#reserveMess').fadeIn(200);
    });
})



//确认下单
$('#takeOrderBtn').click(function () {

    var _this = $(this);

    _this.prop('disabled', true);
    _this.css('backgroundColor', '#ffa69c');

    toast.show();

    $.ajax({
        url: 'http://www.suiyiyou.net/index.php/weixin/Order/addTickOrder',
        data: data,
        type: 'POST',
        success: function (res) {
            toast.hide();
            var code = res.code,
                data = res.data;
            if (code == 200) {
                location.href = 'http://www.suiyiyou.net/index.php/Weixin/Jsapi/index?orderSn=' + data.orderSn + '&shopType=' + type + '&';
            } else {
                Alert(res.msg);
                _this.prop('disabled', false);
                _this.css('backgroundColor', '#ff1900');
            }
        }
    })
})

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


