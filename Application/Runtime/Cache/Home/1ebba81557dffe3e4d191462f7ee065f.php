<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_469978_oxn01xvt918umcxr.css">
    <link rel="stylesheet" href="/Public/Page/dist/p_hotel.min.css">
    <link rel="icon" type="image/jpg" href="/Public/Page/image/logo-icon.jpg">
    <title>随意游</title>
</head>

<body>
<header id="topTips">
    <div class="top-tips">
        <div class="top-item">
            <i class="syy icon-dianhua"></i>官方咨询电话 : 18512009795
        </div>
        <div class="top-item" id="wxCode">
            <i class="syy icon-erweima"></i>微信公众号
        </div>
    </div>
</header>

<div id="nav">
    <div class="nav-box">
        <a href="<?php echo U('index');?>">
            <div class="nav-item">首页</div>
        </a>
        <a href="<?php echo U('groupList');?>">
            <div class="nav-item">跟团游</div>
        </a>
        <a href="<?php echo U('seceneyList');?>">
            <div class="nav-item-on">景酒套餐</div>
        </a>
        <a href="<?php echo U('tickList');?>">
            <div class="nav-item">景区门票</div>
        </a>

        <div class="login-register">
            <?php if(!empty($mobile)): ?><a class="lr" href=""><?php echo ($mobile); ?></a>
                <a class="lr" href="<?php echo U('Tourist/logingOut');?>">退出</a>
                <?php else: ?>
                <a class="lr" href="<?php echo U('Tourist/usershowLogin');?>">登陆</a>
                <a class="lr" href="<?php echo U('Tourist/userRegistShow');?>">注册</a><?php endif; ?>

        </div>
    </div>
</div>

<div id="background">
    <img id="bigBg" src="/Public/Page/image/bg.jpg" alt="">

    <div id="selectItem">
        <ul class="line-item" id="jq">
            <li class="line-name">价格区间 :</li>
            <li class="item on" onclick="price(this,-1)">不限</li>
            <li class="item" onclick="price(this,0,100)">100以内</li>
            <li class="item" onclick="price(this,100,500)">100~500</li>
            <li class="item" onclick="price(this,500,1000)">500~1000</li>
            <li class="item" onclick="price(this,1001,10002)">大于1000</li>
            <li class="item">
                <input class="price-area" id="bp" type="text">~<input class="price-area" id='ep' type="text">
                <button id="priceBtn">确定</button>
            </li>
        </ul>
        <div id="searchInpBox">
            <input class="seach-inp" type="text" id="hotelSearchInp" placeholder="地区">
            <div class="date-box">
                <input type="text" readonly placeholder="入住时间" class="seach-inp datepicker-here" id="hotelBeginTime">
                <i class="syy icon-rl"></i>
            </div>
            <div class="date-box">
                <input type="text" readonly placeholder="离店时间" class="seach-inp datepicker-here" id="hotelEndTime">
                <i class="syy icon-rl"></i>
            </div>
            <div class="date-box">
                <select class="seach-inp" id="adult">
                    <option selected value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
                <span class="unit">成人</span>
                <i class="syy icon-user"></i>
            </div>
            <div class="date-box">
                <select class="seach-inp" id="children">
                    <option selected value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
                <span class="unit">儿童</span>
                <i class="syy icon-ertong"></i>
            </div>
        </div>
        <div class="item-box">
            <input class="seach-inp" type="text" id="allSearchInp" placeholder="请输入酒店名称搜索...">
            <button class="search-btn" id="allSearchBtn">
                <i class="syy icon-sousuo" id='sb'></i>
            </button>
        </div>
    </div>
</div>

<div id="content">
    <div class="sort-bar" id='px'>
        <div class="sort-item on" onclick="zh(this)">综合排序</div>
        <div class="sort-item" onclick="xl(this)">销量优先</div>
        <div class="sort-item" onclick="aprice(this)">价格从低到高</div>
        <div class="sort-item" onclick="dprice(this)">价格从高到低</div>
    </div>
    <div class="content-box" id="list">

    </div>
</div>

<footer id="footer">
    <div class="footer-content">
        <img class="bottom-logo" src="/Public/Page/image/bottom.png" alt="" width="150">
    </div>
</footer>
</body>
<script src="/Public/common/jquery-2.1.1.min.js"></script>
<script src="/Public/Page/dist/p_hotel.min.js"></script>
<script>
    function addSeceney() {
        $.ajax({
            url: "<?php echo U('seceneyLi');?>",
            dataType: "HTML",
            success: function (respons) {
                $('#list').html(respons)
            }
        })
    }

    addSeceney();

    function price(c, ap, bp) {
        $.ajax({
            url: "<?php echo U('seceneyLi');?>",
            method: "POST",
            data: {'ap': ap, "bp": bp},
            dataType: "HTML",
            success: function (respons) {
                $('#list').html(respons)
                $('#jq li').removeClass('on')
                $(c).addClass('on')
            }
        })
    }

    $('#priceBtn').click(function () {
        bp = $('#bp').val();
        ep = $('#ep').val();
        if (bp == '' || ep == '') {
            alert('请填写完整的价格区间')
            return false;
        }
        $.ajax({
            url: "<?php echo U('seceneyLi');?>",
            method: "POST",
            data: {'ap': bp, "bp": ep},
            dataType: "HTML",
            success: function (respons) {
                $('#list').html(respons)
                $('#jq li').removeClass('on')
            }
        })
    })
    $('#sb').click(function () {
        hotelSearchInp = $('#hotelSearchInp').val();
        hotelBeginTime = $('#hotelBeginTime').val();
        hotelEndTime = $('#hotelEndTime').val();
        adult = $('#adult').val();
        allSearchInp = $('#allSearchInp').val();
        children = $('#children').val();
        $.ajax({
            url: "<?php echo U('seceneyLi');?>",
            method: "POST",
            data: {
                "children": children,
                'hotelSearchInp': hotelSearchInp,
                "hotelBeginTime": hotelBeginTime,
                "hotelEndTime": hotelEndTime,
                "adult": adult,
                "allSearchInp": allSearchInp
            },
            dataType: "HTML",
            success: function (respons) {
                $('#list').html(respons)
                $('#jq li').removeClass('on')
            }
        })
    })

    function xl(c) {
        $.ajax({
            url: "<?php echo U('seceneyLi');?>",
            method: "POST",
            data: {'xl': 's'},
            dataType: "HTML",
            success: function (respons) {
                $('#list').html(respons)
                $('#px div').removeClass('on')
                $(c).addClass('on')
            }
        })
    }

    function aprice(c) {
        $.ajax({
            url: "<?php echo U('seceneyLi');?>",
            method: "POST",
            data: {'aprice': 's'},
            dataType: "HTML",
            success: function (respons) {
                $('#px div').removeClass('on')
                $('#list').html(respons)
                $(c).addClass('on')
            }
        })
    }

    function dprice(c) {
        $.ajax({
            url: "<?php echo U('seceneyLi');?>",
            method: "POST",
            data: {'dprice': 's'},
            dataType: "HTML",
            success: function (respons) {
                $('#px div').removeClass('on')
                $('#list').html(respons)
                $(c).addClass('on')
            }
        })
    }

    function zh(c) {
        $.ajax({
            url: "<?php echo U('seceneyLi');?>",
            method: "POST",
            dataType: "HTML",
            success: function (respons) {
                $('#px div').removeClass('on')
                $('#list').html(respons)
                $(c).addClass('on')
            }
        })
    }
</script>
</html>