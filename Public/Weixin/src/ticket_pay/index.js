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
                        shopType: 'tick',
                        orderSn: data.t_order_sn
                    }
                })
                warning('订单已过期',function(){
                    location.href = 'http://www.suiyiyou.net/index.php/Weixin/index/order';
                })
            }
        }, 1000)
    };

    $('#orderPic').attr('src', data.img);
    $('#orderNum').html(data.t_order_sn);
    $('#proNum').html(data.t_tick_code);
    $('#orderName').html(data.t_tick_name);

    var timeArr = data.t_go_date.split('-');
    $('#orderTime').html(data.t_go_date + '(' + getDateWeek(timeArr[0], timeArr[1], timeArr[2]) + ')');

    $('#num').html('￥' + data.t_tick_my_price + ' x ' + data.t_tick_num);

    $('#connectName').html(data.t_order_user_name);
    $('#connectMobile').html(data.t_order_user_mobile);
    $('#connectIdent').html(data.g_identification);

    $('#totalNum').html(data.t_tick_price);

    if (data.playerInfo) {

        if(data.playerInfo.length > 0){
            var html = '';
            for (var i = 0; i < data.g_identity_info.length; i++) {
                html += '<div class="identify">' +
                    '<p class="title-mess"><span class="title">游客姓名 : </span><span class="mess">' + data.g_identity_info[i].name + '</span></p>' +
                    '<p class="title-mess"><span class="title">身份信息 : </span><span class="mess">' + data.g_identity_info[i].identify + '</span></p>' +
                    '</div>';
            }
    
            $('#identifyBox').append(html);
        }

    }

    if (data.g_remark) {
        $('#remarks').html(data.g_remark);
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

