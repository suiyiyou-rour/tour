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
    <link rel="stylesheet" href="//at.alicdn.com/t/font_422207_gufwgxz7bla3jtt9.css">
    <link rel="stylesheet" href="/Public/Weixin/dist/search.min.css?st=v0.0.1">
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script>
        window.public = "/Public";
        window.controller = "/index.php/Weixin/Index";
        window.CONFIG = <?php echo ($wxconfig); ?>;
    </script>
</head>

<body>

    <input id="whichPage" type="hidden" value="<?php echo ($remark); ?>">

    <p class="title">
        <a class="home" href="/index.php/Weixin/Index/home">
            <i class="syy icon-home"></i>
        </a>搜索
        <i id="eye" class="syy icon-open"></i>
    </p>
    <ul class="module">
        <li class="module-item">
            <a href="/index.php/Weixin/Index/s_route">
                <i class="syy icon-zhilupai"></i>跟团线路</a>
        </li>
        <li class="module-item on" id="dataType" data-type="SceneryList">
            <i class="syy icon-icon36"></i>特惠酒店</li>
        <li class="module-item">
            <a href="/index.php/Weixin/Index/s_ticket">
                <i class="syy icon-menpiao1"></i>景点门票</a>
        </li>
    </ul>
    <div class="search-box">
        <div class="search-content">
            <!-- <div id="location">
                <i class="syy icon-dingwei"></i>
                <span id="city">福州</span>
            </div> -->
            <input type="text" id="searchInp" placeholder="按酒店标题搜索">
            <i class="syy icon-chahao"></i>
        </div>
    </div>
    <div id="contentBox">
        <div id="noContent" style="display:none">
            <i class="syy icon-meiyoushuju"></i>
            <p class="no-content">暂无内容</p>
            <p class="no-message">没有找到相关信息</p>
        </div>

        <div id="contentItem">

        </div>
    </div>

    <div class="scroll-top" id="scrollTop">
        <i class="syy icon-up"></i>
    </div>


</body>
<script src="/Public/common/jquery-2.1.1.min.js"></script>
<script src="/Public/Weixin/dist/search.min.js?st=v0.0.0"></script>

</html>