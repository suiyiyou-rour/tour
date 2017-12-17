<?php if (!defined('THINK_PATH')) exit(); if(is_array($group)): $i = 0; $__LIST__ = $group;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$g): $mod = ($i % 2 );++$i;?><div class="content-item">
        <img class="pic" src="<?php echo ($g["imgFile"]); ?>" alt="">
        <div class="mess">
            <p class="name"><?php echo ($g["g_name"]); ?></p>
            <p class="detail">
                出发地：<?php echo ($g["g_go_address"]); ?><br>
                目的地：<?php echo ($g["g_e_address"]); ?>
            </p>
            <p class="sales-volume">销量 :
                <span class="sales-num"><?php echo ($g["g_sell"]); ?></span>
            </p>
            <p class="price-box">
                售价 : ￥
                <span class="price"><?php echo ($g["price"]); ?></span> 起
            </p>
        </div>
        <button class="now-reserve">立即预定</button>
    </div><?php endforeach; endif; else: echo "" ;endif; ?>