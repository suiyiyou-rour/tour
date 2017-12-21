require('./index.scss');
var AlloyFinger = require('COMMON/alloy_finger.js');
var datePrice = require('COMMON/date.js');
var Alert = require('COMMON/Alert-mb.js');
var warning = require('COMMON/warning-mb.js');
var toast = require('COMMON/toast.js');
toast.show();

function urlKey(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}

function setCookie(name, value, time) {
    var oDate = new Date();
    oDate.setTime(oDate.getTime() + time);
    document.cookie = name + '=' + encodeURIComponent(value) + ';expires=' + oDate.toUTCString();
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
                    html += '<td data-dfc="' + price[keyNum].g_df_plat + '" data-child="' + price[keyNum].g_child_my_price + '" class="js_choose" data-gotime=' + price[keyNum].g_go_time + '><p>' + dateArr[i][j].num + '</p>' +
                        '<p class="date-price">￥<span class="ticket-pri">' + price[keyNum].g_man_my_price + '</span></p>' +
                        '<p class="date-kc">余:<span class="kc">' + (+price[keyNum].g_need_kc_num + +price[keyNum].g_no_kc_num) + '</span></p></td>';
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

//价格日历选择
var priceBox = $('#proPrice'),
    goTime = $('#goTime'),
    childPrice;       //儿童价

$('#priceDay').on('click', '.js_choose', function () {
    $('.js_choose').removeClass('on');
    $(this).addClass('on');

    var price = $(this).find('.ticket-pri').text();
    priceBox.text(price);

    var gotime = $(this).attr('data-gotime');
    goTime.text(gotime);

    childPrice = $(this).attr('data-child');

})


var type = urlKey('shopType'),
    code = urlKey('shopCode'),
    URL = 'http://www.suiyiyou.net/index.php/Weixin/Detail/detail';
// URL = 'http://localhost/tour/index.php/Weixin/Detail/detail';

var priceData,
    priceLen,
    priceNum = 0,
    name,
    userId,
    zfInfo,     //自费项目信息
    childZC;    //儿童是否占床位

$.ajax({
    url: URL,
    data: {
        shopType: type,
        shopCode: code
    },
    success: function (res) {
        toast.hide();          //加载动画隐藏
        if (res.code == 200) {
            var data = res.data;
            name = data.g_name;
            userId = data.g_user_code;
            priceData = res.data.date;
            priceLen = priceData.length;
            zfInfo = data.g_zf_info;
            if (data.g_child_all_info[0].zc == 'true' || data.g_child_all_info[1].zc == 'true') {
                childZC = 1;
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

//页面上各种信息渲染
function pageRender(data) {

    var img = data.g_file,
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

    $('#beginPlace').text(data.g_go_address);  //出发地
    $('#proID').text(data.g_code);             //产品ID
    $('#proName').text(data.g_name + data.g_m_tittle + data.g_n_code);  //商品标题

    //产品描述
    var ms = data.g_routing;
    if (ms) {

        var msLen = ms.length,
            msHtml = '';

        for (var i = 0; i < msLen; i++) {

            msHtml += '<p class="travel-day">第 ' + (i + 1) + ' 天<p>' +
                '<p><span class="title">出发地</span> : ' + ms[i].startplace + '</p>' +
                '<p><span class="title">住宿</span> : ' + ms[i].live + '</p>';

            if (data.g_stay != '1') {
                ms[i].food.breakfast.bool == 'false' ? msHtml += '<p><span class="title">用餐</span> : 不含早餐 ' : msHtml += '<p><span class="title">用餐</span> : 含早餐 ';
                ms[i].food.lunch.bool == 'false' ? msHtml += ' 不含午餐 ' : msHtml += ' 含午餐 ';
                ms[i].food.dinner.bool == 'false' ? msHtml += ' 不含晚餐 </p>' : msHtml += ' 含晚餐 </p>';
            }

            msHtml += '<p><span class="title">途径线路</span> : 坐' + ms[i].way.trans;

            if (ms[i].way.city) {
                msHtml += '到' + ms[i].way.city + '</p>';
            } else {
                msHtml += '</p>';
            }

            for (var j = 0; j < ms[i].routeDetail.length; j++) {

                msHtml += '<p><span class="title">行程时间</span> : ' + ms[i].routeDetail[j].time + '</p>';
                msHtml += '<p><span class="title">包含景点</span> : ';

                if (ms[i].routeDetail[j].spotArr) {
                    var spotLen = ms[i].routeDetail[j].spotArr.length;
                    for (var k = 0; k < spotLen; k++) {
                        msHtml += ms[i].routeDetail[j].spotArr[k] + ', ';
                    }
                }

                msHtml += '<p><span class="title">行程</span> : ' + ms[i].routeDetail[j].describe + '</p>';
            }
        }

        $('#miaoshuContent').html(msHtml);
    }

    //产品描述结束


    //费用说明
    var fyHtml = '<p class="main-title">退款说明 : </p>';

    if (data.g_batch == '1') {
        fyHtml += '<p><span class="title">支持部分退款</span> : 可以选择退款人数.订单会按照消费者填写的人数自动完成退款</p>' +
            '<p><span class="title">阶梯退款 : </span>';
        for (var i = 0; i < data.g_ladder_refund.length; i++) {
            fyHtml += '<span>行程开始前 ' + data.g_ladder_refund[i].fromday + '天' + data.g_ladder_refund[i].fromhour + ' 点(不含)至前 ' + data.g_ladder_refund[i].today + '天 ' + data.g_ladder_refund[i].tohour + '点(含)</span>';
            if (data.g_ladder_refund[i].wymoney.val == 'true') {
                fyHtml += '<p><span class="title">违约金 : </span><span>旅游费用总额百分比 ' + data.g_ladder_refund[i].wymoney.num + '%</span></p>';
            }
        }
        fyHtml += '</p>';

    } else {
        fyHtml += '<p>只能够整单退款 : 仅支持全额退款</p>';
    }

    fyHtml += '<p class="main-title">费用包含 : </p>';
    if (data.g_l_tran == 1) { fyHtml += '<p><span class="title">小交通</span> : 景区内用车费用</p>'; };

    if (data.g_play_spot.length > 0) {
        fyHtml += '<p><span class="title">门票</span> : ';
        for (var i = 0; i < data.g_play_spot.length; i++) {
            fyHtml += '<span>' + data.g_play_spot[i].name + '门票</span> ';
        }
    }
    fyHtml += '</p>';

    if (data.g_tour_guider != '1') {

        fyHtml += '<p><span class="title">导游服务</span> : ';
        if (data.g_tour_guider == '2') {
            fyHtml += '<span>中文导游</span></p>';
        } else if (data.g_tour_guider == '3') {
            fyHtml += '<span>全程陪同中文导游</span></p>';
        } else if (data.g_tour_guider == '4') {
            fyHtml += '<span>全程和当地中文导游</span></p>';
        }

    }

    if (data.g_bx == 'false') {
        fyHtml += '<p><span class="title">保险</span> : 含旅游意外险</p>';
    }

    if (data.g_child_all_info[0].age2 || data.g_child_all_info[1].h2) {

        var childIndex = 0;

        if (data.g_child_all_info[0].age2) {
            fyHtml += '<p><span class="title">儿童价说明</span> : ' +
                '<p>年龄' + data.g_child_all_info[0].age1 + '至' + data.g_child_all_info[0].age2 + '周岁(含)';
            childIndex = 0;
        }

        if (data.g_child_all_info[1].h2) {
            fyHtml += '<p><span class="title">儿童价说明</span> : ' +
                '<p>年龄' + data.g_child_all_info[1].h1 + '至' + data.g_child_all_info[1].h2 + '厘米(含)';
            childIndex = 1;
        }

        if (data.g_child_all_info[childIndex].zc == 'true') { fyHtml += ',占床'; };
        if (data.g_child_all_info[childIndex].djt == 'true') { fyHtml += ',大交通费用'; };
        if (data.g_child_all_info[childIndex].car == 'false') { fyHtml += ',当地旅游车位'; };
        if (data.g_child_all_info[childIndex].tick == 'true') { fyHtml += ',门票'; };
        if (data.g_child_all_info[childIndex].guider == 'true') { fyHtml += ',导游服务'; };
        if (data.g_child_all_info[childIndex].dinner == 'true') { fyHtml += ',正餐'; };
        if (data.g_child_all_info[childIndex].halfpricedinner == 'true') { fyHtml += ',半价餐'; };

        fyHtml += '</p>';
    }

    fyHtml += '<p class="main-title">费用不包含 : </p>';
    if (data.g_bkkl == 'true') {
        fyHtml += '<p><span class="title">支持部分退款</span> : 因交通延阻、罢工、天气、飞机、机器故障、航班取消或更改时间等不可抗力原因所导致的额外费用</p>';
    }

    fyHtml += '<div><span class="title">小交通</span> : ';
    for (var i = 0; i < data.g_littl_tran.length; i++) {
        if (i == 0) {
            if (data.g_littl_tran[0] == 'true') {
                fyHtml += '<p> 景区内用车费用 </p>';
            }
        }
        if (i == 1) {
            if (data.g_littl_tran[1] == 'true') {
                fyHtml += '<p> 不含客人所在地到出发地的往返交通费用 </p>';
            }
        }
    }
    fyHtml += '</div>';

    fyHtml += '<div><span class="title">单房差</span> : ';
    for (var i = 0; i < data.g_dfc.length; i++) {
        if (i == 0) {
            if (data.g_dfc[0] == 'true') {
                fyHtml += '<p> 不含单房差 </p>';
            }
        }
        if (i == 1) {
            if (data.g_dfc[1] == 'true') {
                fyHtml += '<p> 不含升级舱位、升级酒店、升级房型等产品的差价 </p>';
            }
        }
    }
    fyHtml += '</div>';

    fyHtml += '<div><span class="title">门票</span> : ';
    for (var i = 0; i < data.g_no_tick.length; i++) {
        if (i == 0) {
            if (data.g_no_tick[0] == 'true') {
                fyHtml += '<p> 不含景点内的园中园门票 </p>';
            }
        }
        if (i == 1) {
            if (data.g_no_tick[1] == 'true') {
                fyHtml += '<p> 不含“费用包含”中体现的其他项目 </p>';
            }
        }
    }
    fyHtml += '</div>';

    fyHtml += '<div><span class="title">补充</span> : ';
    for (var i = 0; i < data.g_no_bc.length; i++) {
        if (i == 0) {
            if (data.g_no_bc[0] == 'true') {
                fyHtml += '<p> 不含酒店内洗衣、理发、电话、传真、收费电视、饮品、烟酒等个人消费 </p>';
            }
        }
        if (i == 1) {
            if (data.g_no_bc[1] == 'true') {
                fyHtml += '<p> 不含当地参加的自费以及“费用包含”中的其他项目 </p>';
            }
        }
    }
    fyHtml += '</div>';

    if (data.g_no_qt) {
        fyHtml += '<div><span class="title">其他</span> : ' + data.g_no_qt + '</div>';
    }

    $('#feiyongContent').html(fyHtml);
    //费用说明结束

    //预定须知
    var ydHtml = '<p class="main-title">特殊人群限制 : </p>';

    if (data.g_ts_man[0] == "true") {
        ydHtml += "<p>患有疾病的客人，参与此行程请根据自身条件，请遵医嘱，安全第一，谨慎出行</p>";
    }

    if (data.g_ts_man[1].val == "true") {
        ydHtml += '<p>此行程不接受出游年龄超过' + data.g_ts_man[1].moreage + '周岁（含）的</p>';
    }

    if (data.g_ts_man[2].val == "true") {
        ydHtml += '<p>此行程不接受出游年龄低于' + data.g_ts_man[2].lessage + '周岁（含）的</p>';
    }

    if (data.g_ts_man[3].val == "true") {
        ydHtml += '<p>出游年龄超过' + data.g_ts_man[3].age + '周岁（含）的客人，需要签署健康协议</p>';
    }

    if (data.g_ts_man[4].val == "true") {
        ydHtml += '<p>出游年龄超过' + data.g_ts_man[4].age + '周岁（含）的客人，需要咨询确认</p>';
    }

    if (data.g_ts_man[5].val == "true") {
        ydHtml += '<p>出游年龄低于' + data.g_ts_man[5].age + '周岁（含）的客人，需要咨询确认</p>';
    }

    if (data.g_ts_man[6] == "true") {
        ydHtml += '<p>不接受外籍游客和港澳台同胞</p>';
    }


    ydHtml += '<p class="main-title">预定须知 : </p>';

    if (data.g_team_food.val == "true") {
        ydHtml += '<p><span class="title">团队用餐</span>' + data.g_team_food.menNum + '人一桌，' + data.g_team_food.caiNum + '菜一汤，人数不足一人，在每人用餐标准不变的前提下调整餐食的分量</p>';
    }

    if (data.g_cj_info == '2') {
        ydHtml += '<p><span class="title">差价说明 : </span>本行程的景点门票为旅行社折扣价，持优待证件（如学生证）产生折扣退费的，按实际差额（实际差额=退费项目旅行社折扣价-优待优惠价）由旅行社退还。</p>';
    }

    if (data.g_cj_info == '3') {
        ydHtml += '<p><span class="title">差价说明 : </span>本行程的景点门票为团队优惠价格，持任何优待证件（如学生证）均无法再次享受景区门票的优惠政策。敬请谅解！</p>';
    }

    ydHtml += '<p><span>购物 : </span>当地购物时请慎重考虑，把握好质量与价格，务必索要发票</p>';

    ydHtml += '<p><span class="title">不成团约定 : </span></p><p>如不能成团，旅行社将按照以下方式与旅游者协商解决：</p>';

    if (data.g_no_team[0] == "true") {
        ydHtml += '<p>延期出团</p>';
    }

    if (data.g_no_team[1] == "true") {
        ydHtml += '<p>改签其他线路出团</p>';
    }

    if (data.g_no_team[2] == "true") {
        ydHtml += '<p>解除合同</p>';
    }

    if (data.g_wx_info) {
        ydHtml += '<p><span class="title">温馨提示 : </span>' + data.g_wx_info + '</p>';
    }

    $('#yudingContent').html(ydHtml);
}


var nowDate = new Date(),
    year = nowDate.getFullYear(),
    month = nowDate.getMonth() + 1,
    priceMonth = $('#priceMonth'),
    priceYear = $('#priceYear');

//立即购买
$('#nowBuyBtn').click(function () {
    var text = $('#proPrice').text();
    if (text == '--') {
        Alert('请先从价格日历选择出游日期');
    } else {
        loginConfirm();
    }
})

function loginConfirm() {
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
                    kc = chooseOn.find('.kc').text(),
                    dfc = chooseOn.attr('data-dfc');

                zfInfo = JSON.stringify(zfInfo);
                setCookie('zfInfo', zfInfo, 30 * 60 * 1000);                //自费项目
                setCookie('price', $('#proPrice').text(), 30 * 60 * 1000);  //价格
                setCookie('date', $('#goTime').text(), 30 * 60 * 1000);
                setCookie('kc', kc, 30 * 60 * 1000);
                setCookie('user', userId, 30 * 60 * 1000);
                HREF = controller + '/d_route?shopCode=' + code + '&shopType=' + type;

                setCookie('dfc', dfc, 30 * 60 * 1000);
                if (childZC == 1) { setCookie('zc', 1, 30 * 60 * 1000); }

                setCookie('name', name, 30 * 60 * 1000);  //产品名字
                //儿童价
                setCookie('childPrice', childPrice, 30 * 60 * 1000);
                toast.hide();
                location.href = HREF;

            } else {
                location.href = controller + '/login';     //未登录跳转登陆
            }
        }
    })
}
var DOM = $('#contentList'),
    TOP = DOM.offset().top,
    allDocument = $(document),
    rili = $('#riliNav'),
    miaoshu = $('#miaoshuNav'),
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
        1: miaoshu.offset().top - 40,
        2: feiyong.offset().top - 40,
        3: yuding.offset().top - 40
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
        desc: $('#proName').text(),
        link: link,
        imgUrl: public + '/Weixin/image/wxbg.png',
    });
})







