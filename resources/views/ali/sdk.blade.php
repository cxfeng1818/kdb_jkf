
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="/assets/jquery.min.js"></script>
    <script src="/assets/js_sdk.js"></script>
    <script src="https://g.yimenyun.net/cdn/jsbridge-mini.js?v=20230831"></script>  
    <script src="https://unpkg.com/vconsole@latest/dist/vconsole.min.js"></script>
<script>
  // VConsole will be exported to `window.VConsole` by default.
  var vConsole = new window.VConsole();
</script>
    <!--<script src="https://gw.alipayobjects.com/as/g/h5-lib/alipayjsapi/3.1.1/alipayjsapi.min.js"></script>  -->
</head>
<body>
<button id="btn" class="orderPay">支付</button>
<script>
$(function(){
    $("#btn").click(function(){
            window.webkit.messageHandlers.alipay.postMessage("{{$data}}");
    })
    
    // function ready(callback) {
    //     console.log(window.AlipayJSBridge)
    //     // 如果jsbridge已经注⼊则直接调⽤
    //     if (window.AlipayJSBridge) {
    //         callback && callback();
    //     } else {
    //         // 如果没有注⼊则监听注⼊的事件
    //         document.addEventListener("AlipayJSBridgeReady", callback, false);
    //     }
    // }

    // ready(function(){
    //     document.querySelector('.orderPay').addEventListener('click', function() {
    //         try {
    //             AlipayJSBridge.call('tradePay', {
    //                 orderStr:"{{$data}}",
    //                 function(result){
    //                     alert(JSON.stringify(result))
    //                     console.log(JSON.stringify(result))
    //                 },

    //             })
    //         }catch (e) {
    //             alert(JSON.stringify(e))
    //         }

    //     })
    // })
})
    

    // $(function(){
    //     $("#btn").click(function(){
    //               window.AlipayJSBridge.call(
    //     "tradePay",
    //     {
    //       orderStr: ""
    //     },
    //     function(result) {
    //       console.log(result);
    //       console.log(thah.$router);
    //       console.log(
    //         thah.$router.replace("./recharge-result?rechargeResult=fail")
    //       );
    //       if (result.resultCode == 9000) {
    //         thah.$router.push("./recharge-result?rechargeResult=success");
    //       } else {
    //         thah.$router.replace("./recharge-result?rechargeResult=fail");
    //       }
    //     }
    //   );

    //         // window.postBridgeMessage("")
    //     })
    // })
</script>
</body>
</html>
