require('./index.scss');
var AlloyFinger = require('COMMON/alloy_finger.js');
var datePrice = require('COMMON/date.js');
var Alert = require('COMMON/Alert-mb.js');
var warning = require('COMMON/warning-mb.js');
var toast = require('COMMON/toast.js');
toast.show();
var dateJson = {
    0: '周一',
    1: '周二',
    2: '周三',
    3: '周四',
    4: '周五',
    5: '周六',
    6: '周日'
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

function urlKey(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}

function getDatePrice(m, y, price) {

    var dateArr = datePrice(m, y),
        len = dateArr.length,
        html = '',
        keyNum = 0;

    for (var i = 0; i < len; i++) {
        html += '<tr>';
        for (var j = 0; j < dateArr[i].length; j++) {
            if (dateArr[i][j].num) {
                keyNum++;
                if (price[keyNum] == '') {
                    html += '<td class="disabled"><p>' + dateArr[i][j].num + '</p></td>';
                } else {
                    html += '<td class="js_choose" data-gotime=' + price[keyNum].y_b_time + '><p>' + dateArr[i][j].num + '</p>' +
                        '<p class="date-price">￥<span class="ticket-pri">' + price[keyNum].y_my_price + '</span></p>';

                    if (price[keyNum].y_kc == '-1' || price[keyNum].y_kc == null || price[keyNum].y_kc == '') {
                        html += '<p class="date-kc">余:999</p></td>';
                    } else {
                        html += '<p class="date-kc">余:<span class="kc">' + price[keyNum].y_kc + '</span></p></td>';
                    }
                }
            } else {
                html += '<td></td>';
            }
        }
        html += '</tr>';
    }

    $('#priceDay').html(html);
    priceMonth.text(m);
    priceYear.text(y);
}

function pageRender(data) {

    var img = data.s_img,
        len = img.length,
        html = '';

    for (var i = 0; i < len; i++) {
        html += '<img class="item-pic" src=' + img[i].imgtitle + '>';
    }
    $('#proPic').html(html);

    //轮播图手势切换
    var now = $('#nowNum'),
        all = $('#allNum'),
        nowNum = 1,
        allNum = len,
        picArr = $('#proPic'),
        prevMonthBtn = $('#prevMonthBtn'),
        nextMonthBtn = $('#nextMonthBtn');

    all.text(allNum);

    new AlloyFinger(picArr[0], {

        swipe: function (evt) {

            var dir = evt.direction,
                nowpic = picArr.children().eq(nowNum - 1);

            if (dir == "Left" && nowNum < allNum) {

                nowpic.animate({ left: '-100%' }, 300).next().animate({ left: '0%' }, 300);
                now.text(nowNum += 1);

            } else if (dir == "Right" && nowNum != 1) {

                nowpic.animate({ left: '100%' }, 300).prev().animate({ left: '0%' }, 300);
                now.text(nowNum -= 1);

            }
        },
    })

    //商品ID
    $('#proID').html(data.s_code);
    //产品名字
    $('#proName').text(data.s_goods_name);


    //上个月
    prevMonthBtn.click(function () {

        if (priceNum == 0) {
            $(this).css('background', '#eaeaea');
            return false;
        }

        nextMonthBtn.css('background', '#3385ff');

        month -= 1;

        if (month == 0) {
            month = 12;
            year -= 1;
        };
        getDatePrice(month, year, priceData[priceNum - 1]);

        priceNum--;

        if (priceNum == 0) {
            $(this).css('background', '#eaeaea');
        }
    })

    //下个月
    nextMonthBtn.click(function () {

        if (priceNum == priceLen - 1) {
            return false;
        }

        prevMonthBtn.css('background', '#3385ff');

        month += 1;

        if (month == 13) {
            month = 1;
            year += 1;
        };
        getDatePrice(month, year, priceData[priceNum + 1]);

        priceNum++;

        if (priceNum == priceLen - 1) {
            $(this).css('background', '#eaeaea');
        }
    })


    //费用说明
    var fyHtml = '';

    fyHtml += '<p><span class="title">费用包含 : </span>' + data.s_name + '</p>';

    $('#feiyongContent').html(fyHtml);

    var ydHtml = '';

    ydHtml += '<p><span class="title">间夜 : </span>' + data.s_hotel_day + '晚2天</p>';
    ydHtml += '<p><span class="title">类型 : </span>套餐适用人数' + data.s_man_num + '成人' + data.s_child_num + '儿童</p>';
    ydHtml += '<p><span class="title">提前预定时间 : </span>';

    data.s_yd_time == 0 ? ydHtml += '当天的' + data.s_hotel_yd_time + '分(中国时区)前可预订' : ydHtml += '提前' + data.s_yd_time + '天' + data.s_hotel_yd_time + '分(中国时区)前可预订';

    ydHtml += '<p><span class="title">订单确认时间 : </span>' + data.s_hotel_sure_time + '</p>';

    ydHtml += '<p><span class="title">预订份数限制 : </span>最小购买份数' + data.s_hotel_buy_m_num;

    data.s_hotel_buy_b_num ? ydHtml += ' 最大购买份数' + data.s_hotel_buy_b_num + '</p>' : ydHtml += '</p>';

    if (data.s_yq_ts) {
        ydHtml += '<p><span class="title">友情提示 : </span>' + data.s_yq_ts + '</p>';
    }

    if(data.s_use_info){
        ydHtml += '<p><span class="title">使用说明 : </span>' + data.s_use_info + '</p>';
    }

    $('#yudingContent').html(ydHtml);
}

var nowDate = new Date(),
    year = nowDate.getFullYear(),
    month = nowDate.getMonth() + 1,
    priceMonth = $('#priceMonth'),
    priceYear = $('#priceYear'),
    priceData,
    priceLen,
    priceNum = 0,
    name,
    userId,
    sCode;

var type = urlKey('shopType'),
    code = urlKey('shopCode'),
    URL = 'http://www.suiyiyou.net/index.php/Weixin/Detail/detail';
// URL = 'http://localhost/tour/index.php/Weixin/Detail/detail';

$.ajax({
    url: URL,
    data: {
        shopType: type,
        shopCode: code
    },
    success: function (res) {
        toast.hide();
        if (res.code == 200) {

            var data = res.data;
            name = data.s_name;
            sCode = data.s_code;
            userId = data.s_user_id;
            priceData = res.data.date;
            priceLen = priceData.length;
            getDatePrice(month, year, priceData[0]);  //价格日历
            pageRender(data);

        } else {
            warning('您查看的产品已下架!', function () {
                location.href = controller + '/home';
            });
        }
    }
})


//价格日历选择
var priceBox = $('#proPrice'),
    goTime = $('#goTime');

$('#priceDay').on('click', '.js_choose', function () {
    $('.js_choose').removeClass('on');
    $(this).addClass('on');

    var price = $(this).find('.ticket-pri').text();
    priceBox.text(price);

    var gotime = $(this).attr('data-gotime');
    goTime.text(gotime);

})


var DOM = $('#contentList'),
    TOP = DOM.offset().top,
    allDocument = $(document),
    rili = $('#riliNav'),
    feiyong = $('#feiyongNav'),
    yuding = $('#yudingNav'),
    down, up;

allDocument.scroll(function () {

    var top = allDocument.scrollTop();
    if (top >= TOP + 40) {
        DOM.css({
            position: 'fixed',
            backgroundColor: 'rgba(51, 133, 255,0.8)',
            color: '#fff'
        });
    } else {
        DOM.css({
            position: 'relative',
            backgroundColor: '#fff',
            color: '#3385ff'
        });
    }
})

$('.list-item').click(function () {

    var _this = $(this);
    var scrollTop = document.documentElement.scrollTop || window.pageYOffset || document.body.scrollTop;
    var index = _this.index();

    scrollFn(index, scrollTop, $(this));

})

//滚动导航
function scrollFn(index, scrollTop, _this) {

    clearInterval(down);
    clearInterval(up);

    var json = {
        0: rili.offset().top - 40,
        1: feiyong.offset().top - 40,
        2: yuding.offset().top - 40
    }

    if (json[index] > scrollTop) {
        var down = setInterval(function () {
            scrollTop += 15;
            window.scrollTo(0, scrollTop);
            if (scrollTop >= json[index]) {
                clearInterval(down);
            }
        }, 10)
    } else {
        var up = setInterval(function () {
            scrollTop -= 15;
            window.scrollTo(0, scrollTop);
            if (scrollTop <= json[index]) {
                clearInterval(up);
            }
        }, 10)
    }
}

//立即购买
$('#nowBuyBtn').click(function () {
    loginConfirm();
})

function loginConfirm() {
    if ($('#proPrice').text() == '--') {
        Alert('请先从价格日历选择日期');
        return false;
    }
    toast.show();
    $.ajax({
        url: 'http://www.suiyiyou.net/index.php/Weixin/BaseLogin/logined',
        // url: 'http://localhost/tour/index.php/Weixin/BaseLogin/logined',
        type: 'POST',
        success: function (res) {
            var code = res.code;
            // if (code == 200) {

                code = urlKey('shopCode');
                type = urlKey('shopType');
                var chooseOn = $('.js_choose.on'),
                    kc = chooseOn.find('.kc').text();

                setCookie('price', $('#proPrice').text(), 30 * 60 * 1000);  //价格
                setCookie('date', $('#goTime').text(), 30 * 60 * 1000);
                setCookie('user', userId, 30 * 60 * 1000);
                setCookie('kc', kc, 30 * 60 * 1000);
                setCookie('name', name, 30 * 60 * 1000);    //产品名字
                setCookie('sCode', sCode, 30 * 60 * 1000);  //套餐编码
                HREF = controller + '/d_hotel?shopCode=' + code + '&shopType=' + type;
                toast.hide();
                location.href = HREF;

            // } else {
                // location.href = controller + '/login';     //未登录跳转登陆
            // }
        }
    })
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
        desc: $('#proName').text(),
        link: link,
        imgUrl: public + '/Weixin/image/wxbg.png',
    });
})


