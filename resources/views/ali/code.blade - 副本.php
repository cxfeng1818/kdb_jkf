<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>支付宝转帐</title>
    <link href="/assets/bootstrap.min.css" rel="stylesheet">
    <style>
        .table td, .table th {
            vertical-align: middle;
        }

        #show {
            display: block;
            text-align: center;
            width: 100%;
        }
    </style>
    <meta name="__hash__" content="a7f55f8c2998c0e892ee07994c6c48a6_56fd7f61af88c16ee1a4cbd2439b4c12">
</head>
<body class="bg-warning" style="background: rgb(1, 169, 239) !important;">
<div style="width: 100%; display: block; position: absolute; top: 0; z-index: 9999; background: #10a1fc; height: 100vh;"
     id='showTop'>
    <div class="wrapper" style="margin:0 auto;margin-top: 200px !important;width:80%">
        <p style="width: 100%;text-align: center;color: white;font-size:1.6rem">正在获取支付订单中 </p>
        <div class="load-bar"
             style="width: 100%; height: 10px;border-radius: 30px; background-color: #D9D9D9;position: relative; box-shadow: 0 1px 0 rgba(255, 255, 255, 0.8), inset 0 2px 3px rgba(0, 0, 0, 0.2);">
            <div class="load-bar-inner" data-loading="0"
                 style="height: 99%; width: 0%;border-radius: inherit; position: relative;background-color: #0096F5; animation: loader 10s linear infinite; -moz-animation: loader 10s linear infinite; -webkit-animation: loader 10s linear infinite;">
                <!--<span id="counter">0%</span>-->
            </div>
        </div>

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
                                <img src="/assets/alipay_logo.jpeg" height="50">
                            </div>
                            <!--<div class="mb-2"><strong>支付宝转帐</strong></div>-->
                            <h5 id="" style="color:red;font-size:1.1rem">
                                注意:如遇提示风控,请截图保存至相册,打开扫一扫完成付款</h5>
                            <p class="small text-danger" style="color:black !important;font-size:1rem;margin-top:1rem;">
                                <span>请在</span>
                                <span id="timeout"></span>
                                <span>内支付</span>
                                <strong class="h5">1231</strong>
                                <span>元</span>
                            </p>

                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
<div class="mb-2" id="show">
    <div id="shoz" style="width: 150px;height: 150px;margin: 0 auto;line-height: 150px;text-align:center;">
        二维码加载中
    </div>
    <div id="qrcode"
         style="width: 180px;height: 180px;margin: 0 auto;line-height: 150px;text-align:center;margin-top:10%">

    </div>
</div>


<p class="small text-primary" style="display:block;width:40%;margin:0 auto;margin-top:15% !important">
    <button class="btn btn-block btn-primary" type="button" onclick="pay()" id="btnPay"
            style="display: none;height:2.8rem" disabled>二维码加载中
    </button>
</p>

<input type="hidden" id="urlShow" value="1231">


<script src="/assets/jquery.min.js"></script>
<script src="/assets/bootstrap.min.js"></script>
<script src="/assets/qrcode.min.js"></script>
<script type="text/javascript">


    // 倒计时
    var seconds = "150";

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

        $("#timeout").html(str);
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


    var updateQrImg = 0;

    //订单监控  {订单监控}
    function order() {
        $.post('/aliGet',
            {order: '{{$order}}'},
            function (result) {
                if (result.code == 302) {
                    window.clearInterval(orderlst);
                    window.clearInterval(interval);
                    $("#btnPay").remove();
                    $("#showTop").css('display', 'none');
                    $("#show").html('订单出码失败;请重新下单');
                }

                if (result.code == 200) {
                    window.clearInterval(orderlst);
                    window.clearInterval(interval);
                    $("#btnPay").remove();
                    $("#show").html('支付成功');
                }

                if (result.code == 100) {
                    $("#showTop").css('display', 'none');
                    $("#urlShow").val(result.data.name);
                    $("#shoz").css('display', 'none');
                    //$("#show").html();
                    var qrcode = new QRCode("qrcode", {
                        text: result.data.name,
                        width: 200,
                        height: 200,
                    });
                    // $("#show").html("<img src='"+result.data.qrcode+"' width='150px'>");
                    // $("#urlShow").val(result.data.name);
                    $("#btnPay").removeAttr('disabled');
                    $("#btnPay").html('立即支付');
                    $("#btnPay").fadeIn();
                }

            });
    }


    //周期监听
    var orderlst = setInterval("order()", 5000);

    // var intervals =  setInterval(function(){
    //     console.log(1)
    // },1000);

    // pay


    function pay() {
        var _alipayh5url = $("#urlShow").val();
        window.open(_alipayh5url);
        // window.location.href = _alipayh5url;
    }


    var Phone = '1231';
    if (Phone == '1') {
        $("#btnPay").css('display', 'block');
        var setInter = setInterval(function () {
            var _alipayh5url = $("#urlShow").val();
            console.log(_alipayh5url);
            if (_alipayh5url.length >= 1) {
                window.clearInterval(setInter);
                window.open(_alipayh5url);
            }
            // window.location.href = _alipayh5url;
        }, 500);
    }

    $(function () {
        var interval = setInterval(increment, 650);
        var current = 0;

        function increment() {
            current += 4;
            $('.load-bar-inner').css('width', current + '%');
            if (current >= 100) {
                window.clearInterval(interval);
                // window.clearInterval(intervals);
                $("#showTop").css('display', 'none');
            }
        }


    });

</script>
</body>
</html>
