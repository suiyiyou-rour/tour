var Alert = function(content) {
    var $box = $('<div></div>'),
        $i = $('<i class="iconfont icon-jinggao"></i>'),
        $span = $('<span></span>');

    $box.css({
        position: 'fixed',
        textAlign: 'center',
        width: '180px',
        bottom: '30px',
        left: 'calc(50% - 90px)',
        fontSize: '12px',
        borderRadius: '3px',
        color: '#fff',
        opacity: '0.1',
        background: 'rgba(0,0,0,0.7)'
    });

    $span.css({
        lineHeight: '25px',
    })

    $span.append(content);
    $box.append($span);
    $('body').append($box);
    $box.animate({
        opacity: '1',
        bottom: '50px'
    }, 200, function () {
        setTimeout(function () {
            $box.animate({
                opacity: '0.1',
                bottom: '30px'
            }, function () {
                $box.remove();
            })
        }, 1500)
    })
}

if (typeof module !== 'undefined' && typeof exports === 'object') {
    module.exports = Alert;
} else {
    window.datePrice = Alert;
}

