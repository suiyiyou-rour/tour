/**
 * 
 * {
 *  @param {*要插入的文本内容} content 
 *  @param {*关闭弹框的回调}   callback
 * }
 * 
 */

var dialog = function (json) {

    var title = title || '';
    var bg = $('<div></div>');
    var box = $('<div></div>');
    var btn = $('<p>返 回</p>');

    bg.css({
        background: 'rgba(0,0,0,0.2)',
        display: 'none',
        position: 'absolute',
        width: '100%',
        height: '100%',
        top: 0,
        left: 0
    });

    box.css({
        width: '100%',
        height: '100%',
        position: 'fixed',
        bottom: '-100%',
        backgroundColor: '#fff',
        borderTopLeftRadius: '20px',
        borderTopRightRadius: '20px',
        left: '0',
    });

    btn.css({
        width: '100%',
        color: '#3385ff',
        lineHeight: '40px',
        fontSize: '16px',
        position: 'absolute',
        bottom: 0,
        textAlign: 'center',
        margin: '0',
        padding: '0',
        borderTop: '1px solid #3385ff'
    });

    box.append(btn);
    bg.append(box);
    $('body').append(bg);

    if(json.content){
        box.append(json.content);
    }

    this.show = function(){
        bg.show();
        box.animate({'bottom':'0'},200);
    }

    this.hide = function(callback){
        box.animate({'bottom':'-100%'},200,function(){
            bg.hide();
        });
    }

    btn.click(function(){
        box.animate({'bottom':'-100%'},200,function(){
            bg.hide();

            if(json.callback){
                json.callback();
            }
        });
    })
}

if (typeof module !== 'undefined' && typeof exports === 'object') {
    module.exports = dialog;
} else {
    window.dialog = dialog;
}