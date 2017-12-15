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
                    html += '<td class="js_choose" data-gotime=' + price[keyNum].p_date + '><p>' + dateArr[i][j].num + '</p>' +
                        '<p class="date-price">￥<span class="ticket-pri">' + price[keyNum].p_my_price + '</span></p>';

                    if (price[keyNum].p_ck == '-1' || price[keyNum].p_ck == null || price[keyNum].p_ck == '') {
                        html += '<p class="date-kc">余:999</p></td>';
                    } else {
                        html += '<p class="date-kc">余:<span class="kc">' + price[keyNum].p_ck + '</span></p></td>';
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

    var img = data.t_tick_file,
        len = img.length,
        html = '';

    for (var i = 0; i < len; i++) {
        html += '<img class="item-pic" src=' + img[i].src + '>';
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

    //商品品类
    $('#proCity').html(data.t_tick_city);
    //商品ID
    $('#proID').html(data.t_code);
    //产品名字
    $('#proName').text(data.t_tick_name + '+' + data.t_tick_cat + '+' + data.t_tick_spot);


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
    var fyHtml = '<p class="main-title">费用包含<p>';
    for (var i = 0; i < data.t_tick_cost.length; i++) {

        fyHtml += '<p><span class="费用包含"></span>' +
            '<span class="title">' + data.t_tick_cost[i].name + ' : </span><span>' + data.t_tick_cost[i].num + data.t_tick_cost[i].danwei + ' , 单次时长' + data.t_tick_cost[i].duration + '</span>';

    }
    fyHtml += '</p>';

    if (data.t_another_cost) {
        fyHtml += '<p><span class="title">费用包含补充说明 : </span><span>' + data.t_another_cost + '</span></p>';
    }

    if (data.t_tick_insurance != '0') {
        fyHtml += '<p><span class="title">保险 : </span><span>包含</span></p>';
    }

    fyHtml += '<p class="main-title">费用不包含<p>';

    if (data.t_tick_no_contain) {
        fyHtml += '<p><span class="title">费用不包含 : </span><span>' + data.t_tick_no_contain + '</span></p>';
    }

    if (data.t_tick_insurance == '0') {
        fyHtml += '<p><span class="title">保险 : </span><span>不包含</span></p>';
    }

    $('#feiyongContent').html(fyHtml);

    var ydHtml = '';

    if (data.t_tick_mobile == '1') {
        ydHtml += '<p><span class="title">联系人信息 : </span>需要</p>';
    }

    if (data.t_tick_playerInfo.val == '1') {
        ydHtml += '<p><span class="title">游玩人信息 : </span>只需要1位游玩人信息</p>';
    } else if (data.t_tick_playerInfo.val == '2') {
        ydHtml += '<p><span class="title">游玩人信息 : </span>需要全部游玩人信息</p>';
    }

    if (data.t_tick_pre_book_time.val == '1') {
        ydHtml += '<p><span class="title">提前购买时间 : </span><span>用户需提前' + data.t_tick_pre_book_time.two.hour.val + '小时' + data.t_tick_pre_book_time.two.minute.val + '分预定</span></p>';
    } else if (data.t_tick_pre_book_time.val == '2') {
        ydHtml += '<p><span class="title">提前购买时间 : </span><span>用户需在游玩' + data.t_tick_pre_book_time.three.day.val + data.t_tick_pre_book_time.three.hour.val + '点' + data.t_tick_pre_book_time.three.minute + '分预定</span></p>'
    }

    if (data.t_yd_num) {
        ydHtml += '<p><span class="title">预定数量限制 : </span>每笔订单最少购买' + data.t_yd_num.min + '张';

        if (data.t_yd_num.max) {
            ydHtml += ',最大购买' + data.t_yd_num.max + '张</p>';
        } else {
            ydHtml += '</p>';
        }
    }

    if (data.t_tick_identity.maxNum) {
        ydHtml += '<p><span class="title">身份证限制 : </span>同一身份证在' + minDay + '天内最多购买' + maxNum + '张</p>';
    }

    if (data.t_go_b_time[0].fromH) {
        ydHtml += '<p><span class="title">入园时间 : </span>' + data.t_go_b_time[0].fromH + ':' + data.t_go_b_time[0].fromM + '~' + data.t_go_b_time[0].toH + ':' + data.t_go_b_time[0].toM;
        if (data.t_go_b_time[0].bcInfo.val) {
            ydHtml += ' <span> ' + data.t_go_b_time[0].bcInfo.val + '</span>';
        } else {
            ydHtml += '</p>';
        }
    }

    if (data.t_tick_use_address) {
        ydHtml += '<p><span class="title">入园地址 : </span>';

        for (var i = 0; i < data.t_tick_use_address.length; i++) {
            ydHtml += '<span>' + data.t_tick_use_address[i].val + ' </span>';
        }

        ydHtml += '</p>';
    }

    if (data.t_tick_date == '1') {
        ydHtml += '<p><span class="title">有效期模式 : </span>此门票无需用户指定使用日期</p>';

        ydHtml += '<p><span class="title">有效期 : </span><span>' + data.t_tick_verify_time + '~' + data.t_tick_xj_time + '</span></p>';
    }

    if (data.use_time) {

        ydHtml += '<p><span class="title">可用日期 :</span>';
        for (var i = 0; i < data.use_time.length; i++) {
            if (data.use_time[i] == 'true') {
                ydHtml += '<span> ' + dateJson[i] + ' </span>';
            }
        }
    }

    if (data.no_use_time) {
        ydHtml += '<p><span class="title">不可用日期 :</span>';
        for (var i = 0; i < data.no_use_time.length; i++) {
            ydHtml += '<span> ' + data.no_use_time[i] + ' </span>';
        }
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
    mess,     // =2 需要全部游客信息
    oneIdentify,
    useDate;   //是否是有效期模式

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
            name = data.t_tick_name + '+' + data.t_tick_cat + '+' + data.t_tick_spot;
            userId = data.t_user_id;
            priceData = res.data.date;
            priceLen = priceData.length;
            mess = data.t_tick_playerInfo.val; //其他游客身份数组
            oneIdentify = data.t_tick_mobile;  //需要一个身份信息1 不需要0
            if (data.t_tick_date == '1') {       //如果是有效期模式
                useDate = {
                    use: data.t_tick_date,
                    limit: data.t_tick_verify_time + '~' + data.t_tick_xj_time,
                    time: data.use_time
                };
                if (data.no_use_time) {
                    useDate.no = data.no_use_time
                }
            }
            getDatePrice(month, year, priceData[0]);  //价格日历
            pageRender(data);

        } else {
            warning('您查看的产品已下架!', function () {
                location.href = controller + '/home';
            });
        }
    }
})

//价格日历手势图切换
new AlloyFinger(document.getElementById('datePriceTable'), {

    swipe: function (evt) {

        var dir = evt.direction;

        if (dir == "Left") {

            nextMonthBtn.click();

        } else if (dir == "Right") {

            prevMonthBtn.click();

        }
    },
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
            if (code == 200) {

                code = urlKey('shopCode');
                type = urlKey('shopType');
                var chooseOn = $('.js_choose.on'),
                    kc = chooseOn.find('.kc').text();

                setCookie('price', $('#proPrice').text(), 30 * 60 * 1000);  //价格
                if (useDate) {
                    setCookie('use', JSON.stringify(useDate), 30 * 60 * 1000);     //有效期模式
                }
                setCookie('date', $('#goTime').text(), 30 * 60 * 1000);
                setCookie('user', userId, 30 * 60 * 1000);
                setCookie('kc', kc, 30 * 60 * 1000);
                setCookie('mess', mess, 30 * 60 * 1000);
                setCookie('name', name, 30 * 60 * 1000);  //产品名字
                HREF = controller + '/d_ticket?shopCode=' + code + '&shopType=' + type;
                toast.hide();
                location.href = HREF;

            } else {
                location.href = controller + '/login';     //未登录跳转登陆
            }
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


