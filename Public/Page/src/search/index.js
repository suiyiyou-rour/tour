require('./index.scss');

var URL = 'http://www.suiyiyou.net/index.php/Weixin/index/',

    urlJson = {
        s_route: 'GroupList',      //跟团游
        s_hotel: 'SceneryList',    //酒店
        s_ticket: 'TickList'       //门票
    },

    hrefJson = {
        s_route: 'p_route',      //跟团游
        s_hotel: 'p_hotel',       //酒店
        s_ticket: 'p_ticket'       //门票
    },
    page = 1;

var lastKey = window.location.pathname.split('/').pop();  //url最后一个值


function AjaxGetList() {
    $.ajax({
        url: URL + urlJson[lastKey],
        data: {
            page: page
        },
        success: function (res) {
            var code = res.code,
                data = res.data;

            if (code == 200) {
                renderRoute(data);
            }
        }
    })
}

//数据渲染页面
function renderRoute(data) {

    var len = data.length,
        html = '';

    for (var i = 0; i < len; i++) {

        html += '<a href=' + CONTROLLER + '/' + hrefJson[lastKey] + '?code=' + data[i].code + '&type=' + data[i].shop_type + '><div class="content-item">' +
            '<img class="pic" src=' + data[i].imgFile + ' alt="">' +
            '<div class="mess">';

        if (lastKey == 's_ticket') {
            html += '<p class="name">' + data[i].name + '+' + data[i].t_tick_cat + '+' + data[i].t_tick_spot + '</p>';
        } else {
            html += '<p class="name">' + data[i].name + '</p>';
        }

        html += '<p class="sales-volume">销量 :' +
            '<span class="sales-num">' + data[i].sell + '</span>' +
            '</p>' +
            '<p class="price-box">' +
            '售价 : ￥' +
            '<span class="price">' + data[i].price + '</span> 起' +
            '</p>' +
            '</div>' +
            '<button class="now-reserve">立即预定</button>' +
            '</div></a>';
    }

    $('#contentBox').append(html);
}

AjaxGetList();