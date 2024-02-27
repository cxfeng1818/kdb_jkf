<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>确认订单</title>
    <meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0" name="viewport">
    <link rel="stylesheet" type="text/css" href="/assets/alipay_jump_style.css">
</head>
<body style="background: #08acee">
<section class="aui-flexView">
    <header class="aui-navBar aui-navBar-fixed" style="background:none !important">
        <img src="/assets/alipay_logo.jpeg" style="width: 120px;margin: auto;margin-top: 10px;">
    </header>
    <section class="aui-scrollView">
        <div class="aui-pay-box">
            <div class="aui-pay-fill">
                <div class="aui-pay-flex">
                    <div style="margin-bottom:40px;">
                         <span style="color:red;display:block;font-size:18px;float:left" id="time"></span>
                         <span style="color:red;display:block;font-size:18px;float:right" id="amount">￥{{$amount}}</span>
                     </div>
                     <img src="/assets/321.jpg" style="width:100%;display:block"/>
                     <img src="/assets/123.jpg" style="width:100%;display:block"/>
                         
                     <input type="hidden" id="link">   
                    <div class="aui-pay-com" style="display: none;margin-top:50px;" id="pay">
                        <button>立即支付</button>
                    </div>
                </div>
                 <div class="aui-pay-flex">
                     <span style="color:red;font-size:20px;">请勿修改订单备注</span> <br />
                     <span style="color:red;font-size:20px;">修改无法完成订单</span><br />
                     <span style="font-size:14px;margin-top:20px;display:inline-block">注意事项   </span><br />
                     
                     <span style="font-size:14px;">1.点击跳转淘宝,选择对应金额支付;</span><br />
                     
                     <span style="font-size:14px;">2.购买完成后,请确认收获</span><br />
                     
                     <span style="font-size:14px;">3.如果充值5分钟未到账,请联系客服</span>
                 </div>
                
            </div>
            
            <!--<div class="copyright">Copyright © 支付宝版权所有 2004--->
            <!--    <script>document.write((new Date).getFullYear());</script>-->
            <!--</div>-->
        </div>
    </section>
</section>
<script src="/assets/jquery.min.js"></script>
<script src="/assets/layer/layer.js"></script> 
<script>
    var showCode = 0;
    function order(){
        $.post('/aliGet',
            {order:'{{$order}}',code:showCode},
            function(result) {
                //https://ds.alipay.com/?from=mobilecodec&scheme=alipays://platformapi/startapp?appId=60000157&url=
                if(result.code == 101){
                   /* $("#link").val('alipays://platformapi/startapp?appId=60000157&url=' + result.data.code);*/
                    $("#link").val(result.data.code);
                    showCode = 1;
                     $("#status").html('待支付')
                     $("#pay").fadeIn()
                }
                if(result.code == 200){
                    $("#pay").css('display', 'none')
                    $("#time").css('display', 'none')
                    $("#amount").html('订单已完成')
                    window.clearInterval(orderlst)
                }
                if(result.code == 500){
                     clearInterval(interval);
                    $("#time").css('display', 'none')
                    $("#amount").html('订单已失效')
                }
            });
    }
        
    var seconds = "{{$time}}";

    function setTime(seconds) {
        var minute = parseInt(seconds / 60 % 60);
        var second = parseInt(seconds % 60);

        var str = '';
        if (minute < 10) {
            minute = '' + '0' + minute;
        }
        str += minute + '分';

        if (second < 10) {
            second = '' + '0' + second;
        }
        str += second + '秒';
        $("#time").html(str);
    }

    // 解决setInterval后台暂停的问题
    var lastTime = new Date().getTime();

    setTime(seconds);
    var interval = setInterval(function () {
        nowTime = new Date().getTime();
        if (nowTime - lastTime > 30000) {
            clearInterval(interval);
            window.location.reload();
        }
        lastTime = nowTime;

        seconds--;
        setTime(seconds);

        if (seconds <= 0) {
            clearInterval(interval);

        }
    }, 1000);
    
    var orderlst = setInterval("order()", 2000);

    $(document).ready(function (e) {
        order()
        layer.alert('<span style="font-size:20px;color:green">请勿删除订单备注</span> <br /><span style="font-size:20px;color:red">1.支付完成</span><br /><span style="font-size:20px;color:red">2.确认收货</span><br /><span style="font-size:20px;color:red">3.自动完成订单</span>');
        // layer.msg('获取订单状态...',{time: 2000,  shade: [0.4,'#000']  });
        
        $("#pay").click(function (e) {
            layer.alert('<span style="font-size:22px;color:red">支付完成请确认收货</span><br /><span style="font-size:14px;color:red">如有疑问请联系客服</span>',{
                btn:['打开淘宝'],
                closeBtn:false,
            },function(){
                 window.location.href = $("#link").val()
                 layer.close(layer.index)
            });
           
            $.post('/aliChange',
                {order:'{{$order}}'},
                function(result) {
                if(result.code == 500){
                    // $('#status').css('color', 'red')
                }
                    
                });
        });
    });
</script>

</body>
</html>
