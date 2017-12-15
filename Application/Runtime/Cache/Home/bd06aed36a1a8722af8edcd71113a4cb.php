<?php if (!defined('THINK_PATH')) exit();?>
    <?php if(is_array($tick)): $i = 0; $__LIST__ = $tick;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$t): $mod = ($i % 2 );++$i;?><div class="content-item">
            <img class="pic" src="<?php echo ($t["imgFile"]); ?>" alt="">
            <div class="mess">
                <p class="name"><?php echo ($t["t_tick_name"]); ?></p>
                <p class="detail">
                    景点地点： <?php echo ($t["tick_spot"]); ?></br>
                    景点城市：<?php echo ($t["t_tick_city"]); ?>
                </p>
                <p class="sales-volume">销量 :
                    <span class="sales-num"><?php echo ($t["t_tick_sell"]); ?></span>
                </p>
                <p class="price-box">
                    售价 : ￥
                    <span class="price"><?php echo ($t["t_tick_my_price"]); ?></span> 起
                </p>
            </div>
            <button class="now-reserve">立即预定</button>
        </div><?php endforeach; endif; else: echo "" ;endif; ?>