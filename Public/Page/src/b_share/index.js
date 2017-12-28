require('./index.scss');

var clickFile = $('#clickFile'),
    file = $('#file'),
    readImg = $('#readImg'),
    code = $('#code'),
    reader = new FileReader(),
    imgUrl = 'http://www.suiyiyou.net/index.php/Page/Share/imgGet',
    submit = $('#submit'); 

clickFile.click(function(){
    file.click();
});

file.change(function(){
    reader.readAsDataURL(this.files[0])
    reader.onload = function(e){  
        readImg.attr('src',this.result);
    }  
});

submit.click(function(){
    var codeId = code.val();
    if(codeId == ''){
        alert('请输入产品id！');
        return;
    }
    var postData = new FormData($('#formData')[0]);
    $.ajax({
        url:imgUrl,
        type:"post",
        data:postData,
        cache: false,
        processData: false,  
        contentType: false,
        success: function (res) {  
            if(res.code == 200){
                alert('上传成功');
            }else{
                alert(res.msg);
            }
        }   
    });
});
