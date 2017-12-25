var Confirm = function( text , callback ){

    var _this = this;

    this.text = text || '';
    this.bg = $('<div></div>');
    this.box = $('<div></div>');
    this.p = $('<p></p>');
    this.btnBox = $('<div></div>');
    this.ybtn = $('<p>确定</p>');
    this.nbtn = $('<p>取消</p>');

    this.p.html(this.text);

    this.bg.css({
        position : 'fixed',
        top : '0',
        left : '0',
        width : '100%',
        height : '100%',
        background : 'rgba(0,0,0,0.7)',
        zIndex: '99'
    });

    this.box.css({
        width: '90%',
        height: '100px',
        background: '#fff',
        margin: '65% auto 0',
        position: 'relative',
        borderRadius: '5px',
        border: '1px solid #3385ff',
        opacity: '0'
    })

    this.p.css({
        fontSize: '14px',
        color: '#777',
        padding: '8px',
    })

    this.btnBox.css({
        width: '100%',
        height: '30px',
        position: 'absolute',
        bottom: '0',
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center'
    })

    this.ybtn.css({
        width: '50%',
        height: '30px',
        lineHeight : '30px',
        fontSize: '15px',
        color: '#fff',
        textAlign: 'center',
        borderTop: '1px solid #3385ff',
        boxSizing: 'border-box',
        background: 'rgb(131,176,243)'
    })

    this.nbtn.css({
        width: '50%',
        height: '30px',
        lineHeight : '30px',
        fontSize: '15px',
        color: '#3385ff',
        textAlign: 'center',
        borderTop: '1px solid #3385ff',
        boxSizing: 'border-box',
        background: 'rgb(255,255,255)'
    })

    this.btnBox.append(this.ybtn,this.nbtn);
    this.box.append(this.p,this.btnBox);
    this.bg.append(this.box);
    $('body').append(this.bg);
    this.box.animate({opacity: '1'},300);

    this.ybtn.click(function(){
        _this.box.animate({opacity: '0'},300,function(){
            _this.bg.remove();
            if(callback) callback();
        });
    });

    this.nbtn.click(function(){
        _this.box.animate({opacity: '0'},300,function(){
            _this.bg.remove();
        });
    })
}

if (typeof module !== 'undefined' && typeof exports === 'object') {
    module.exports = Confirm;
} else {
    window.Confirm = Confirm;
}
