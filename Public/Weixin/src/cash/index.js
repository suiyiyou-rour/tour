require('./index.scss');
require('COMMON/bottom-menu.js');
var Alert = require('COMMON/Alert-mb.js');
var warning = require('COMMON/warning-mb.js');

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
        $(this).val(int + '.' + dec.substring(0,2));
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



})