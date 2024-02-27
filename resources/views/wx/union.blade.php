<!DOCTYPE html>
<html lang="zh-cn"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>订单付款</title>
    <link href="/assets/bootstrap.min.css" rel="stylesheet">
    <style>
        .table td, .table th {
            vertical-align: middle;
        }
        .layui-layer-btn .layui-layer-btn0{
            color: white !important;
        }
    </style>
<body class="bg-warning" style="background: rgb(19, 169, 214) !important;">
<div style="width: 100%; display: none; position: absolute; top: 0; z-index: 9999; background: #10a1fc; height: 100vh;" id='showTop'>
    <div class="wrapper" style="margin:0 auto;margin-top: 200px !important;width:80%">
        <div class="load-bar" style="width: 100%; height: 10px;border-radius: 30px; background-color: #D9D9D9;position: relative; box-shadow: 0 1px 0 rgba(255, 255, 255, 0.8), inset 0 2px 3px rgba(0, 0, 0, 0.2);">
            <div class="load-bar-inner" data-loading="0" style="height: 99%; width: 0%;border-radius: inherit; position: relative;background-color: #0096F5; animation: loader 10s linear infinite; -moz-animation: loader 10s linear infinite; -webkit-animation: loader 10s linear infinite;">
                <!--<span id="counter">0%</span>-->
            </div>
        </div>
        <p style="width: 100%;text-align: center;color: white;">请等待... </p>
    </div>
</div>
<div class="container mt-3">
    <div class="row">
        <div class="col-sm-6 offset-sm-3">
            <div class="row">
                <div class="col-sm-12 text-center">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="mb-2">
                                <img src="/zgyl.png" height="50">
                                <img src="/ysf.png" height="50">
                            </div>
                            <div class="mb-2" id="show" style="margin-top: 40px;margin-bottom: 30px !important;">
                                <h3 id="done" style="color:green;display:none;">订单已完成</h3>
                                <h3 id="fail" style="color:red;display:none;">订单失效;请重新下单</h3>
                                <div id="code" style="width: 200px;height: 200px;margin: 0 auto;line-height: 150px;">

                                </div>
                            </div>
                            <p class="small text-danger mb-3" style="margin-bottom: 20px !important;font-size: 20px;">
                                <span>订单金额</span>
                                <strong class="h5" style="font-size: 24px">{{$amount}}</strong>
                                <span>元</span>
                            </p>
                            <p class="small text-left text-primary" style="font-size: 16px">

                            <p style="text-align: left;color: #007bff"> 1.电脑PC浏览器打开当前网页的话，请使用<span style="color: red">手机银行</span>或<span style="color: red">云闪付APP</span><span style="color: red">扫一扫</span>扫描二维码。<br></p>
                            <p style="text-align: left;color: #007bff">   2.手机浏览器请截图二维码保存到相册，使用微信<span style="color: red">扫一扫</span>相册，进行扫码支付。<br></p>
                            <!--<p style="text-align: left;color: #007bff">  3.如遇提示"<span style="color: red">风险交易</span>";请"<span style="color: red">申请解除风险</span>"。再次识别扫码完成付款。<br></p>-->
                            </p>
                        </div>
                    </div>
                    <div class="card" style="margin-top: 16px;">
                        <div class="card-body">
                            <div class="mb-2 text-left text-secondary small">
                                <p>
                                    <strong>注意事项</strong><br>
                                    <span>1.当前页面仅限充值一次，请勿重复充值;</span><br>
                                    <span>2.转账完成后，系统将自动充值到您的账户;</span><br>
{{--                                <span>3.修改金额、超时、重复充值将导致充值不到账;</span><br>--}}
                                    <span>3.如果充值5分钟未到账，请联系客服。</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/assets/qr.js"></script>
<script src="/assets/jquery.min.js"></script>
<script src="/assets/layer/layer.js"></script> 
<script src="/assets//bootstrap.min.js"></script>
<script type="text/javascript">
    // layer.alert('<span style="font-size:20px;color:green">如遇提示"<span style="color: red">风险交易</span>";请"<span style="color: red">申请解除风险</span>"。再次识别扫码完成付款。</span>', {btn:['我知道了']});
    //, time:3000
    //订单监控  {订单监控}
    function order(){
        $.post('/check_status',
            {order:'{{$order}}'},
            function(result) {
                if(result.code == 200){
                    $("#code").css("display", 'none');
                    $("#done").css("display", 'block');
                    window.clearInterval(orderlst);
                }

                if(result.code == 500){
                    $("#code").css("display", 'none');
                    $("#fail").css("display", 'block');
                    window.clearInterval(orderlst);
                }

            });
    }

// order();

    //周期监听
    var orderlst = setInterval("order()", 60000);

    // var intervals =  setInterval(function(){
    //     console.log(1)
    // },1000);

    // pay



    $(function(){
        var qrcode = new QRCode(document.getElementById("code"), {
            text:  "{{ $url }}",
            width: 200,
            height: 200,
            correctLevel : 0 // 二维码结构复杂性 0~3
        });

    });

</script>




</body>
</html>
