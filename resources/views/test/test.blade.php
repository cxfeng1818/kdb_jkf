<!DOCTYPE html>
<!-- saved from url=(0055)http://www.fortis.best/Pay_Charges_index_mid_10005.html -->
<html lang="zh-CN"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>向商家付款</title>

    <meta content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport">
    <meta name="Keywords" content="">
    <meta name="Description" content="">
    <!-- Mobile Devices Support @begin -->
    <meta content="telephone=no, address=no" name="format-detection">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <!-- apple devices fullscreen -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <!-- Mobile Devices Support @end -->
    <link rel="stylesheet" href="/test/style.min.css" type="text/css">
    <link rel="stylesheet" href="/test/base.min.css">
    <style>
        a[target=_blank][title='站长统计']{
            display: none;
        }
    </style>
    <link rel="stylesheet" href="/test/layer.css" id="layui_layer_skinlayercss" style=""></head>
<body onselectstart="return true;" ondragstart="return false;" style="user-select: none;">
<script type="text/javascript">
    var LC={placeholder:"/Public/Wap/img/placeholder.png"};
</script>
<link rel="stylesheet" href="/test/payOrder.min.css">

<style>
    .layui-layer-content {
        max-width: 1.48rem!important;
    }
    .pay-gray{
        background: #e5e5e5!important;
    }
</style>


<section class="pay-container-box bg-white pay-simplified">
    <!-- 门店名称展示 -->
    <div class="shop-name-box">
        <i class="default-shop-icon"></i>
        <span class="shop-name-display single-overflow" id="shop-name-display"></span>
    </div>
    <!-- 金额输入（账单金额等） -->
    <div class="input-money-box">
        <div class="js-amount-input pay-amount-box display-flex flex-between-lr flex-horizontal-center s-open-keyboard" data-id="mainMoney">
            <label class="pay-money-desc" for="">金额</label>
            <span class="js-input-hint no-pay-amount hide">￥0.00</span>
            <span class="js-input-amount has-pay-amount">
                        <em>￥</em>
                        <em id="mainMoney"></em>
                        <em class="pay-money-cursor"></em>
                    </span>
        </div>
    </div>

    <!-- 支付方式 -->
    <div class="pay-way-box">
        @foreach($channel as $cn)
       <div class=" pay-way-item display-flex flex-between-lr flex-horizontal-center" id="wechatRadio" data-code="" data-id="{{$cn['encode']}}">
                        <span>
                            <i class=""></i>
                            <em class="pay-way-desc">{{$cn['name']}}</em>
                        </span>
        <i class="select-icon"></i>
        </div>
        @endforeach
    </div>

    <!--按钮 部分 start-->
    <div class="s-pay-btn" id="s-pay-btn">确认支付</div>
    <!--按钮 部分 end-->
</section>

<!-- 自定义键盘 start 加上x-mask-show显示-->
<div id="keyBoard" class="x-mask-box x-mask-show" data-id="mainMoney" style="z-index:9;background-color: rgba(0,0,0,0);height:auto;" v-cloak="">
    <div class="x-slide-box pop-up-show">
        <div class="x-key-board">
            <div class="row">
                <div class="item js-key" data-number="1">1</div>
                <div class="item js-key" data-number="4">4</div>
                <div class="item js-key" data-number="7">7</div>
                <div class="item js-key" data-number=".">.</div>
            </div>
            <div class="row" style="width: 50%">
                <div class="display-flex">
                    <div class="item js-key" style="width: 50%" data-number="2">2</div>
                    <div class="item js-key" style="width: 50%" data-number="3">3</div>
                </div>
                <div class="display-flex">
                    <div class="item js-key" style="width: 50%" data-number="5">5</div>
                    <div class="item js-key" style="width: 50%" data-number="6">6</div>
                </div>
                <div class="display-flex">
                    <div class="item js-key" style="width: 50%" data-number="8">8</div>
                    <div class="item js-key" style="width: 50%" data-number="9">9</div>
                </div>
                <div class="display-flex">
                    <div class="item js-key" data-number="0">0</div>
                    <div class="item js-key s-pack-key" data-number="down"><i class="keyboard-icon"></i></div>
                </div>
            </div>
            <div class="row">
                <div class="item no-border-right js-key x-key-del" data-number="×">
                    <i class="back-icon"></i>
                </div>
                <div class="item no-border-bottom no-border-right x-key-ok" data-number="ok" id="confirm_pay">
                    <span style="line-height: 1.2; font-size: .2rem;">确<br>认<br>支<br>付</span>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 自定义键盘 end -->
<script type="text/javascript" src="/test/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="/test/layer.js"></script>
<script type="text/javascript" src="/test/payOrderSimpfilied.min.js"></script>
<script type="text/javascript" src="/test/jweixin-1.0.0.js"></script>
<form action="" style="visibility: hidden" id="form1"  method="post">
    <input type="hidden" value="" id="money" name='amount'>
    <input type="hidden" value="" id="cid" name='cid'>
</form>
<script>
    var auto_wiping_zero = 1
    $('#s-pay-btn, #confirm_pay').click(function() {
          var money =  $('#mainMoney').html();
         $("#money").val(money);
         var cid = $(".active").attr('data-id');
         $("#cid").val(cid);
      
         if(money.length == 0){
             alert('请输入金额');
             return false;
         }
         if(typeof(cid) == 'undefined'){
             alert('请选择通道');
             return false;
         }
         $("#form1").submit();
    })
</script>
</body></html>