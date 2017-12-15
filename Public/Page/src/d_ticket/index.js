require('./index.scss');

$('.item').click(function () {

    var _this = $(this);
    _this.addClass('on').find('.item-inp').focus();;

})

$('.item-inp').blur(function () {
    if (!$(this).val()) {
        $(this).parents('.item').removeClass('on');
    }
})

//读URL参数
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
var PRICE = getCookie('price'),      //价格
    code = urlKey('code'),           //商品id
    type = urlKey('type'),           //商品类型
    name = getCookie('name'),        //商品名
    date = getCookie('date'),        //出游日期
    dateArr = date.split('-'),
    KC = getCookie('kc'),            //库存
    mess = getCookie('mess'),        //是否需要身份证
    useDate = getCookie('use'),
    NUM = getCookie('num'),          //数量
    dataJson = {
        0: '周一',
        1: '周二',
        2: '周三',
        3: '周四',
        4: '周五',
        5: '周六',
        6: '周日'
    }

$('#totalPrice').text(PRICE);
$('#numDate').text('x' + NUM);

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
    $('#date').html(timeHtml);

} else {
    $('#date').text(date + '(' + getDateWeek(dateArr[0], dateArr[1], dateArr[2]) + ')');
}


//商品名存半小时cookie，判断是否有商品名
if (name) {
    $('#proName').text(name);
} else {
    //没有的话跳转商品详情页面
    location.href = controller + '/p_ticket?code=' + code + '&type=' + type;
}

$('#proPic').prop('src',getCookie('pic'));

//是否需要填写身份证
if (mess == '1' || mess == '2') {
    $('#oneIdentify').show();
    
    if (mess == 2) {
        $('#messContentTitle').show();
        var messContent = '';
        for (var i = 0; i < NUM - 1; i++) {
            messContent += '<div class="one-person">' +
                '<h3 class="number">游客<span class="num">' + (i + 1) + '</span></h3>' +
                '<div class="item"><input class="item-inp name" type="text"  placeholder="请输入真实姓名">' +
                '</div><div class="item"><input class="item-inp identify" type="text"  placeholder="身份证/儿童输入出生日期"></div></div>';
        }
        $('#travelerMess').html(messContent);
    }
}

//去填写游客信息
$('#goTourBtn').click(function () {

    if ($('#reserveCheck').prop('checked')) {

        var confirmText = '是否确认' + $('#ticketTime').text() + '出行?';

        if (useDate) {
            confirmText = '此产品为有效期模式,游玩日期' + $('#ticketTime').text() + ',确认购买?';
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

//确认下单
$('#commitOrder').click(function () {

    var tourName = $('#name').val();
    if (!tourName) {
        alert('请输入您的真实姓名!');
        return false;
    }

    var tourMobile = $('#mobile').val();
    if (tourMobile) {
        if (!(/^1\d{10}$/.test(tourMobile))) {
            alert("联系手机号码有误，请重填");
            return false;
        }
    } else {
        alert('请输入联系的手机号码!');
        return false;
    }

    var tourIdentify = $('#identify').val();
    if ($('#oneIdentify').css('display') == 'block') {
        if (tourIdentify) {
            if (!checkIdentity(tourIdentify)) {
                alert('游客身份证号码错误!');
                return false;
            }
        } else {
            alert('请输入身份证号码!');
            return false;
        }
    }

    var remarks = $('#marks').val();

    var data = {};
    data.playerInfo = [];    //空数组怎么发不出去？？

    var len = $('.one-person').length;

    if (len > 0) {
        for (var i = 0; i < len; i++) {
            var itemContainer = $('.one-person').eq(i);
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

    if(!$('#agreement').prop('checked')){
        alert('请先阅读并同意《随意游服务使用协议》');
    }

    data.code = urlKey('code');
    data.gyscode = getCookie('user');

    data.identification = tourIdentify; //身份证
    data.mobile = tourMobile;           //游客手机
    data.name = tourName;               //第一位游客姓名
    data.jxscode = '';                  //以后的分销商pid
    data.remarks = remarks;             //备注
    data.date = date;                   //出行时间
    data.num = +NUM; //数量
    data.totalPrice = $('#totalPrice').text();  //总价

    var _this = $(this);

    _this.prop('disabled', true);
    _this.css('backgroundColor', '#ffa69c');

    $.ajax({
        url: 'http://www.suiyiyou.net/index.php/weixin/Order/addTickOrder',
        data: data,
        type: 'POST',
        success: function (res) {
          
            var code = res.code,
                data = res.data;
            if (code == 200) {
                location.href = 'http://www.suiyiyou.net/index.php/Weixin/Jsapi/index?orderSn=' + data.orderSn + '&type=' + type + '&';
            } else {
                Alert(res.msg);
                _this.prop('disabled', false);
                _this.css('backgroundColor', '#ff1900');
            }
        }
    })
})


