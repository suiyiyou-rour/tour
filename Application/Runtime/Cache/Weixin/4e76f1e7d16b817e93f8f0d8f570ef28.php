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
    <link rel="stylesheet" href="//at.alicdn.com/t/font_422207_ry9l0xfukwfm9529.css">
    <link rel="stylesheet" href="/Public/Weixin/dist/home.min.css?st=v1.0.4">
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
</head>
<script>
    window.public = "/Public";
    window.controller = "/index.php/Weixin/Index";
    window.CONFIG = <?php echo ($wxconfig); ?>;
</script>

<body>
    <div id="homeBoxPage">
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

                <?php if($think.session.online_use_info.user_type == 2 ): ?><span id="getMoney">提现</span><?php endif; ?>
            </div><?php endif; ?>

        <div class="search-box">
            <div id="inputBox">
                <i class="syy icon-sousuo"></i>
                <input type="text" id="searchInp" class="search-inp" readonly placeholder="我想找...">
            </div>
            <!-- <button id="searchBtn">搜索</button> -->
        </div>
        <div id="carouselBg">
            <ul id="pointers">
                <li class="pointer on"></li>
                <li class="pointer"></li>
                <li class="pointer"></li>
            </ul>
        </div>
        <div class="module">
            <a class="module-a" id="xianLu" href="/index.php/Weixin/Index/s_route">
                <div class="module-icon gentuan">
                    <i class="syy icon-xianlu"></i>
                </div>
                <p class="module-name">跟团线路</p>
            </a>
            <!-- <a class="module-a" id="jiuDian" href="/index.php/Weixin/Index/s_hotel">
                <div class="module-icon jiudian">
                    <i class="syy icon-jiudian"></i>
                </div>
                <p class="module-name">特惠酒店</p>
            </a> -->
            <a class="module-a" id="menPiao" href="/index.php/Weixin/Index/s_ticket">
                <div class="module-icon menpiao">
                    <i class="syy icon-menpiao"></i>
                </div>
                <p class="module-name">景区门票</p>
            </a>
            <a class="module-a" id="dingDan" href="/index.php/Weixin/Index/order">
                <div class="module-icon dingdan">
                    <i class="syy icon-dingdan"></i>
                </div>
                <p class="module-name">订单管理</p>
            </a>
        </div>
        <div id="production">
            <p class="pro-title">热门推荐</p>
            <div id="productBox">

            </div>
        </div>

        <div id="bottomBox">
            <p class="service-mobile">
                <i class="syy icon-dianhua"></i>
                客服热线 : 17759185562
            </p>
            <p class="companyName">©2017 随意游-suiyiyou.net</p>
        </div>
    </div>
</body>
<script src="/Public/common/jquery-2.1.1.min.js"></script>
<script src="/Public/Weixin/dist/home.min.js?st=v1.0.4"></script>

</html>