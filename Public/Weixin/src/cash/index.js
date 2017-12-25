require('./index.scss');
require('COMMON/bottom-menu.js');
var Alert = require('COMMON/Alert-mb.js');
var Confirm = require('COMMON/Confirm-mb.js');
var warning = require('COMMON/warning-mb.js');

(function () {
    //账户余额
    $.ajax({
        url: 'http://www.suiyiyou.net/index.php/weixin/JxsCash/getBalance',
        success: function (res) {
            var code = res.code;
            var data = res.data;
            if (code == 200) {
                $('#money').text(data.balance);
            }
        }
    })

})();

//全部提现
$('#allCashBtn').click(function () {

    var money = $('#money').text(),
        moneyInp = $('#moneyInp');

    if (+money == 0) {
        Alert('账户中没有余额!');
        return false;
    }

    moneyInp.val(money);
})

//收支明细
var detailBox = $('#detailBox'),
    detailList = $('#detailList');

$('#detailBtn').click(function (e) {

    detailBox.show();

    detailList.animate({
        left: '30%'
    }, 200);

})

$('#back').click(function () {
    detailList.animate({
        left: '100%'
    }, 200, function () {
        detailBox.hide();
    });
})

detailList.scroll(function (e) {
    e.stopPropagation();
})

function iToFixed(num, length) {

    var length = length || 2;

    if (isNaN(+num)) {
        return false;
    }
    //如果是整数,直接返回
    if (parseInt(num) == +num) {
        return num;
    } else {
        //不是正整数,变字符串
        num = num.toString();
        var numArr = num.split('.'),
            int = numArr[0],    //整数部分
            dec = numArr[1];    //小数部分

        if (length) {

            if (length > dec.length) {

                for (var i = 0; i < length - dec.length; i++) {
                    dec += '0';
                }
                return +(int + '.' + dec);

            } else if (length == dec.length) {
                return +num;
            } else {
                if (+dec[length] >= 5) {

                    var index = +dec[length - 1] + 1;
                    dec = dec.substring(0, length - 1);
                    dec += index.toString();
                    return +(int + '.' + dec);

                } else {
                    dec = dec.substring(0, length);
                    return +(int + '.' + dec);
                }
            }

        } else {
            if (+dec[0] >= 5) {
                return +int + 1;
            } else {
                return +int;
            }
        }
    }
}


$('#moneyInp').on('input', function () {

    var money = $(this).val(),
        int = money.split('.')[0],
        dec = money.split('.')[1];

    if (dec && dec.length > 2) {
        $(this).val(int + '.' + dec.substring(0, 2));
    }
})

//提现
$('#cashBtn').click(function () {

    var money = $('#moneyInp').val();

    if (!money) {
        Alert('请输入要提现的金额!');
        return false;
    }

    money = +money;

    if (isNaN(money) || money < 0) {
        Alert('请输入正确的提现金额!');
        return false;
    }

    if (money < 100) {
        Alert('最小提现金额： 100');
        return false;
    }

    if (money > +$('#money').text()) {
        Alert('提现金额超出账户余额!');
        return false;
    }

    var resMoney,
        rate,
        rateJson = {
            "10%": 0.1,
            "8%": 0.08,
            "7%": 0.07,
            "6%": 0.06
        }

    if (money < 1000) rate = '10%';

    if (money >= 1000 && money < 3000) rate = '8%';

    if (money >= 3000 && money < 5000) rate = '7%';

    if (money >= 5000) rate = '6%';

    rateMoney = iToFixed(money * rateJson[rate]);

    Confirm('提现金额为 ' + money + ' 元 , 收取平台服务费' + rate + '为 ' + rateMoney + ' 元 , 实际到账 ' + iToFixed(money - rateMoney) + ' 元', function () {
        var remarks = $('#remarks').val();
        $.ajax({
            url: 'http://www.suiyiyou.net/index.php/weixin/JxsCash/putInCash',
            data: {
                money: money,
                message: remarks
            },
            success: function (res) {
                if (res.code == 200) {
                    warning('申请成功！请耐心等待...', function () {
                        location.reload();
                    });
                }
            }
        })

    })
})

function showDetailList(data) {

    if(data.length == 0 && page == 1){
        $('#noOrder').show();
        return false;
    }

    var html = '';
    for (var i = 0; i < data.length; i++) {
        html += '<div class="item"><div class="item-m">' +
            '<p class="time">时间: ' + data[i].tb_time + '</p><p class="type ' + typeColor[data[i].tb_code] + '">' + typeJson[data[i].tb_code] + '</p></div>' +
            '<div class="item-d"><p class="money ' + colorJson[data[i].tb_code] + '">';

        if (+data[i].tb_money > 0) {
            html += '+' + data[i].tb_money;
        } else {
            html += data[i].tb_money;
        }
        html += '</p><p class="now">' + data[i].tb_balance + '</p></div></div>';
    }
    detailList.append(html);
}

var page = 1,
    typeJson = {
        1: '进账',
        2: '退款',
        3: '提现成功',
        4: '处理中...',
        5: '拒绝'
    },
    colorJson = {
        1: 'green',
        2: 'red',
        3: 'red',
        4: 'red',
        5: 'red'
    },
    typeColor = {
        1: 'green',
        2: 'red',
        3: 'green',
        4: 'blue',
        5: 'red'
    };

function getList() {
    $.ajax({
        url: 'http://www.suiyiyou.net/index.php/weixin/JxsCash/getBillRecord',
        data: {
            page: page
        },
        success: function (res) {
            if (res.code == 200) {
                var data = res.data;
                showDetailList(data);
            }
        }
    })
}
getList();


