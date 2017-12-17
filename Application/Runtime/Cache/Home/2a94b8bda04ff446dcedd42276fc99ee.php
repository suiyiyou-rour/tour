<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>随意游</title>
    <link rel="stylesheet" href="/Public/Page/dist/home.min.css">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_469978_0ku5qokkalb4kj4i.css">
    <link rel="icon" type="image/jpg" href="/Public/Page/image/logo-icon.jpg">
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
        <a style="text-decoration:none;" href="<?php echo U('index');?>">
            <div class="nav-item-on">首页</div>
        </a>
        <a style="text-decoration:none;" href="<?php echo U('groupList');?>">
            <div class="nav-item">跟团游</div>
        </a>
        <a style="text-decoration:none;" href="<?php echo U('seceneyList');?>">
            <div class="nav-item">景酒套餐</div>
        </a>
        <a style="text-decoration:none;" href="<?php echo U('tickList');?>">
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

    <div id="searchBox" >
        <div style="margin-left:30px;" class="item on">
            全部
        </div>
        <div class="item">
            跟团游
        </div>
        <div class="item">
            景酒套餐
        </div>
        <div class="item">
            景区门票
        </div>
        <div class="item-box" >
            <input class="seach-inp" type="text" id="allSearchInp" placeholder="请输入关键字搜索">
            <button class="search-btn" id="allSearchBtn"><i class="syy icon-sousuo"></i></button>
        </div>
        <div class="item-box" style="display:none">
            <input class="seach-inp" type="text" id="routeSearchInp" placeholder="我想去...">
            <div class="date-box">
                <input type="text" readonly class="seach-inp datepicker-here" id="routeBeginTime">
                <i class="syy icon-rl"></i>
            </div>
            <div class="date-box">
                <input type="text" readonly class="seach-inp datepicker-here" id="routeEndTime">
                <i class="syy icon-rl"></i>
            </div>
            <button id="routeSearchBtn" class="search-btn"><i class="syy icon-sousuo"></i></button>
        </div>
    </div>
</div>

<div id="content">
    <div class="title">
        <img class="line" src="/Public/Page/image/ltt.jpg" alt="">
        <h1>跟团游</h1>
        <img class="line" src="/Public/Page/image/ltt.jpg" alt="">
    </div>
    <div class="content-box">
        <?php if(is_array($group)): $i = 0; $__LIST__ = $group;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$g): $mod = ($i % 2 );++$i;?><div class="content-item">
                <img class="pic" src="<?php echo ($g["imgFile"]); ?>" alt="">
                <div class="mess">
                    <p class="name"><?php echo ($g["g_name"]); ?></p>
                    <p class="detail">
                        出发地：<?php echo ($g["g_go_address"]); ?><br>
                        目的地：<?php echo ($g["g_e_address"]); ?>
                    </p>
                    <p class="price-box">
                        售价 : ￥<span class="price"><?php echo ($g["price"]); ?></span>
                    </p>
                </div>
                <button class="now-reserve" code="<?php echo ($g["g_code"]); ?>" type="g_code">立即预定</button>
            </div><?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
    <div class="title">
        <img class="line" src="/Public/Page/image/ltt.jpg" alt="">
        <h1>景酒套餐</h1>
        <img class="line" src="/Public/Page/image/ltt.jpg" alt="">
    </div>
    <div class="content-box">
        <?php if(is_array($seceny)): $i = 0; $__LIST__ = $seceny;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><div class="content-item">
                <img class="pic" src="<?php echo ($v["imgfile"]); ?>" alt="">
                <div class="mess">
                    <p class="name"><?php echo ($v["s_name"]); ?></p>
                    <p class="detail">
                        <?php if(is_array($v[s_tj_ly])): $i = 0; $__LIST__ = $v[s_tj_ly];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$st): $mod = ($i % 2 );++$i; echo ($st["val"]); endforeach; endif; else: echo "" ;endif; ?>
                    </p>
                    <p class="price-box">
                        售价 : ￥<span class="price"><?php echo ($v["my_price"]); ?></span>
                    </p>
                </div>
                <button class="now-reserve" code="<?php echo ($v["s_code"]); ?>" type="s_code">立即预定</button>
            </div><?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
    <div class="title">
        <img class="line" src="/Public/Page/image/ltt.jpg" alt="">
        <h1>景区门票</h1>
        <img class="line" src="/Public/Page/image/ltt.jpg" alt="">
    </div>
    <div class="content-box">
        <?php if(is_array($tick)): $i = 0; $__LIST__ = $tick;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$t): $mod = ($i % 2 );++$i;?><div class="content-item">
                <img class="pic" src="<?php echo ($t["imgFile"]); ?>" alt="">
                <div class="mess">
                    <p class="name"><?php echo ($t["t_tick_name"]); ?></p>
                    <p class="detail">
                        景点地点： <?php echo ($t["tick_spot"]); ?></br>
                        景点城市：<?php echo ($t["t_tick_city"]); ?></p>
                    <p class="price-box">
                        售价 : ￥<span class="price"><?php echo ($t["t_tick_my_price"]); ?></span>
                    </p>
                </div>
                <button class="now-reserve" code="<?php echo ($t["t_code"]); ?>" type="t_code">立即预定</button>
            </div><?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
</div>

<footer id="footer">
    <div class="footer-content">
        <img class="bottom-logo" src="/Public/Page/image/bottom.png" alt="" width="150">
    </div>
</footer>

</body>
<script src="/Public/common/jquery-2.1.1.min.js"></script>
<script src="/Public/Page/dist/home.min.js"></script>

</html>