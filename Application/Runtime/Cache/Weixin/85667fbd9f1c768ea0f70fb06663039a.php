<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>不好意思</title>
    <style>
        .css_four{
            width: 60%;
            height: 200px;
            margin: 80px  auto 0 auto;
            background: url(/Public/Weixin/image/err.png) no-repeat;
            background-position: center;
        }
        .css_fonts{
            width: 80%;
            height: 200px;
            margin: 40px  auto 0 auto;
            text-align: center;
            font-weight: 600;
            color:#6B6854;
            font:20px/24px "Trebuchet MS", Arial, Helvetica, sans-serif;
        }
        #timeDown{
            font-size: 16px;
            padding-top: 30px;
            border-top: 1px dashed #3b3939;
        }
    </style>
</head>
<body>
    <div class="css_four"></div>
    <div class="css_fonts">
        <span ><?php echo ($str); ?></span>
        <p id="timeDown"></p>
    </div>
</body>
    <script>
    var timeDowd = document.getElementById('timeDown'),
        time = '',
        timeNum = 5;
    timeDowd.innerHTML = timeNum + '秒后跳转首页';
    time = setInterval(function(){
        timeNum--;
        if(timeNum <= 0){
             location.href = "<?php echo ($url); ?>";
        }
        timeDowd.innerHTML = timeNum + '秒后跳转首页';
    },1000);
    </script>
</html>