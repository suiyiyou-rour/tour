<?php if (!defined('THINK_PATH')) exit(); if(is_array($seceney)): $i = 0; $__LIST__ = $seceney;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><div class="content-item">
        <img class="pic" src="<?php echo ($v["imgfile"]); ?>" alt="">
        <div class="mess">
            <p class="name"><?php echo ($v["s_name"]); ?></p>
            <p class="detail">
                <?php if(is_array($v[s_tj_ly])): $i = 0; $__LIST__ = $v[s_tj_ly];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$st): $mod = ($i % 2 );++$i; echo ($st["val"]); endforeach; endif; else: echo "" ;endif; ?>
            </p>
            <p class="sales-volume">销量 :
                <span class="sales-num"><?php echo ($v["s_sell"]); ?></span>
            </p>
            <p class="price-box">
                售价 : ￥
                <span class="price"><?php echo ($v["my_price"]); ?></span> 起
            </p>
        </div>
        <a href = "<?php echo U('secenyDetail');?>"><button class="now-reserve">立即预定</button></a>
    </div><?php endforeach; endif; else: echo "" ;endif; ?>