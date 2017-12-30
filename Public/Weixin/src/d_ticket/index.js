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
var onePrice = getCookie('price'),   //成人价格
    code = urlKey('shopCode'),           //商品id
    type = urlKey('shopType'),           //商品类型
    name = getCookie('name'),        //商品名
    date = getCookie('date'),        //出游日期
    dateArr = date.split('-'),
    bottomTotalPri = $('#bottomTotalPri'),
    KC = getCookie('kc'),
    mess = getCookie('mess'),
    oneIdentify = getCookie('oneIdentify'),
    useDate = getCookie('use'),
    dataJson = {
        0: '周一',
        1: '周二',
        2: '周三',
        3: '周四',
        4: '周五',
        5: '周六',
        6: '周日'
    }

if (useDate) {
    useDate = JSON.parse(useDate);
    timeHtml = useDate.limit;
    $('#tips').show();
    for (var i = 0; i < 7; i++) {
        if (useDate.time[i] == 'true') {
            timeHtml += ' <span> ' + dataJson[i] + ' </span>';
        }
    }
    if (useDate.no) {
        timeHtml += ' <p><span>不可用日期 : </span> ';
        for (var i = 0; i < useDate.no.length; i++) {
            timeHtml += useDate.no[i] + ' ';
        }
        timeHtml += '</p>';
    }
    $('#ticketTime').html(timeHtml);

} else {
    $('#ticketTime').text(date + '(' + getDateWeek(dateArr[0], dateArr[1], dateArr[2]) + ')');
}
$('#onePrice').text(onePrice);
$('#totalPri').text(onePrice);
bottomTotalPri.text(onePrice);

//商品名存半小时cookie，判断是否有商品名
if (name) {
    $('#ticketName').text(name);
} else {
    //没有的话跳转商品详情页面
    location.href = controller + '/p_ticket?shopCode=' + code + '&shopType=' + type;
}

//是否需要填写身份证
if (mess == '1' || mess == '2') {
    $('#oneIdentify').show();
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

        var confirmText = '是否确认' + $('#ticketTime').text() + '出行?';

        if (useDate) {
            confirmText = '此产品为有效期模式,游玩日期'+$('#ticketTime').text()+',确认购买?';
        }

        if (confirm(confirmText)) {
            $('#reserveMess').fadeOut(200, function () {
                $('#tourristMess').fadeIn(200);
            });

            var resPrice = bottomTotalPri.text();
            $('#orderTotalPri').text(resPrice);

            if (mess == '2') {
                var itemBox = $('#secItem'),
                    html = '',
                    ticketNum = +$('#ticketNum').text() - 1;

                for (var i = 0; i < ticketNum; i++) {
                    html += '<div class="item-container"><div class="item-box"><p class="item-p"><span class="red">' + (i + 2) + '.</span>游客姓名 : </p><input class="item-inp name" placeholder="姓名" type="text"></div>' +
                        '<div class="item-box"><p class="item-p">身份证号 : </p><input class="item-inp identify" type="text" placeholder="若是儿童输入出生日期"></div></div>';
                }

                itemBox.html(html);
            }
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

    var tourName = $('#tourName').val();
    if (!tourName) {
        Alert('请填写第1位游客的真实姓名!');
        return false;
    }

    var tourMobile = $('#tourMobile').val();
    if (tourMobile) {
        if (!(/^1\d{10}$/.test(tourMobile))) {
            Alert("第1位游客手机号码有误，请重填");
            return false;
        }
    } else {
        Alert('请填写第1位游客的手机号码!');
        return false;
    }

    var tourIdentify = $('#tourIdentify').val();
    if ($('#oneIdentify').css('display') == 'block') {
        if (tourIdentify) {
            if (!checkIdentity(tourIdentify)) {
                Alert('第1位游客身份证号码错误!');
                return false;
            }
        } else {
            Alert('请输入第1位游客的身份证号码!');
            return false;
        }
    }

    var remarks = $('#remarks').val();

    var data = {};
    data.playerInfo = [];    //空数组怎么发不出去？？

    var len = $('.item-container').length;

    if (len > 0) {
        for (var i = 0; i < len; i++) {
            var itemContainer = $('.item-container').eq(i);
            var nameVal = itemContainer.find('.name').val();
            var idetVal = itemContainer.find('.identify').val();

            if (!nameVal) {
                Alert('请输入第' + (i + 2) + '位游客的姓名!');
                return false;
            }

            if (!idetVal) {
                Alert('请输入第' + (i + 2) + '位游客的身份证或出生日期!');
                return false;
            }

            var json = {
                name: nameVal,
                identify: idetVal
            }

            data.playerInfo.push(json);  //身份数组
        }
    }

    data.code = urlKey('shopCode');
    data.gyscode = getCookie('user');

    data.identification = tourIdentify; //身份证
    data.mobile = tourMobile;           //游客手机
    data.name = tourName;               //第一位游客姓名
    data.jxscode = '';                  //以后的分销商pid
    data.remarks = remarks;             //备注
    data.date = date;                   //出行时间
    data.num = +$('#ticketNum').text(); //数量
    data.totalPrice = $('#orderTotalPri').text();  //总价

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


