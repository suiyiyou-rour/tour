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
    <link rel="stylesheet" href="/Public/Weixin/dist/ticket_detail.min.css?st=v0.0.0">
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script>
        window.public = "/Public";
        window.controller = "/index.php/Weixin/Index";
        window.CONFIG = <?php echo ($wxconfig); ?>;
    </script>
</head>

<body>

    <p class="main-title">
        <span id="statusTitle">--</span>订单</p>
    <img id="orderPic" src="" alt="">
    <div id="orderMessBox">
        <div class="item">
            <p class="title">订单号 : </p>
            <p class="mess" id="orderNum">--</p>
        </div>
        <div class="item">
            <p class="title">产品ID : </p>
            <p class="mess" id="proNum">--</p>
        </div>
        <div class="item">
            <p class="title">产品名称 : </p>
            <p class="mess" id="orderName">--</p>
        </div>
        <div class="item">
            <p class="title">出游日期 : </p>
            <p class="mess" id="orderTime">--</p>
        </div>
        <div class="item">
            <p class="title">数量 : </p>
            <p class="mess" id="num">--</p>
        </div>
        <div class="item">
            <p class="title">联系人 : </p>
            <p class="mess" id="connectName">--</p>
        </div>
        <div class="item">
            <p class="title">联系手机号 : </p>
            <p class="mess" id="connectMobile">--</p>
        </div>
        <div id="identifyBox"></div>
        <div class="item">
            <p class="title">备注 : </p>
            <p class="mess" id="remarks">--</p>
        </div>
    </div>
    <div class="bottom-box">
        <p class="price">总价 : ￥
            <span id="totalNum">--</span>
        </p>
    </div>
</body>
<script src="/Public/common/jquery-2.1.1.min.js"></script>
<script src="/Public/Weixin/dist/ticket_detail.min.js?st=v0.0.0"></script>

</html>