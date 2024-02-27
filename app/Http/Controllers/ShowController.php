<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Lib\BsPaySdk\core\BsPayClient;
use App\Lib\BsPaySdk\request\V2TradePaymentJspayRequest;
use App\Models\Channel;
use App\Models\ChannelAccount;
use App\Models\Order;
use App\Models\User;
use App\Models\UserRecord;
use App\Models\Black;
use App\Models\UserChannel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class ShowController extends BaseController
{
    protected $user;

    public function check()
    {
        $order = request()->input('order');
        if(empty($order)){
            exit("<h1>参数错误</h1>");
        }
         $order = Order::where('shop_order', $order)->first();
         if(empty($order)){
            exit("<h1>参数错误</h1>");
        }
        if($order['status'] == 'fail'){
            return $this->returnJson(500, '订单失效');
        }
        if($order['status'] == 'paid' || $order['status'] == 'success'){
            return $this->returnJson(200, '订单完成');
        }
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
        $http = request()->isSecure() ? 'https://' : 'http://';
        return view('wx.gzh')->with('order', $order['shop_order'])->with('amount', $order['amount'])->with('url', $http.request()->server('HTTP_HOST').'/order/'.$order['shop_order']);
    }

    public function backLink($param = '')
    {
        // dd((request()->isSecure() ? 'https://' : 'http://').request()->server('HTTP_HOST').'/show/'.$param['shop_order']);
        return $this->returnJson(200, '下单成功', [ 'url' => (request()->isSecure() ? 'https://' : 'http://').request()->server('HTTP_HOST').'/show/'.$param['shop_order']]); 
    }

    public function order($order = '')
    {
        if(empty($order)){
            exit("<h1>参数错误</h1>");
        }
        
        $order = Order::where('shop_order', $order)->first();
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
        
        if($order['status'] == 'paid' || $order['status'] == 'success')
        {
            exit("<h1>系统繁忙,请稍后再试.</h1>");
        }
        
        if($order['status'] == 'fail' || $order['status'] == 'load_fail'){
            exit("<h1>订单已失效;请重新下单</h1>");
        }
        
        if($order['status'] == 'black_ip' || $order['status'] == 'black_open'){
             exit("<h1>系统繁忙,请稍后再试.</h1>");
        }
        
         if($order['uid'] == '1243'){
            Order::where('id', $order['id'])->update([
                'client_ip' => request()->ip(),
            ]);
            $account = ChannelAccount::where('id', $order['aid'])->first();
            $http = request()->isSecure() ? 'https://' : 'http://';
            $host = $http.request()->server('HTTP_HOST').'/jumpPay/'.$order['shop_order'];
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$account['appid'].'&redirect_uri='.$host.'&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
            Header("Location:$url");
            exit;
        }
        
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
        }
        
        $account = ChannelAccount::where('id', $order['aid'])->first();
        
        $findIp = Black::where('ip', request()->ip())->first();
        if($findIp){
            Order::where('id', $order['id'])->update([
                'status' => 'black_ip',
                'client_ip' => request()->ip(),
                'openid' => 'ip黑名单'
            ]);
            exit("<h1>系统繁忙.请稍后再试</h1>");
        }
        
        $http = request()->isSecure() ? 'https://' : 'http://';
        $host = $http.request()->server('HTTP_HOST').'/jumpPay/'.$order['shop_order'];
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$account['appid'].'&redirect_uri='.$host.'&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
        
        Header("Location:$url");
        exit;
    }

    public function jump($order = '')
    {
        if(empty($order)){
             exit("<h1>参数错误</h1>");
        }
        require app_path().'/Lib/BsPaySdk/loader.php';
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
        
        $account = ChannelAccount::where('id', $order['aid'])->first();
        $code = request()->input('code');
        $info = $this->httpGet("https://api.weixin.qq.com/sns/oauth2/access_token?appid={$account['appid']}&secret={$account['secret']}&code={$code}&grant_type=authorization_code");
        $info = json_decode($info, true);
        
        if(!isset($info['openid'])){
            exit('<h1>请重新下单</h1>');
        }
        
        $openid = $info['openid'];
        if(!empty($order['openid'])){
           if( $order['openid'] != $openid){
                exit('<h1>请重新下单</h1>');
           }
        }
        
        $findOpen = Black::where('openid', $openid)->first();
        if($findOpen){
            Order::where('id', $order['id'])->update([
                'status' => 'black_open',
                'openid' => $openid
            ]);
            exit("<h1>系统繁忙,请稍后再试</h1>");
        }
        

        if($order['status'] == 'black_ip' || $order['status'] == 'black_open'){
             exit("<h1>系统繁忙,请稍后再试</h1>");
        }
        
        $ip = request()->ip();
        $isIp = Black::where("ip", $ip)->first();
        $isOpen = Black::where('openid', $openid)->first();
        if($isIp || $isOpen){
            exit("<h1>系统繁忙,请稍后再试!</h1>");
        }
        
        // if($order['user_type'] == 'yello')
        // {
             $time = time() - 3600;
            $find = Order::where('openid', $openid)->where('created_at', '>', $time)->where('id', '!=', $order['id'])->first();
            if($find){
                 if($find['status'] == 'paid' || $find['status'] == 'success'){
                     Order::where('id', $order['id'])->update([
                            'client_ip' => request()->ip(),
                            'status' => 'fail',
                            'openid' => '订单限制['.$find['id'].']'
                         ]);
                    exit('<h1 style="width:100%;text-align:center;font-size:60px;margin-top:100px;">业务繁忙;请稍后重新下单</h1>');
                }
            }
        // }
        
        $cache = Cache::get($order['id'].'_'.$openid);
        if($cache){
             return view('wx.pay')->with('param', json_decode($cache, true));
        }
        
        $request = new V2TradePaymentJspayRequest();
        $request->setReqDate(date("Ymd"));
        $request->setReqSeqId($order['sys_order']);
        $request->setHuifuId($account['mchid']);
        $request->setTradeType("T_JSAPI");
        $request->setTransAmt($order['amount']);
        $request->setGoodsDesc("订单付款");
        $request->setExtendInfo($this->getExtendInfos($account['appid'], $openid));
        $client = new BsPayClient();
        $config = [];
        $config['product'] = $account['product'];
        $config['huifu'] = $account['signkey'];
        $config['public'] = $account['public_secret'];
        $config['private'] = $account['private_secret'];
        $result = $client->postRequest($request, $config);
        
//         if (!$result || $result->isError()) {
//             // dd($result->getErrorInfo());
//              exit("<h1>请重新下单</h1>");
// //            var_dump($result -> getErrorInfo());
//         } else {    //成功处理
            $result = $result->getRspDatas();
            if($result['data']['resp_code'] == '00000100')
            {
                Order::where('id', $order['id'])->update([
                    'status' => 'load',
                    'updated_at' => time(),
                    'client_ip' => request()->ip(),
                    'openid' => $openid
                ]);
                $param = json_decode($result['data']['pay_info'], true);
                Cache::set($order['id'].'_'.$openid, $result['data']['pay_info'], 3600);
                return view('wx.pay')->with('param', $param);
            }else{
                $messge = '';
                if(isset($result['data']['bank_message'])){
                    $messge = $result['data']['bank_message'];
                }
                if($messge == '重复交易'){
                    Order::where('id', $order['id'])->update([
                                'status' => 'load_fail',
                                'openid' =>  '下单与支付账号不一致'
                    ]);
                }else if($result['data']['resp_desc'] == '重复交易'){
                    Order::where('id', $order['id'])->update([
                                'status' => 'load_fail',
                                'openid' => '下单与支付账号不一致'
                    ]);
                }else{
                    ChannelAccount::where('id', $order['aid'])->update([
                        'status' => 0, 
                        'msg' => $result['data']['resp_desc']
                    ]);
                    Order::where('id', $order['id'])->update([
                                'status' => 'load_fail',
                                'openid' =>  $result['data']['resp_desc'].';' . $messge
                    ]);
                }
                exit("<h1>请重新下单</h1>");
            }
        // }
    }

    public function notify()
    {
       $arr = request()->input();
       if(empty($arr)) exit;
       Log::debug('支付回调:'.json_encode($arr, true));
       $result = $arr['resp_data'];
       $data = json_decode($result, true);
       if(!isset($data['trans_stat'])){
            Log::debug('回调参数错误1:'.json_encode($arr, true));
                return '000';
       }
       if($data['trans_stat'] == 'S'){
            if(isset($data['wx_response']['openid'])){
                $data['wx_response']['sub_openid'] = $data['wx_response']['openid'];
            }
            if(!isset($data['mer_ord_id']) || !isset($data['wx_response']['sub_openid'])){
                Log::debug('回调参数错误2:'.json_encode($arr, true));
                return '000';
            }
            
            $order = Order::where('sys_order', $data['mer_ord_id'])->first();
          if(empty($order)){
               Log::debug('未找到订单:'.json_encode($arr, true));
               return '000';
          }
          if($order['status'] == 'load' || $order['status'] == 'load_fail'){
              $account = ChannelAccount::where('id', $order['aid'])->value('mchid');
              if($data['acct_split_bunch']['acct_infos'][0]['huifu_id'] == $account){
                      User::where('id', $order['uid'])->increment('amount', $order['shop_amount']);
                      $this->createLog($order['id'], $order['uid'], $order['shop_amount']);
                      User::where('id', $order['ms_id'])->increment('amount', $order['code_amount']);
                      $this->createLog($order['id'], $order['ms_id'], $order['code_amount']);
                      Order::where('sys_order', $data['mer_ord_id'])->update([
                          'status' => 'paid',
                          'updated_at' => time(),
                          'out_trans_id' => $data['out_trans_id'],
                          'notify_at' => '1'
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
       }
       return '000';
    }
//3918
    
    public function checkNotify()
    {
        $start = strtotime(date('Ymd 00:00:00'));
        $end = strtotime(date('Ymd 23:59:59'));
        $list = Order::whereBetween('created_at', [$start, $end])->where('status', 'paid')->get();
        // $list = Order::where('status', 'paid')->get();
        foreach ($list as $order){
            $notifyAt = $order['notify_at'] ?? 0;
            if($notifyAt <= 4){
                 $notifyAt = $notifyAt += 1;
                //  Order::where('id', $order['id'])->increment('notify_at', $notifyAt);
                //  $this->notifyBack($order['notify_url'], $order);
            }
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
                "移动",
                "电信",
                "联通",
                "中国",
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
                "宁夏",
                "新疆",
                "北京",
                "天津",
                "上海",
                "重庆"
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

    private function getExtendInfos($appid, $openid) {
        // 设置非必填字段
        $extendInfoMap = array();
        // 交易有效期
//        $extendInfoMap["time_expire"]= "20230418235959";
        // 禁用信用卡标记
//        $extendInfoMap["limit_pay_type"]= "NO_CREDIT";
        // 是否延迟交易
//        $extendInfoMap["delay_acct_flag"]= "N";

        $extendInfoMap['wx_data'] = $this->getWechat($appid, $openid);
        
        $extendInfoMap['risk_check_data'] = $this->getRisk();
        $extendInfoMap['terminal_device_data'] = $this->getDevice();
         
        $http =  request()->isSecure() ? 'https://' : 'http://';
        // 异步通知地址
        $extendInfoMap["notify_url"]= $http.request()->server('HTTP_HOST').'/wxNotify';;
        // 备注
        $extendInfoMap["remark"]= "";
        // 账户号
        // $extendInfoMap["acct_id"]= "";
        return $extendInfoMap;
    }

    public function getDevice(){
        $dto = array();
        $dto['device_type'] = "1";
        return json_encode($dto,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

     private function getRisk(){
        $dto = array();
        $dto['ip_addr'] = request()->ip();
        $location = $this->getLocationInfo(request()->ip());
        if($location !== false){
            $dto['latitude'] = $location['lat'];
            $dto['longitude'] = $location['lng'];
        }
        
        return json_encode($dto,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    private function getWechat($appid, $openid){
        $dto = array();
        $dto['ip_addr'] = $appid;
        $dto['openid'] = $openid;
        return json_encode($dto,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
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
}
