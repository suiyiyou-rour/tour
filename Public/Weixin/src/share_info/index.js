require('./index.scss');
require('COMMON/bottom-menu.js');


var prompt = $('#prompt'),
    getJxsUrl = 'http://www.suiyiyou.net/index.php/Weixin/Share/login',
    getImageUrl = 'http://www.suiyiyou.net/index.php/Weixin/Share/getImageUrl',
    type = GetQueryString('type'),
    code = GetQueryString('code'),
    showPic = $('#showPic');

 // 请求是否分销商
 $.ajax({
    url: getJxsUrl,
    success: function (res) {
        if(res.code == 200){
            prompt.css('display','flex');
            return;
        }
        getImage();
    }
});

// 图片地址
function getImage(){
    $.ajax({
        url:getImageUrl,
        data:{'type':type,'code':code},
        success:function(res){
            switch(res.code){
                case 404:
                    prompt.css('display','flex');
                    break;
                case 403:
                    location.href = "http://www.suiyiyou.net/index.php/Weixin/Share/share_poster";
                    break;
                case 200:
                    showPic.attr('src',res.msg);
            }
        }
    });
}


// js获取get参数
function GetQueryString(name)
{
     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
     var r = window.location.search.substr(1).match(reg);
     if(r!=null)return  decodeURI(r[2]); return null;
}