function toast() {

    this.container = $('<div></div>');

    this.container.prop('id', 'loadingPc');

    var loading = $('<div></div>');
    this.container.prop('class', 'loading-box');

    var p = $('<p>请稍后</p>');

    var svg = $('<img src="http://www.suiyiyou.net/Public/common/loading.svg">');

    this.container.css({
        position: 'fixed',
        left: 0,
        top: 0,
        width: '100%',
        height: '100%',
        zIndex: 99,
        display: 'none'
    });

    loading.css({
        width: '100px',
        height: '100px',
        background: 'rgba(0, 0, 0, 0.5)',
        borderRadius: '10px',
        margin: '70% auto 0'
    });

    svg.css({
        margin: '0 auto',
        paddingTop: '10px',
        display: 'block'
    })

    p.css({
        textAlign: 'center',
        color: '#fff',
        fontSize: '14px',
        marginTop: '5px'
    })

    loading.append(svg, p);
    this.container.append(loading);
    $('body').append(this.container);

    this.show = function () {
        this.container.show();
    };

    this.hide = function () {
        this.container.hide();
    }
}

if (typeof module !== 'undefined' && typeof exports === 'object') {
    module.exports = new toast();
} else {
    window.toast = new toast();
}