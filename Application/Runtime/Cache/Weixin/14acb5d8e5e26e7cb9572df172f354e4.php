<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        <?php if(isset($_COOKIE['company'])): echo ($_COOKIE['company']); ?>
            <?php else: ?> 随意游-suiyiyou.net<?php endif; ?>
    </title>
    <link rel="stylesheet" href="//at.alicdn.com/t/font_422207_f855orumfyo80k9.css">
    <link rel="stylesheet" href="/tour/Public/Weixin/dist/cash.min.css?st=v1.0.0">
</head>
<script>
    window.public = "/tour/Public";
    window.controller = "/tour/index.php/Weixin/Index";
</script>
<body>
    <div class="title">
        <i class="syy icon-zuojiantou"></i>
        <span class="detail">收支明细</span>
    </div>
    <div class="money-box">
        <i class="syy icon-tixian"></i>
        <p><span id="money">00.00</span><span class="unit">元</span></p>
    </div>
    <div class="money-inp-box">
        <span class="cash-money">提现金额 :</span>
        <input type="text" id="moneyInp" placeholder="请输入提现金额">
        <span id="allCashBtn">全部提现</span>
    </div>
    <p id="cashBtn">提 现</p>
</body>
<script src="/tour/Public/common/jquery-2.1.1.min.js"></script>
<script src="/tour/Public/Weixin/dist/cash.min.js?st=v1.0.0"></script>

</html>