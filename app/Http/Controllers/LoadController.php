<?php

namespace App\Http\Controllers;

use App\Lib\HnPay\commands\WSScanPayProcess;
use Illuminate\Support\Facades\Log;
use App\Lib\HnPay\utils\ExpUtils;
use App\Models\Order;
use App\Models\Channel;
use App\Models\ChannelAccount;
use App\Models\User;
use App\Models\Black;

class LoadController extends BaseController
{
    protected $user;
    
    public function status($order = null)
    {
        if(empty($order)){
            exit("<h1>参数错误</h1>");
        }
        $order = Order::where('shop_order', $order)->first();
        if(empty($order)){
            exit("<h1>参数错误</h1>");
        }
        if($order['status'] == 'paid' || $order['status'] == 'success'){
              return $this->returnJson(200, '支付成功');
        }else if($order['status'] == 'paid' || $order['status'] == 'load_fail'){
              return $this->returnJson(500, '订单失效');
        }
    }
    
    public function backLink($param = '')
    {
        return $this->returnJson(200, '下单成功', [ 'url' => (request()->isSecure() ? 'https://' : 'http://').request()->server('HTTP_HOST').'/loadShow/'.$param['shop_order']]);
    }
    
    public function show($order = null)
    {
        if(empty($order)){
            exit("<h1>参数错误</h1>");
        }
        $order = Order::where('shop_order', $order)->first();
        if(empty($order)){
            exit("<h1>参数错误</h1>");
        }
        if($order['status'] == 'paid' || $order['status'] == 'success'){
            exit("<h1>订单已完成</h1>");
        }
        if($order['status'] == 'fail' || $order['status'] == 'load_fail'){
            exit("<h1>订单已失效;请重新下单</h1>");
        }
        if($order['status'] == 'black_ip' || $order['status'] == 'black_open'){
            exit("<h1>系统繁忙,请稍后再试</h1>");
        }
        $showClient = $this->is_Mobile() ? '手机端' : '电脑端';
        Log::debug($order['shop_order'].'客户端:'.$showClient);
        return view('ali.code')
             ->with('amount', $order['amount'])
             ->with("order",$order['shop_order'])
             ->with('mobile', $this->is_Mobile())
             ->with("link", 'https://'.request()->host().'/loadPay/'.$order['shop_order']);
    }
    
    public function pay($order = null)
    {
       if(empty($order)){
            exit("<h1>参数错误</h1>");
        }
        $order = Order::where('shop_order', $order)->first();
        if(empty($order)){
            exit("<h1>参数错误</h1>");
        }
        $findIp = Black::where('ip', request()->ip())->first();
        if($findIp){
            Order::where('id', $order['id'])->update([
                'status' => 'black_ip',
                'client_ip' => request()->ip(),
                'openid' => 'ip黑名单'
            ]);
            exit("<h1>系统繁忙.请稍后再试</h1>");
        }
        
        $topOrder = Channel::where('id', $order['cid'])->value('top_order');
        // if($topOrder != '0'){
            $minTime = time() - 60;
            $orderCount = Order::where('cid', $order['cid'])->whereIn('status', ['load', 'paid', 'success'])->where('created_at', '>=', $minTime)->count();
            if($orderCount >= $topOrder){
                  if(empty($order['openid'])){
                         Order::where('id', $order['id'])->update(['status' => 'load_fail', 'client_ip' => '通道并发限制']);
                         exit("<h1>系统繁忙,请稍后再试.</h1>");
                    }
            }
        // }
        $url = 'https://opendata.baidu.com/api.php?query='.request()->ip().'&co=&resource_id=6006&oe=utf8';
        $result = $this->httpGet($url);
        $result = json_decode($result, true);
        $location = $result['data'][0]['location'];
        $checkCn = $this->checkCn($location);
        if(!$checkCn){
            Order::where('id', $order['id'])->update([
                    'status' => 'fail',
                    'client_ip' => request()->ip(),
                    'openid' => $location
                ]);
            exit("<h1>系统繁忙;请稍后再试.</h1>");
        }else{
            Order::where('id', $order['id'])->update([
                     'client_ip' => request()->ip(),
            ]);   
        }
        
        
       
        if($order['status'] == 'paid' || $order['status'] == 'success'){
            exit("<h1>订单已完成</h1>");
        }
        if($order['status'] == 'fail' || $order['status'] == 'load_fail'){
            exit("<h1>订单已失效;请重新下单</h1>");
        }
        if($order['status'] == 'black_ip' || $order['status'] == 'black_open'){
            exit("<h1>系统繁忙,请稍后再试</h1>");
        }
        
        if(!empty($order['openid'])){
            if($order['client_ip'] != request()->ip()){
                exit("<h1>系统繁忙,请稍后再试</h1>");
            }
             Log::debug($order['shop_order'].' '.$order['client_ip'].';更换IP'.request()->ip());
             Order::where('id', $order['id'])->update([
                        'client_ip' => request()->ip(),
              ]);
             exit('<script> window.location.href="alipayqr://platformapi/startapp?saId=10000007&qrcode='.$order['openid'].'";</script>');
        }
        
        $account = ChannelAccount::where('id', $order['aid'])->first();
        $scanPay = new WSScanPayProcess($account['signkey'], $account['mchid'], $order['amount'] * 100, $order['shop_order'], $account['private_secret'], $account['public_secret'], $account['name']);
        $result = $scanPay->run();
        if ("0000" != $result['resultCode']){
                Order::where('id', $order['id'])->update([
                        'status' => 'load_fail',
                        'openid' => $result['msgExt'],
                        'client_ip' => request()->ip(),
                ]);
                
            ChannelAccount::where('id', $order['aid'])->update([
                    'status' => '0',
                    'msg' => $result['msgExt']
            ]);    
            exit("<h1>系统繁忙;请稍后重试</h1>");
        }
        $qrcode = str_replace("https://qrcode.hnapay.com/qrcode.shtml?qrContent=", "", $result['qrCodeUrl']);
        Order::where('id', $order['id'])->update([
           'out_trans_id' =>  $result['hnapayOrderId'],
           'openid' => $qrcode,
           'status' => 'load',
           'updated_at' => time(),
           'client_ip' => request()->ip()
        ]);
        
        exit('<script> window.location.href="alipayqr://platformapi/startapp?saId=10000007&qrcode='.$qrcode.'";</script>');
        
    }
    
    
    public function is_Mobile()
    {
        if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) {
            return true;
        } elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML")) {
            return true;
        } elseif (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            return true;
        } elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i',$_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }

    public function notify()
    {
       $arr = request()->input();
       if(empty($arr)){
           echo "200";
           exit;
       }
        Log::debug('支付回调loadPay:'.json_encode($arr, true));
        $order = Order::where('shop_order', $arr['merOrderNum'])->first();
        if($order){
              $verifyField =  ["tranCode", "version", "merId", "merOrderNum", "tranAmt", "submitTime",
                "hnapayOrderId","tranFinishTime", "respCode", "charset", "signType"];
            $account = ChannelAccount::where('id', $order['aid'])->first();
            $exp = new ExpUtils($account['private_secret'], $account['public_secret']);
            $verify = $exp->verify($verifyField, $arr, $arr['signMsg']);
            if($verify){
                User::where('id', $order['uid'])->increment('amount', $order['shop_amount']);
                $this->createLog($order['id'], $order['uid'], $order['shop_amount']);
                User::where('id', $order['ms_id'])->increment('amount', $order['code_amount']);
                $this->createLog($order['id'], $order['ms_id'], $order['code_amount']);
                Order::where('shop_order', $arr['merOrderNum'])->update([
                    'status' => 'paid',
                    'updated_at' => time(),
                    'out_trans_id' => $arr['realBankOrderId'],
                    'notify_at' => '1',
                    'openid' => $arr['userId'],
                ]);
                ChannelAccount::where('id', $order['aid'])->increment('amount', $order['amount']);
                $accAmount = ChannelAccount::where('id', $order['aid'])->value('amount');
                $channel = Channel::where('id', $order['cid'])->first();
                if($channel['top_amount'] != '0'){
                    if($accAmount > $channel['top_amount']){
                        ChannelAccount::where('id', $order['aid'])->update(['status' => '0']);
                    }
                }

                if($channel['top_num'] != '0'){
                    $start = strtotime(date('Ymd 00:00:00'));
                    $end = strtotime(date('Ymd 23:59:59'));
                    $count = Order::where('aid', $order['aid'])->whereBetween('created_at', [$start, $end])->whereIn('status', ['load_fail','paid', 'success'])->count();
                    if($count >= $channel['top_num']){
                        ChannelAccount::where('id', $order['aid'])->update(['status' => '0']);
                    }
                }

                if($channel['done_amount'] != '0'){
                    $start = strtotime(date('Ymd 00:00:00'));
                    $end = strtotime(date('Ymd 23:59:59'));
                    $amount = Order::where('cid', $order['cid'])->whereBetween('created_at', [$start, $end])->whereIn('status', ['paid', 'success'])->sum('amount');
                    if($amount >= $channel['done_amount']){
                        Channel::where('id', $order['cid'])->update(['status' => '0']);
                    }
                }


                $this->notifyBack($order['notify_url'], $order);
            }
        }
        echo "200";
    }
    
     public function httpGet($url,$type=1) {
        	$curl = curl_init();
        	curl_setopt($curl,CURLOPT_TIMEOUT,5000);
        	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        	curl_setopt($curl,CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
        	curl_setopt($curl,CURLOPT_URL,$url);
        	if($type == 1){
        		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        	}
        	$res = curl_exec($curl);
        	if($res){
        		curl_close($curl);
        		return $res;
        	}else {
        		$error = curl_errno($curl);
        		curl_close($curl);
        		return $error;
        	}
     }
     
     public function getLocationInfo($ip)
    {
         $url = 'https://apis.map.qq.com/ws/location/v1/ip?ip='.$ip.'&key=IOQBZ-ZFY3B-35BUT-NNXVA-CEFHJ-HLFBQ';
         $result = $this->httpGet($url);
         $result = json_decode($result, true);
         if($result['status'] == '0'){
             return [
                'lat' => $result['result']['location']['lat'],
                'lng' => $result['result']['location']['lng']
            ];
         }else{
             return false;
         }
        
    }
    
     public function checkCn($location)
    {
        if(strpos($location, '宁夏') !== false){
                return false;
        }
        if(strpos($location, '宁德') !== false){
                return false;
        }
        $pivonce = [
                "河北",
                "山西",
                "黑龙江",
                "吉林",
                "辽宁",
                "江苏",
                "浙江",
                "安徽",
                "福建",
                "江西",
                "山东",
                "河南",
                "湖北",
                "湖南",
                "广东",
                "海南",
                "四川",
                "贵州",
                "云南",
                "陕西",
                "甘肃",
                "青海",
                "内蒙古",
                "广西",
                "西藏",
                // "宁夏",
                "新疆",
                "北京",
                "天津",
                "上海",
                "重庆",
                "移动",
                "电信",
                "联通",
                "中国",
            ];
        $isCn = false;    
        foreach ($pivonce as $once)
        {
            if(strpos($location, $once) !== false){
                    $isCn = true;
            }
        }
        return $isCn;
    }


}
