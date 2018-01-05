//需要页面上有 window.controller = '__CONTROLLER__';
function menu() {
    //tab链接
    var menuArr = [
        {
            src: controller + '/home',
            icon: 'icon-home',
            name: '首页'
        }, {
            src: controller + '/order',
            icon: 'icon-dingdan',
            name: '订单'
        }, {
            src: controller + '/share_poster',
            icon: 'icon-earth',
            name: '推广'
        }, {
            src: controller + '/s_route',
            icon: 'icon-xianlu',
            name: '线路'
        }, {
            src: controller + '/s_ticket',
            icon: 'icon-menpiao',
            name: '门票'
        }
    ]

    var box = $('<div id="bottomMenu"></div>');
    box.css({
        position: 'fixed',
        width: '100%',
        height: '54px',
        bottom: '0',
        background: '#fff',
        borderTop: '1px solid #cecece'
    })

    var html = '';
    for (var i = 0; i < menuArr.length; i++) {
        html += '<a href=' + menuArr[i].src + '><i class="syy ' + menuArr[i].icon + '"></i><p>' + menuArr[i].name + '</p></a>';
    }

    box.html(html);
    $('body').css('paddingBottom', '54px').append(box);

    $('#bottomMenu i').css({
        fontSize: '24px',
        color: '#777',
        display: 'block',
        lineHeight: '38px'
    })

    $('#bottomMenu p').css({
        fontSize: '12px',
        color: '#777',
        textAlign: 'center',
        lineHeight: '16px'
    })

    $('#bottomMenu a').css({
        display: 'inline-block',
        textAlign: 'center',
        width: '20%',
        height: '54px',
        verticalAlign: 'middle'
    })
}


if (typeof module !== 'undefined' && typeof exports === 'object') {
    module.exports = new menu();
} else {
    window.menu = new menu();
}