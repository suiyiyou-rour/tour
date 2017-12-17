<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_469978_oxn01xvt918umcxr.css">
    <link rel="stylesheet" href="/Public/Page/dist/p_ticket.min.css">
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
            <div class="nav-item">景酒套餐</div>
        </a>
        <a href="<?php echo U('tickList');?>">
            <div class="nav-item-on">景区门票</div>
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
        <ul class="line-item" id='pu'>
            <li class="line-name">价格区间 :</li>
            <li class="item on" id='f'>不限</li>
            <li class="item" id="a">100以内</li>
            <li class="item" id="b">100~500</li>
            <li class="item" id="c">500~1000</li>
            <li class="item" id="d">大于1000</li>
            <li class="item">
                <input class="price-area" type="text" id='bp'>~<input id='ep' class="price-area" type="text">
                <button id="priceBtn">确定</button>
            </li>
        </ul>
        <div id="searchInpBox">
            <input class="seach-inp" type="text" id="hotelSearchInp" placeholder="地区">
            <div class="date-box">
                <input type="text" readonly placeholder="游玩时间" class="seach-inp datepicker-here" id="hotelBeginTime">
                <i class="syy icon-rl"></i>
            </div>
            <div class="date-box">
                <input type="text" readonly placeholder="结束时间" class="seach-inp datepicker-here" id="hotelEndTime">
                <i class="syy icon-rl"></i>
            </div>
        </div>
        <div class="item-box">
            <input class="seach-inp" type="text" id="allSearchInp" placeholder="请输入景点名称搜索...">
            <button class="search-btn" id="allSearchBtn">
                <i class="syy icon-sousuo" id='searchb'></i>
            </button>
        </div>
    </div>
</div>

<div id="content">
    <div class="sort-bar" id='sb'>
        <div class="sort-item on" id='all'>综合排序</div>
        <div class="sort-item" id='xl'>销量优先</div>
        <div class="sort-item" id='ascprice'>价格从低到高</div>
        <div class="sort-item" id='descprice'>价格从高到低</div>
    </div>
    <div class="content-box" id='list'>
    </div>
</div>

<footer id="footer">
    <div class="footer-content">
        <img class="bottom-logo" src="/Public/Page/image/bottom.png" alt="" width="150">
    </div>
</footer>
</body>
<script src="/Public/common/jquery-2.1.1.min.js"></script>
<script src="/Public/Page/dist/p_ticket.min.js"></script>
<script>
    function addTick() {
        $.ajax({
            url: "<?php echo U('tickLI');?>",
            method: "POST",
            dataType: "HTML",
            success: function (response) {
                $('#list').html(response)
            }
        })
    }

    addTick();


    $('#descprice').click(function () {
        $.ajax({
            url: "<?php echo U('tickLi');?>",
            data: {'desprice': 'a'},
            method: "POST",
            dataType: "HTML",
            success: function (response) {
                $('#list').html(response)
                $('#sb div').removeClass('on')
                $('#descprice').addClass('on')
            }
        })
    })

    $('#ascprice').click(function () {
        $.ajax({
            url: "<?php echo U('tickLi');?>",
            data: {'ascprice': 'd'},
            method: "POST",
            dataType: "HTML",
            success: function (response) {
                $('#list').html(response)
                $('#sb div').removeClass('on')
                $('#ascprice').addClass('on')
            }
        })
    })
    $('#xl').click(function () {
        $.ajax({
            url: "<?php echo U('tickLi');?>",
            data: {'xl': 'd'},
            method: "POST",
            dataType: "HTML",
            success: function (response) {
                $('#list').html(response)
                $('#sb div').removeClass('on')
                $('#xl').addClass('on')
            }
        })
    })
    $('#all').click(function () {
        $.ajax({
            url: "<?php echo U('tickLi');?>",
            data: {'all': 'd'},
            method: "POST",
            dataType: "HTML",
            success: function (response) {
                $('#list').html(response)
                $('#sb div').removeClass('on')
                $('#all').addClass('on')
            }
        })
    })

    $('#all').click(function () {
        $.ajax({
            url: "<?php echo U('tickLi');?>",
            data: {'all': 'd'},
            method: "POST",
            dataType: "HTML",
            success: function (response) {
                $('#list').html(response)
                $('#sb div').removeClass('on')
                $('#all').addClass('on')
            }
        })
    })
    $('#searchb').click(function () {
        address = $('#hotelSearchInp').val();
        bTime = $('#hotelBeginTime').val();
        eTime = $('#hotelEndTime').val();
        Inp = $('#allSearchInp').val();
        $.ajax({
            url: "<?php echo U('tickLi');?>",
            data: {'ci': address, 'bTime': bTime, 'eTime': eTime, "spot": Inp},
            method: "POST",
            dataType: "HTML",
            success: function (response) {
                $('#list').html(response)
            }
        })
    })

    $('#a').click(function () {
        address = $('#hotelSearchInp').val();
        bTime = $('#hotelBeginTime').val();
        eTime = $('#hotelEndTime').val();
        Inp = $('#allSearchInp').val();
        $.ajax({
            url: "<?php echo U('tickLi');?>",
            data: {'ci': address, 'bTime': bTime, 'eTime': eTime, "spot": Inp, 'one': '100'},
            method: "POST",
            dataType: "HTML",
            success: function (response) {
                $('#list').html(response)
                $('#pu li').removeClass('on')
                $('#a').addClass('on')
            }
        })
    })
    $('#b').click(function () {
        address = $('#hotelSearchInp').val();
        bTime = $('#hotelBeginTime').val();
        eTime = $('#hotelEndTime').val();
        Inp = $('#allSearchInp').val();
        $.ajax({
            url: "<?php echo U('tickLi');?>",
            data: {'ci': address, 'bTime': bTime, 'eTime': eTime, "spot": Inp, 'two': '100'},
            method: "POST",
            dataType: "HTML",
            success: function (response) {
                $('#list').html(response)
                $('#pu li').removeClass('on')
                $('#b').addClass('on')
            }
        })
    })
    $('#c').click(function () {
        address = $('#hotelSearchInp').val();
        bTime = $('#hotelBeginTime').val();
        eTime = $('#hotelEndTime').val();
        Inp = $('#allSearchInp').val();
        $.ajax({
            url: "<?php echo U('tickLi');?>",
            data: {'ci': address, 'bTime': bTime, 'eTime': eTime, "spot": Inp, 'three': '100'},
            method: "POST",
            dataType: "HTML",
            success: function (response) {
                $('#list').html(response)
                $('#pu li').removeClass('on')
                $('#c').addClass('on')
            }
        })
    })
    $('#d').click(function () {
        address = $('#hotelSearchInp').val();
        bTime = $('#hotelBeginTime').val();
        eTime = $('#hotelEndTime').val();
        Inp = $('#allSearchInp').val();
        $.ajax({
            url: "<?php echo U('tickLi');?>",
            data: {'ci': address, 'bTime': bTime, 'eTime': eTime, "spot": Inp, 'four': '100'},
            method: "POST",
            dataType: "HTML",
            success: function (response) {
                $('#list').html(response)
                $('#pu li').removeClass('on')
                $('#d').addClass('on')
            }
        })
    })
    $('#f').click(function () {
        address = $('#hotelSearchInp').val();
        bTime = $('#hotelBeginTime').val();
        eTime = $('#hotelEndTime').val();
        Inp = $('#allSearchInp').val();
        $.ajax({
            url: "<?php echo U('tickLi');?>",
            data: {'ci': address, 'bTime': bTime, 'eTime': eTime, "spot": Inp},
            method: "POST",
            dataType: "HTML",
            success: function (response) {
                $('#list').html(response)
                $('#pu li').removeClass('on')
                $('#f').addClass('on')
            }
        })
    })

    $('#priceBtn').click(function () {
        address = $('#hotelSearchInp').val();
        bTime = $('#hotelBeginTime').val();
        eTime = $('#hotelEndTime').val();
        Inp = $('#allSearchInp').val();
        bp = $('#bp').val();
        ep = $('#ep').val();
        if (bp == '' || ep == '') {
            alert('请填写完整区间')
        }
        $.ajax({
            url: "<?php echo U('tickLi');?>",
            data: {'ci': address, 'bTime': bTime, 'eTime': eTime, "spot": Inp, "bp": bp, "ep": ep},
            method: "POST",
            dataType: "HTML",
            success: function (response) {
                $('#list').html(response)
                $('#pu li').removeClass('on')
            }
        })
    })
</script>
</html>