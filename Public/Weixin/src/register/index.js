require('./index.scss');
var Alert = require('COMMON/Alert-mb.js');

var mobile,
    validCode,
    psd,
    rePsd;

//读URL参数
function urlKey(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return encodeURI(r[2]); return null;
}
    

$('#getCodeBtn').click(function(){

    mobile = $('#mobileInp').val();
    if (mobile) {
        if (!(/^1\d{10}$/.test(mobile))) {
            Alert("手机号码有误，请重填");
            return false;
        }
    } else {
        Alert('请填写手机号码');
        return false;
    }

    $.ajax({
        url : 'http://www.suiyiyou.net/index.php/Weixin/Sms/useSendSms',
        type : 'POST',
        data : {
            mobile : mobile
        },
        success : function(res){
            var code = res.code;
            if(code == 200){
                Alert('请等待验证码短信');
            }
        }
    })

    var _this = $(this),
        time = 60;

    _this.prop('disabled',true).css('background','#cecece');
    _this.html('60秒后重发');

    var timer = setInterval(function(){

        time -- ;
        _this.html(time + '秒后重发');
        
        if(time == 0){
            clearInterval(timer);

            _this.prop('disabled',false).css('background','#3385ff');
            _this.html('重新发送');
        }

    },1000);
})


$('#bindAndLoginBtn').click(function(){
    
    mobile = $('#mobileInp').val();
    if (mobile) {
        if (!(/^1\d{10}$/.test(mobile))) {
            Alert("手机号码有误，请重填");
            return false;
        }
    } else {
        Alert('请填写手机号码');
        return false;
    } 

    psd = $('#psd').val();
    if(psd){
        if(psd.length < 6){
            Alert('密码不小于6位数');
            return false;
        }
    }else{
        Alert('请输入密码');
        return false;
    }

    rePsd = $('#rePsd').val();
    if(rePsd){
        if(psd != rePsd){
            Alert('两次密码不一致');
            return false;
        }
    }else{
        Alert('请再次输入密码');
        return false;
    }

    validCode = $('#codeInp').val();
    if (validCode) {
        if (!(/^\d{6}$/.test(validCode))) {
            Alert("验证码有误，请重填");
            return false;
        }
    } else {
        Alert('请填写验证码');
        return false;
    }

    $.ajax({
        url : 'http://www.suiyiyou.net/index.php/Weixin/BaseLogin/register',
        type : 'POST',
        data : {
            mobile : mobile,
            password : psd,
            verify : validCode
        },
        success : function(res){
            var code = res.code;
            if(code == 200){
                location.href = controller + '/home';
            }else{
                Alert(res.msg);
            }
        }
    })

})


function getCookie(name) {
    var arr = document.cookie.split('; ');
    var i = 0;
    for (i = 0; i < arr.length; i++) {
        var arr2 = arr[i].split('=');

        if (arr2[0] == name) {
            var getC = decodeURIComponent(arr2[1]);
            return getC;
        }
    }
    return '';
}

CONFIG.jsApiList = [    //需要调用的接口
    'onMenuShareTimeline',
    'onMenuShareAppMessage'
]
wx.config(CONFIG);
var link;
if(urlKey('pid')){
    link = location.href;
}else{
    link = location.href + '?pid=' + getCookie('pid');
}
wx.ready(function () {
    //分享朋友圈
    wx.onMenuShareTimeline({
        title: document.title,
        link: link,
        imgUrl: public + '/Weixin/image/wxbg.png'
    });
    //分享朋友
    wx.onMenuShareAppMessage({
        title: document.title,
        desc: '我的旅游小店，随我心，游天下',
        link: link,
        imgUrl: public + '/Weixin/image/wxbg.png',
    });
})