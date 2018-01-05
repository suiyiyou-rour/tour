require('./index.scss');
var Alert = require('COMMON/Alert-mb.js');
var warning = require('COMMON/warning-mb.js');

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

    if (data.count == '-1') {
        warning('订单已过期',function(){
            location.href = 'http://www.suiyiyou.net/index.php/Weixin/index/order';
        })
    } else {
        var timeBox = $('#orderCount');
        var count = +data.count;  //时间戳
        var min = parseInt(count / 60); //分钟数
        var sec = count % 60; //秒数
        var timer = setInterval(function () {
            if (sec == 0) {
                sec = 59;
                min--;
            } else {
                sec--;
            }
            sec < 10 ? timeBox.text(min + ':0' + sec) : timeBox.text(min + ':' + sec);
            if (min == 0 && sec == 0) {
                clearInterval(timer);
                $.ajax({
                    url: 'http://www.suiyiyou.net/index.php/weixin/Order/CloseOrder',
                    data: {
                        shopType: 'scenery',
                        orderSn: data.o_order_sn
                    }
                })
                warning('订单已过期',function(){
                    location.href = 'http://www.suiyiyou.net/index.php/Weixin/index/order';
                })
            }
        }, 1000)
    };

    $('#orderPic').attr('src', data.img);
    $('#orderNum').html(data.o_order_sn);
    $('#proNum').html(data.o_seceny_code);
    $('#orderName').html(data.o_seceny_name);

    var timeArr = data.o_date.split('-');
    $('#orderTime').html(data.o_date + '(' + getDateWeek(timeArr[0], timeArr[1], timeArr[2]) + ')');

    $('#num').html('￥' + data.o_plane_price + ' x ' + data.o_num);

    $('#connectName').html(data.o_name);
    $('#connectMobile').html(data.o_mobile);
    $('#connectIdent').html(data.g_identification);

    $('#totalNum').html(data.o_order_price);

    if (data.remarks) {
        $('#remarks').html(data.remarks);
    }

}

var orderSn = urlKey('orderSn'),
    type = urlKey('shopType'),
    URL = 'http://www.suiyiyou.net/index.php/weixin/order/getOrderDetails',
    data = {
        orderSn: orderSn,
        shopType: type
    };

$.ajax({
    url: URL,
    data: data,
    type: 'POST',
    success: function (res) {
        var code = res.code,
            data = res.data;
        if (code == 200) {
            $('#loadingPc').hide();
            dataRender(data);
        } else {
            Alert(res.msg);
        }
    }
})

