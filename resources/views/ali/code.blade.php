<!DOCTYPE html>
<html lang="zh-cn"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>充值页面</title>
    <link href="/assets/bootstrap.min.css" rel="stylesheet">
    <style> 
        .table td, .table th {
            vertical-align: middle;
        }
    </style>
<body class="bg-warning" style="background: rgb(19, 169, 214) !important;">

<div class="container mt-3">
    <div class="row">
        <div class="col-sm-6 offset-sm-3">
            <div class="row">
                <div class="col-sm-12 text-center">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="mb-2">
                                <img src="/assets/alipay_logo.jpeg" height="50">
                            </div>
                            <p class="small text-danger mb-3" style="margin-bottom: 20px !important;font-size: 18px;margin-top: 20px;">
                                <span>订单编号</span>
                                <strong class="h5" style="font-size: 18px">{{$order}}</strong>
                                <!--<span>元</span>-->
                            </p>
                            <p class="small text-danger mb-3" style="margin-bottom: 20px !important;font-size: 20px;margin-top: 20px;">
                                <span>订单金额</span>
                                <strong class="h5" style="font-size: 24px">{{$amount}}</strong>
                                <span>元</span>
                            </p>
                            
                            <p class="small text-danger mb-3" id="done" style="display:none;margin-bottom: 20px !important;font-size: 20px;margin-top: 20px;">
                                <strong class="h5" style="font-size: 24px;color:green">订单已支付</strong>
                            </p>
                            
                            <p class="small text-danger mb-3" id="fail" style="display:none;margin-bottom: 20px !important;font-size: 20px;margin-top: 20px;">
                                <strong class="h5" style="font-size: 24px;color:black;">订单已失效</strong>
                            </p>
                            
                            @if($mobile)
                                <div style="margin-bottom: 20px;" class="showData">
                                    <button class="btn btn-block btn-primary" style="width: 80%;height:2.8rem;margin: auto;" id="pay">立即支付</button>
                                    <input type="hidden" id="link" value="{{$link}}">
                                </div>
                            @else
                                <div class="mb-2 showData" id="show" style="margin-bottom: 30px !important;" >
                                    <div id="code" style="width: 200px;height: 200px;margin: 0 auto;line-height: 150px;">
    
                                    </div>
                                </div>
                            @endif

                            <p class="small text-left text-primary" style="font-size: 16px">
                            <p style="text-align: left;color: #007bff"> 1.电脑PC浏览器打开当前网页的话，请使用支付宝<span style="color: red">扫一扫</span>扫描二维码。<br></p>
                            <p style="text-align: left;color: #007bff">   2.手机浏览器请截图二维码保存到相册，使用支付宝<span style="color: red">扫一扫</span>相册，进行扫码支付。<br></p>
                            <p style="text-align: left;color: #007bff">  3.如遇提示"<span style="color: red">风险交易</span>";请"<span style="color: red">申请解除风险</span>"。再次识别扫码完成付款。<br></p>
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
                                    {{--                                    <span>3.修改金额、超时、重复充值将导致充值不到账;</span><br>--}}
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
<script src="/assets//bootstrap.min.js"></script>
<script type="text/javascript">
    var current = 0;
    var showCode = 0;
    //订单监控  {订单监控}
    function order(){
        $.get('/loadGet/{{$order}}',
            function(result) {
                if(result.code == '200'){
                    $(".showData").css('display', 'none');
                    $("#done").css('display', 'block')
                }
                if(result.code == '500'){
                    $(".showData").css('display', 'none');
                    $("#fail").css('display', 'block')
                }
            });
    }

    //周期监听
    var orderlst = setInterval("order()", 60000);

    // var intervals =  setInterval(function(){
    //     console.log(1)
    // },1000);

    // pay

    $(function () {
        
        
        var interval = setInterval(increment, 100);
        function increment() {
            current += 1;
            $('.load-bar-inner').css('width', current + '%');
            $('#counter').html(current + '%');
            if (current >= 100) {
                window.clearInterval(interval);
                // window.clearInterval(intervals);
                $("#showTop").css('display', 'none');
            }
        }
        
        @if($mobile)
            $("#pay").click(function(){
                window.open($("#link").val());
            });
        @else
             new QRCode(document.getElementById("code"), {
                        text:  "{{$link}}",
                        width: 200,
                        height: 200,
                        correctLevel : 0 // 二维码结构复杂性 0~3
                    });
        @endif
        
        
        
        
        

    });


</script>




</body>
</html>
