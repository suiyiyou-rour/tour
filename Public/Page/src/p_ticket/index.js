require('./index.scss');
var datePrice = require('COMMON/date.js');
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

                    if (price[keyNum].p_kc == '-1' || price[keyNum].p_kc == null) {
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

    picShow.css('background', 'url(' + data.t_tick_file[0].src + ') center no-repeat');

    for (var i = 0; i < len; i++) {
        html += '<li class="pic-item"><img class="item-pic" src=' + img[i].src + '></li>';
    }
    $('#picList').html(html);

    //销量
    $('#sales').html('销量: ' + data.t_tick_sell);

    var prevMonthBtn = $('#prevMonthBtn'),
        nextMonthBtn = $('#nextMonthBtn');

    //商品品类
    $('#proCity').html(data.t_tick_city);
    //商品ID
    $('#proID').html(data.t_code);
    //产品名字
    $('#proName').text(data.t_tick_name + '+' + data.t_tick_cat + '+' + data.t_tick_spot);


    //上个月
    prevMonthBtn.click(function (e) {

        if (priceNum == 0) {
            $(this).css('background', '#cecece');
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
            $(this).css('background', '#cecece');
        }
    }).bind("selectstart", function () { return false; });

    //下个月
    nextMonthBtn.click(function (e) {

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
            $(this).css('background', '#cecece');
        }
    }).bind("selectstart", function () { return false; });


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

var proPrice = $('#proPrice'),
    NUM = $('#num'),
    PRICE,    //价格
    KC,       //库存
    numVal = 1;   //数量
//增加
$('#chooseNum').on('click', '.add', function () {
    
    var price = proPrice.text();

    if(price == '--'){
        alert('请先选择出游日期');
    }else{
        var num = $(this).parents('.amount').find('.num');
        numVal = +num.text();

        if(numVal == KC){
            Alert('已达到库存上限');
        }

        numVal += 1;
        num.text(numVal);

        price = +PRICE * 100;
        proPrice.text(numVal * price / 100);
    }

}).bind("selectstart", function () { return false; });

$('#chooseNum').on('click', '.red', function () {
    
    var price = proPrice.text();

    if(price == '--'){
        alert('请先选择出游日期');
    }else{
        var num = $(this).parents('.amount').find('.num');
        numVal = +num.text();
        
        if(numVal == 1){
            return false;
        }

        numVal -= 1;
        num.text(numVal);

        price = +PRICE * 100;
        proPrice.text(numVal * price / 100);
    }

}).bind("selectstart", function () { return false; });

var nowDate = new Date(),
    year = nowDate.getFullYear(),
    month = nowDate.getMonth() + 1,
    priceMonth = $('#priceMonth'),
    priceYear = $('#priceYear'),
    picShow = $('#picShow'),
    priceData,
    priceLen,
    priceNum = 0,
    name,
    userId,
    mess,     // =2 需要全部游客信息
    oneIdentify,
    useDate;   //是否是有效期模式

var type = urlKey('type'),
    code = urlKey('code'),
    URL = 'http://www.suiyiyou.net/index.php/Weixin/Detail/detail';

$.ajax({
    url: URL,
    data: {
        type: type,
        code: code
    },
    success: function (res) {

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
    }
})


$('#picList').on('mouseover', '.pic-item', function () {
    var src = $(this).find('.item-pic').attr('src');
    picShow.css('background', 'url(' + src + ') center no-repeat');
})


//价格日历选择
var priceBox = $('#proPrice'),
    goTime = $('#goTime');

$('#priceDay').on('click', '.js_choose', function () {
    $('.js_choose').removeClass('on');
    $(this).addClass('on');

    var price = $(this).find('.ticket-pri').text();
    priceBox.text(price);
    PRICE = +price;
    proPrice.text(PRICE * +NUM.text());

    KC = +$(this).find('.date-kc').text();

    var gotime = $(this).attr('data-gotime');
    goTime.text(gotime);

})


//立即购买
$('#nowBuy').click(function () {
    loginConfirm();
})

function loginConfirm() {
    if ($('#proPrice').text() == '--') {
        alert('请先从价格日历选择日期');
        return false;
    } else {
        code = urlKey('code');
        type = urlKey('type');

        setCookie('price', $('#proPrice').text(), 30 * 60 * 1000);  //价格
        if (useDate) {
            setCookie('use', JSON.stringify(useDate), 30 * 60 * 1000);     //有效期模式
        }
        setCookie('date', $('#goTime').text(), 30 * 60 * 1000);    //出行时间
        setCookie('user', userId, 30 * 60 * 1000);                  
        setCookie('mess', mess, 30 * 60 * 1000);                    //是否需要身份证
        setCookie('num', numVal, 30 * 60 * 1000);
        setCookie('pic', $('.item-pic').eq(0).attr('src'), 30 * 60 * 1000);  //图片首图
        HREF = controller + '/d_ticket?code=' + code + '&type=' + type;

        location.href = HREF;
        setCookie('name', name, 30 * 60 * 1000);  //产品名字
    }
    // $.ajax({
    //     url: 'http://www.suiyiyou.net/index.php/Weixin/BaseLogin/logined',
    //     type: 'POST',
    //     success: function (res) {
    //         var code = res.code;
    //         // if (code == 200) {

    //         code = urlKey('code');
    //         type = urlKey('type');
    //         var chooseOn = $('.js_choose.on'),
    //             kc = chooseOn.find('.kc').text();

    //         setCookie('price', $('#proPrice').text(), 30 * 60 * 1000);  //价格
    //         if (useDate) {
    //             setCookie('use', JSON.stringify(useDate), 30 * 60 * 1000);     //有效期模式
    //         }
    //         setCookie('date', $('#goTime').text(), 30 * 60 * 1000);
    //         setCookie('user', userId, 30 * 60 * 1000);
    //         setCookie('mess', mess, 30 * 60 * 1000);
    //         HREF = controller + '/d_ticket?code=' + code + '&type=' + type;

    //         location.href = HREF;
    //         setCookie('name', name, 30 * 60 * 1000);  //产品名字

    //         // } else {
    //         // location.href = controller + '/register';     //未注册跳转注册
    //         // }
    //     }
    // })
}


