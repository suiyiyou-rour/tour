<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>分销商注册--随意游</title>

    <link rel="stylesheet" href="/Public/Page/dist/fregister.min.css">
    <link rel="stylesheet" href="http://at.alicdn.com/t/font_469978_jh7pzgqlg14i.css">
    <link rel="icon" type="image/jpg" href="/Public/Page/image/logo-icon.jpg">
</head>

<body>
    <div class="top">

    </div>
    <div>
        <img class="bg" src="/Public/Page/image/bg-login.png" alt="注册背景">
    </div>

    <form id="fregister" enctype="multipart/form-data" method="post">
        <div class="content">
            <div class="left">
                <div class="l-top">成为分销商</div>

                <div class="l-c">
                    <label class="lc-title">公司名称：</label>
                    <input type="text" class="lc-text lc-long" id="company" name="company" placeholder="请输入公司名称">
                </div>

                <div class="l-c">
                    <label class="lc-title">联系人：</label>
                    <input type="text" class="lc-text" id="name" name="name" placeholder="请输入您的名字">
                </div>

                <div class="l-c">
                    <label class="lc-title">邮箱：</label>
                    <input type="text" class="lc-text" id="email" name="email" placeholder="请输入邮箱">
                </div>

                <div class="l-c">
                    <label class="lc-title">传真号：</label>
                    <input type="text" class="lc-text" id="fax" name="fax" placeholder="请输入传真号">
                </div>

                <div class="l-c">
                    <label class="lc-title">城市：</label>
                    <select class="lc-select" id="city" name="city">
                        <option value="350000" class="lc-opt">福州</option>
                    </select>
                </div>
            </div>

            <div class="right">
                <div class="l-top"></div>
                <div>
                    <div class="l-top-tx">请上传营业执照。</div>
                    <div id="fileUploadContent">
                    </div>
                </div>
            </div>
        </div>
    </form>
</body>
<script src="/Public/common/jquery-2.1.1.min.js"></script>
<script src="/Public/Page/dist/fregister.min.js"></script>

</html>