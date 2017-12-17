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
    <script src="/Public/common/jquery-2.1.1.min.js"></script>
    <link rel="stylesheet" href="//at.alicdn.com/t/font_422207_kse831de677dgqfr.css">
    <link rel="stylesheet" href="/Public/Weixin/dist/p_ticket.min.css?st=v1.0.2">
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script>
        window.public = "/Public";
        window.controller = "/index.php/Weixin/Index";
        window.CONFIG = <?php echo ($wxconfig); ?>;
    </script>
</head>

<body>
    <div id="production">
        <div class="pro-top">
            <div class="count-num">
                <span id="nowNum">1</span>/
                <span id="allNum">1</span>
            </div>
            <div id="proPic">

            </div>
            <div class="pro-mess">
                <p class="pro-type">城市 :
                    <span id="proCity"></span>
                </p>
                <p class="pro-id">产品ID :
                    <span id="proID"></span>
                </p>
            </div>
        </div>
        <div class="pro-content">
            <p id="proName"></p>
        </div>
        <?php if($_COOKIE['company']): ?><div id="fxsMessBox">
                <div class="pic-box">
                    <img id="fxsPic" src="<?php echo ($_COOKIE['img']); ?>" alt="">
                </div>
                <div class="fxs-mess">
                    <p class="fxs-name"><?php echo ($_COOKIE['company']); ?></p>
                    <p class="fxs-job">
                        <i class="syy icon-ren"></i> : 经理</p>
                    <p class="fxs-mobile">
                        <i class="syy icon-dianhua1"></i> : <?php echo ($_COOKIE['phone']); ?>
                    </p>
                </div>
            </div><?php endif; ?>
        <div class="pro-detail">
            <div class="fixed-box">
                <ul id="contentList">
                    <li class="list-item">价格日历</li>
                    <li class="list-item">费用说明</li>
                    <li class="list-item">预定须知</li>
                </ul>
            </div>
            <p id="riliNav" class="item-de">
                <i class="syy icon-rl"></i>价格日历
                <i class="syy icon-xiajiantou"></i>
            </p>
            <div id="riliContent" class="content-box">
                <table id="datePriceTable" border="1">
                    <thead>
                        <tr>
                            <th colspan="7" id="controllerTime">
                                <span id="prevMonthBtn" class="monthBtn">上月</span>
                                <span>
                                    <b id="priceYear">2017</b>年
                                    <b id="priceMonth">11</b>月</span>
                                <span id="nextMonthBtn" class="monthBtn">下月</span>
                            </th>
                        </tr>
                        <tr class="price-week">
                            <th>日</th>
                            <th>一</th>
                            <th>二</th>
                            <th>三</th>
                            <th>四</th>
                            <th>五</th>
                            <th>六</th>
                        </tr>
                    </thead>
                    <tbody id="priceDay">

                    </tbody>
                </table>
            </div>
            <p id="feiyongNav" class="item-de">
                <i class="syy icon-feiyong"></i>费用说明
                <i class="syy icon-xiajiantou"></i>
            </p>
            <div id="feiyongContent" class="content-box">

            </div>
            <p id="yudingNav" class="item-de">
                <i class="syy icon-shuoming"></i>预定须知
                <i class="syy icon-xiajiantou"></i>
            </p>
            <div id="yudingContent" class="content-box">

            </div>
        </div>
        <div class="buy-box">
            <p class="pro-price">售价 :
                <span class="red">￥</span>
                <span id="proPrice">--</span>
                <span id="goTime"></span>
            </p>
            <button id="nowBuyBtn">立即购买</button>
        </div>
        <a id="goToHome" href="/index.php/Weixin/Index/home"><i class="syy icon-home"></i></a>
    </div>
</body>
<script src="/Public/Weixin/dist/p_ticket.min.js?st=1.0.3"></script>

</html>