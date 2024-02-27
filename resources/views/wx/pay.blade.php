<script type="text/javascript">
function onBridgeReady() {
    WeixinJSBridge.invoke(
        'getBrandWCPayRequest', {
            'appId': "{{$param['appId']}}",
            'timeStamp': '<?php echo $param["timeStamp"]; ?>',
            'nonceStr': '<?php echo $param["nonceStr"]; ?>',
            'package': '<?php echo $param["package"]; ?>',
            'signType': '<?php echo $param["signType"]; ?>',
            'paySign': '<?php echo $param["paySign"]; ?>'
        },
        function (res) {
            if (res.err_msg == "get_brand_wcpay_request:ok") {
                // 支付成功后的处理逻辑
            }
        }
    );
}

if (typeof WeixinJSBridge == "undefined") {
    if (document.addEventListener) {
        document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
    } else if (document.attachEvent) {
        document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
        document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
    }
} else {
    onBridgeReady();
}

</script>