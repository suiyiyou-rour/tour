require('./index.scss');
var Alert = require('COMMON/Alert-mb.js');
var toast = require('COMMON/toast.js');

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

var bottomTotalPri = $('#bottomTotalPri'),
    KC = +getCookie('kc'),
    nowTotal = 1;       //成人，儿童已选票数的总数量

//从url取数据
var onePrice = getCookie('price'),   //成人价格
    code = urlKey('shopCode'),           //商品id
    type = urlKey('shopType'),           //商品类型
    name = getCookie('name'),        //商品名
    zfInfo = getCookie('zfInfo'),    //自费项目
    date = getCookie('date'),        //出游日期
    dateArr = date.split('-'),
    childPrice = getCookie('childPrice'),   //儿童价格
    dfc = getCookie('dfc'),
    zc = getCookie('zc');

$('#ticketTime').text(date + '(' + getDateWeek(dateArr[0], dateArr[1], dateArr[2]) + ')');
$('#totalPri').text(onePrice);
$('#bottomTotalPri').text(onePrice);

//商品名存半小时cookie，判断是否有商品名
if (name) {
    $('#ticketName').text(name);
} else {
    //没有的话跳转商品详情页面
    location.href = controller + '/p_route?shopCode=' + code + '&shopType=' + type;
}

//自费项目信息
zfInfo = JSON.parse(zfInfo);
if (typeof zfInfo == 'string') { 
    $('#zfTitle').hide();
} else {
    $('#zfNum').html(itemNum(zfInfo));
}

//是否存在儿童价信息
if (childPrice) {
    $('#peopleNum').append(peopleNum(onePrice, '成人', 1), peopleNum(childPrice, '儿童', 0));
} else {
    $('#peopleNum').append(peopleNum(onePrice, '人', 1));
}

if (dfc) {
    
    $('#dfcBox').append('<p class="subtitle">单房数量</p><div class="num-box">' +
        '<p class="one-price">￥<span class="js_pri">' + dfc + '</span>/单房差</p>' +
        '<div class="select-num">' +
        '<p class="js_red"><i class="iconfont icon-jianshao"></i></p>' +
        '<span id="dfcNum" class="choose-num">0</span>' +
        '<p class="js_add"><i class="iconfont icon-zengjia"></i></p>' +
        '</div></div>');

    //单房差增加/减少
    $('#dfcBox').on('click', '.js_red', function () {

        var parent = $(this).parents('.num-box'),
            pri = +parent.find('.js_pri').text() * 100,
            tNum = parent.find('.choose-num'),
            numVal = +tNum.text();

        if (numVal == 0) {
            return false;
        }

        var totalPrice = +bottomTotalPri.text() * 100;
        tNum.text(numVal -= 1);
        totalPrice = ((totalPrice - pri) / 100).toFixed(2);
        Number(totalPrice) == totalPrice ? bottomTotalPri.text(Number(totalPrice)) : totalPrice;

    })

    $('#dfcBox').on('click', '.js_add', function () {

        var parent = $(this).parents('.num-box'),
            pri = +parent.find('.js_pri').text() * 100,
            tNum = parent.find('.choose-num'),
            numVal = +tNum.text();

        if (zc) {
            if (nowTotal == numVal) {
                Alert('单房差已与人数数量一致');
                return false;
            }
        } else {
            if (nowTotal - (+$('.choose-num.man').eq(1).text()) == numVal) {
                Alert('单房差已与成人数量一致');
                return false;
            }
        }

        var totalPrice = +bottomTotalPri.text() * 100;
        tNum.text(numVal += 1);
        totalPrice = ((totalPrice + pri) / 100).toFixed(2);
        Number(totalPrice) == totalPrice ? bottomTotalPri.text(Number(totalPrice)) : totalPrice;

    })
}

//成人儿童不同票价
function peopleNum(price, mantype, num) {
    var html = '<div class="num-box">' +
        '<p class="one-price">￥<span class="js_pri">' + price + '</span>/' + mantype + '</p>' +
        '<div class="select-num">' +
        '<p class="js_red"><i class="iconfont icon-jianshao"></i></p>' +
        '<span data-type=' + num + ' class="choose-num man">' + num + '</span>' +
        '<p class="js_add"><i class="iconfont icon-zengjia"></i></p>' +
        '</div></div>';

    return html;
}

//自费项目
function itemNum(data) {

    var html = '',
        len = data.length;

    for (var i = 0; i < len; i++) {
        html += '<div class="num-box">' +
            '<p class="one-price">￥<span class="js_pri">' + data[i].price + '</span>/人</p>' +
            '<p class="item-name">' + data[i].name + '</p>' +
            '<div class="select-num">' +
            '<p class="js_red"><i class="iconfont icon-jianshao"></i></p>' +
            '<span class="choose-num zf">0</span>' +
            '<p class="js_add"><i class="iconfont icon-zengjia"></i></p>' +
            '</div></div>';
    }

    return html;
}

//减少
$('#ticketNumBox').on('click', '.js_red', function () {

    var parent = $(this).parents('.num-box');
    var pri = +parent.find('.js_pri').text() * 100;
    var tNum = parent.find('.choose-num');
    var numVal = +tNum.text();

    //数量选择
    if (tNum.hasClass('man')) {
    
        if (tNum.attr('data-type') == '1' && numVal == 1) {
            Alert('成人最少购买数量1');
            return false;
        } else if (tNum.attr('data-type') == '0' && numVal == 0) {
            return false;
        }

        nowTotal -= 1;

        var zfItem = $('.choose-num.zf'),
            price = zfItem.parents('.num-box').find('.js_pri');

        for (var i = 0; i < zfItem.length; i++) {
            if (+zfItem.eq(i).text() > nowTotal) {
                zfItem.eq(i).text(nowTotal);
                bottomTotalPri.text((+bottomTotalPri.text() * 100 - +price.eq(i).text() * 100) / 100);
            }
        }

        var dfcDom = $('#dfcNum');
        dfcNum = +dfcDom.text();

        if (dfc && tNum.attr('data-type') == '1') {
            if (zc) {
                if (nowTotal < dfcNum) {
                    dfcDom.text(dfcNum -= 1);
                    bottomTotalPri.text((+bottomTotalPri.text() * 100 - +dfc * 100) / 100);
                }
            } else {
                if (nowTotal - (+$('.choose-num.man').eq(1).text()) < dfcNum) {
                    dfcDom.text(dfcNum -= 1);
                    bottomTotalPri.text((+bottomTotalPri.text() * 100 - +dfc * 100) / 100);
                }
            }
        }
    }



    //自费项目选择
    if (tNum.hasClass('zf')) {
        if (numVal == 0) {
            return false;
        }
    }
    var totalPrice = +bottomTotalPri.text() * 100;
    tNum.text(numVal -= 1);
    totalPrice = ((totalPrice - pri) / 100).toFixed(2);
    Number(totalPrice) == totalPrice ? bottomTotalPri.text(Number(totalPrice)) : totalPrice;
})

//增加
$('#ticketNumBox').on('click', '.js_add', function () {

    var parent = $(this).parents('.num-box'),
        pri = +parent.find('.js_pri').text() * 100,
        tNum = parent.find('.choose-num'),
        numVal = +tNum.text();

    if (tNum.hasClass('man')) {

        if (nowTotal < KC) {
            nowTotal += 1;
        } else {
            Alert('已达到库存上限');
            return false;
        }
    }

    //自费项目选择
    if (tNum.hasClass('zf')) {
        if (numVal == nowTotal) {
            Alert('数量已与人数一致');
            return false;
        }
    }

    var totalPrice = +bottomTotalPri.text() * 100;
    tNum.text(numVal += 1);
    totalPrice = ((totalPrice + pri) / 100).toFixed(2);
    Number(totalPrice) == totalPrice ? bottomTotalPri.text(Number(totalPrice)) : totalPrice;

})



//去填写游客信息
$('#goTourBtn').click(function () {

    if ($('#reserveCheck').prop('checked')) {

        if (confirm('是否确认' + $('#ticketTime').text() + '出行?')) {
            $('#reserveMess').fadeOut(200, function () {
                $('#tourristMess').fadeIn(200);
            });

            var resPrice = bottomTotalPri.text();
            $('#orderTotalPri').text(resPrice);

            var manNum = $('.choose-num.man');
            if (manNum.length == 1) {
                var itemNum = +manNum.text() - 1;
            } else {
                var itemNum = +manNum.eq(0).text() + +manNum.eq(1).text() - 1;
            }
            var html = '';

            for (var i = 0; i < itemNum; i++) {
                html += '<div class="item-container"><div class="item-box"><p class="item-p"><span class="red">' + (i + 2) + '.</span>游客姓名 : </p><input class="item-inp name" placeholder="姓名" type="text"></div>' +
                    '<div class="item-box"><p class="item-p">身份证号 : </p><input class="item-inp identify" type="text" placeholder="若是儿童输入出生日期"></div></div>';
            }

            $('#secItem').html(html);
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
    if (tourIdentify) {
        if (!checkIdentity(tourIdentify)) {
            Alert('第1位游客身份证号码错误!');
            return false;
        }
    } else {
        Alert('请输入第1位游客的身份证号码!');
        return false;
    }

    var remarks = $('#remarks').val();

    var data = {};
    data.info = [];

    var len = $('.item-container').length;

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

        data.info.push(json);  //身份数组
    }

    data.g_code = urlKey('shopCode');
    data.g_user_id = getCookie('user');
    data.identification = tourIdentify;

    data.mobile = tourMobile; //游客手机
    data.name = tourName;     //第一位游客姓名
    data.jxscode = '';        //以后的分销商pid
    data.remarks = remarks;   //备注
    data.g_on_time = date;    //出行时间
    data.dfcNum = +$('#dfcNum').text();

    if (zfInfo) {
        var zfNum = $('.choose-num.zf');
        for (var i = 0; i < zfInfo.length; i++) {
            zfInfo[i].num = zfNum.eq(i).text();
        }
    }
    //自费项目信息
    data.zfInfo = zfInfo;

    var manNum = $('.choose-num.man');
    //成人 儿童数量
    if (manNum.lenght == 1) {
        data.menNum = manNum.text();
        data.childNum = 0;
    } else {
        data.menNum = manNum.eq(0).text();
        data.childNum = manNum.eq(1).text();
    }

    data.totalPrice = $('#orderTotalPri').text();  //总价

    var _this = $(this);

    _this.prop('disabled', true);
    _this.css('backgroundColor', '#ffa69c');

    toast.show();

    $.ajax({
        url: 'http://www.suiyiyou.net/index.php/weixin/Order/addGroupOrder',
        data: data,
        type: 'POST',
        success: function (res) {
            toast.hide();
            var code = res.code,
                data = res.data;
            if (code == 200) {
                location.href = 'http://www.suiyiyou.net/index.php/Weixin/Jsapi/index?&orderSn=' + data.orderSn + '&shopType=' + type + '&';
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