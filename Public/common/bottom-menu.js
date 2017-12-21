//需要页面上有 window.controller = '__CONTROLLER__';
function menu() {
    //tab链接
    var menu = {
        home: controller + '/home',
        order: controller + '/order',
        route: controller + '/s_route',
        ticket: controller + '/s_ticket'
    }

    var box = $('<div id="bottomMenu"></div>');
    box.css({
        position: 'fixed',
        display: 'flex',
        justifyContent: 'space-around',
        alignItems: 'center',
        width: '100%',
        height: '36px',
        bottom: '0',
        background: '#fff',
        borderTop: '1px solid #cecece'
    })


    var html = '<a href=' + menu.home + '><i class="syy icon-home"></i></a>' +
        '<a href=' + menu.order + '><i class="syy icon-dingdan"></i></a>' +
        '<a href=' + menu.route + '><i class="syy icon-xianlu"></i></a>' +
        '<a href=' + menu.ticket + '><i class="syy icon-menpiao"></i></a>';
    
    box.html(html);
    $('body').css('paddingBottom','36px').append(box);

    $('#bottomMenu i').css({
        fontSize: '26px',
        color: '#777'
    })
}


if (typeof module !== 'undefined' && typeof exports === 'object') {
    module.exports = new menu();
} else {
    window.menu = new menu();
}