var warning = function( text , callback ){

    var _this = this;

    this.text = text || '哪里出了点问题!';
    this.bg = $('<div></div>');
    this.box = $('<div></div>');
    this.p = $('<p></p>');
    this.btn = $('<p>知道了</p>');

    this.p.html(this.text);

    this.bg.css({
        position : 'fixed',
        top : '0',
        left : '0',
        width : '100%',
        height : '100%',
        background : 'rgba(0,0,0,0.1)',
        zIndex: '99'
    });

    this.box.css({
        width: '90%',
        height: '100px',
        background: '#fff',
        textAlign: 'center',
        margin: '65% auto 0',
        position: 'relative',
        borderRadius: '5px',
        border: '1px solid #3385ff',
        opacity: '0'
    })

    this.p.css({
        fontSize: '14px',
        color: '#777',
        padding: '0 5px',
        lineHeight: '70px'
    })

    this.btn.css({
        width: '100%',
        height: '30px',
        lineHeight : '30px',
        fontSize: '15px',
        color: '#fff',
        textAlign: 'center',
        position: 'absolute',
        bottom: '0',
        borderTop: '1px solid #3385ff',
        boxSizing: 'border-box',
        background: 'rgb(131,176,243)'
    })

    this.box.append(this.p,this.btn);
    this.bg.append(this.box);
    $('body').append(this.bg);
    this.box.animate({opacity: '1'},300);

    this.btn.click(function(){
        _this.box.animate({opacity: '0'},300,function(){
            _this.bg.remove();
        });
        if(callback) callback();
    });
}

if (typeof module !== 'undefined' && typeof exports === 'object') {
    module.exports = warning;
} else {
    window.warning = warning;
}
