require('./index.scss');
require('COMMON/bottom-menu.js');
var Alert = require('COMMON/Alert-mb.js');

var getJxsUrl = 'http://www.suiyiyou.net/index.php/Weixin/Share/login',
    getPosterUrl = 'http://www.suiyiyou.net/index.php/Weixin/Share/getPoster',
    htmlStr = '',
    posterShowDiv = $('#posterShowDiv'),
    prompt = $('#prompt'),
    btnClose = $('#btnClose');

//www.suiyiyou.net

getPoster();
$('.box').on('click', '.poster_img', function (e) {
    // 判断分销商
    var type = $(this).attr('type'),
        code = $(this).attr('code');

    $.ajax({
        url: getJxsUrl,
        success: function (res) {
            if(res.code != 200){
                prompt.css('display','flex');
                return;
            }
            location.href="http://www.suiyiyou.net/index.php/Weixin/index/share_info?code="+code+"&type="+type;
        }
    });
});

btnClose.click(function(){
    prompt.css('display','none');
});


function getPoster() {
    $.ajax({
        url: getPosterUrl,
        data: { 'token': 'syy' },
        success: function (res) {
            if (res.code != 200) {
                Alert(res.msg);
                return;
            }
            createHtml(res.msg)

        }
    });
}

function createHtml(dataArr) {
    var length = dataArr.length;
    if (length >= 0) {
        
        for(var i = 0;i<length;i++){
            htmlStr += ' <a href="#" class="to_detail">' +
                        '<div class="poster">' +
                            '<img class="poster_img" code="'+dataArr[i]['good_code']+'"  type="'+dataArr[i]['good_type']+'" src="http://www.suiyiyou.net/'+dataArr[i]['img_url']+'">' +
                        ' </div>'+
                    '</a>';
        }
        posterShowDiv.html(htmlStr);
    }
}