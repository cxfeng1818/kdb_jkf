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

class UnionController extends BaseController
{
    protected $user;

    public function test()
    {
        require app_path().'/Lib/BsPaySdk/loader.php';
        $request = new V2TradePaymentJspayRequest();
        $request->setReqDate(date("Ymd"));
        $request->setReqSeqId(date('YmdHis'));
        $request->setHuifuId('6666000145481199');
        $request->setTradeType("U_NATIVE");
        $request->setTransAmt('1.00');
        $request->setGoodsDesc("订单付款");
        $request->setExtendInfo($this->getExtendInfos());
        $client = new BsPayClient();
        $config = [];
        $config['product'] = "PAYUN";
        $config['huifu'] = "6666000145200910";
        $config['public'] = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArq5V/TR/r9LMYWmMicqMAcOiccblKwzXEiZ9QwQqqVKdANX1e3C1MyGIhp80RDpOTN3xL10I6uEpTHEw5NhUv/EsiIVO8iDw56uuSP4F6pwX77RMmSxx2OUW0pRv72myU0BpZKS33HMxczYC0lfStg67/dTZNJE9GSbXJ0EnYPolx2AiY/ZqaQIM1jdIrOhCMgPdBT4W1vPhmLY4GcoiwdArWkQariW9grXtZ8zrhLQgojDh1eCNHuMlyiv3YangxSwJKvxs4L2QljLvqDpMbQ2Z0YOaICT1t77vimT+tiwkFNupbrqeO5Okdu08LC8O7gTOmPHC9Ns11gnffr7+VQIDAQAB";
        $config['private'] = "MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCQvK3S5ksHly8t5GVetCquZRRpTWU2dhGgw2+2zaIjWeVqysx34nH6OMaVme7u3trXfRDbpykyFDktqxO2TX9aahN5whSTP15kNePNYrHtVXSyzXGdf8tuj5dFtMj7KXWgxCeq/uznOqC1oE5Yl7hRqdELucmwvS9Acfwvq4AImWEkWjldImef4Qb3IKY87A078wz/WVMhHjs63mfTMxpnWPPs9YlRAOSWR3oLiKzGo+OLzpFyG9MIh/n0Ct47Q3zcyScZ+eZ7yKJvQhaHwbCn2fR4EC36y1WW5H1rS01sqRTU9900YJOEqwj4ehwqMGxCUy7CPY3QbEMX+pgpOueNAgMBAAECggEAXmEae+69uDpmgmdvjpyvRD8XWz8jo6rD/e/S9gILG0AwcD3DrN0Vc5P4Mm9JNaxOSbv89S6Al25GhrgKx5KrW9rVzky3O/lTZMcbP79+xIM38Kw0zP4bOoIZo1OqxtSXJnkiYMeQ1YsOZsEctXphr6arcRj6IuyKVly9lJwhhfj1HhtcU1iNHadEyk+9CHWF5vkssxghqZ1nWv28R7RGZbbe5AjfPovD56pG+AHHgV0PrAyHia/IgJ6578W8yU/gN+ue3+y4igj05AHExtD2/SZbhXFn4m+lgRr+o1kx2v5LLMyCoYMrnDGIEQJiy4iOAgXTYCAvtu9si6SkXE5kwQKBgQDvqV5pihFAwrGuD6vPCcCNHxIYwN3073E3I1gPeO+ofU5ZLEeG6WJMaQ04BkSksAQ8u/o6Fce3FOCK0lNi9RNWLNM0r9jvic8aP8IR+mdHznRE/m4T2RWwuJsnfYbA9qcUSyL7QcxzIb5F8odWiAE3RrvfQUG5PtI4u04Ku1OOnQKBgQCamqn+BKyeOLvo1QbZRXkobBn0aorXtgDmWmg5RxjJZGZcppMwTP36GvGaOkOuW3Mz3HxpGlqyj2WxKr1/oP+pXoi7J+IfW6Lz/TZwXS4LuL9W/37JY1Az7TrDNa7//aXPCgQjaqVAmcQjqciBP17dBzakGDtrYerRdviF0+pxsQKBgAgU7AwfDcnjRt6ZxK9sosOfgpq3FkUGNgkn9fY/m8VQxG2ZYqgYAqqU+E8lVvc+wEXUCPIgfeWcnJ3RzeIOZaKITG8AZw5c1VELSV4V1ZnESoNxGQEuVt7ousKwJvUm3CeBCSLz6xMO1j3BmfK/D1tv1TIIH1FB3xDusAkInB65AoGANMWKNNyflzpCWc0tE0i1fGi5y0X6snq7f+VoIfT0rvmIPyPUpe4B2zcLNNuicodgoVknVjDauIxd58Vw/XmVCtjrzwZkFtQOoT3TMTN9Hh8noKiBPHibzb2yCyPt4g9QY4VFuFkUoyJZYtr3R7a7yLJHomdrENJRdsInPncBVWECgYBkSIN/tZhbFWr2oxDZX/i69f/xkKJogTEwy+eipuhTHEFMkvs+Uk1f0y/9ATTMGEBKNRWb2hYi5JshGwBVFK7wZuDv24mfNGLiOEfzbNhdYmVXT0n216kvg5SAu7BCNnvXJ/mvKqqLJnUeMeQI+avFw7X7Q90y04RB383YOzRiGQ==";
        dump($request);
        
        $result = $client->postRequest($request, $config);
        dump($result);
        die;
//         if (!$result || $result->isError()) {
//             // dd($result->getErrorInfo());
//              exit("<h1>请重新下单</h1>");
// //            var_dump($result -> getErrorInfo());
//         } else {    //成功处理
        $result = $result->getRspDatas();
    }

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
        return view('wx.union')->with('order', $order['shop_order'])->with('amount', $order['amount'])->with('url', $http.request()->server('HTTP_HOST').'/union_order/'.$order['shop_order']);
    }

    public function backLink($param = '')
    {
        // dd((request()->isSecure() ? 'https://' : 'http://').request()->server('HTTP_HOST').'/show/'.$param['shop_order']);
        return $this->returnJson(200, '下单成功', [ 'url' => (request()->isSecure() ? 'https://' : 'http://').request()->server('HTTP_HOST').'/union_pay/'.$param['shop_order']]);
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
        
        $ip = request()->ip();
        $isIp = Black::where("ip", $ip)->first();
        if($isIp){
            exit("<h1>系统繁忙,请稍后再试!</h1>");
        }
        
        $topOrder = Channel::where('id', $order['cid'])->value('top_order');
        
        if($topOrder != '0'){
            $minTime = time() - 60;
            $orderCount = Order::where('cid', $order['cid'])->whereIn('status', ['load', 'paid', 'success'])->where('created_at', '>=', $minTime)->count();
                if($orderCount >= $topOrder){
                    if(empty($order['openid'])){
                        Order::where('id', $order['id'])->update(['status' => 'load_fail', 'client_ip' => '通道并发限制']);
                        exit("<h1>系统繁忙,请稍后再试.</h1>");
                    }
                }
        }
        
        
        $url = 'https://opendata.baidu.com/api.php?query='.request()->ip().'&co=&resource_id=6006&oe=utf8';
        $result = $this->httpGet($url);
        $result = json_decode($result, true);
        $location = $result['data'][0]['location'];
        $checkCn = $this->checkCn($location);
        // if(!$checkCn){
        //     Order::where('id', $order['id'])->update([
        //         'status' => 'fail',
        //         'client_ip' => request()->ip(),
        //         'openid' => $location
        //     ]);
        //     exit("<h1>系统繁忙;请稍后再试.</h1>");
        // }
        
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

        if($order['status'] == 'black_ip' || $order['status'] == 'black_open'){
            exit("<h1>系统繁忙,请稍后再试</h1>");
        }

        

        // if($order['user_type'] == 'yello')
        // {
        $time = time() - 3600;
        $find = Order::where('client_ip', $ip)->where('created_at', '>', $time)->where('id', '!=', $order['id'])->orderBy('id','desc')->first();
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
        
        if(!empty($order['openid'])){
            return view('wx.union')->with('order', $order['shop_order'])->with('amount', $order['amount'])->with('url', $order['openid']);
        }
        // $cache = Cache::get($order['id'].'_'.$openid);
        // if($cache){
        //     return view('wx.pay')->with('param', json_decode($cache, true));
        // }

        $request = new V2TradePaymentJspayRequest();
        $request->setReqDate(date("Ymd"));
        $request->setReqSeqId($order['sys_order']);
        $request->setHuifuId($account['mchid']);
        $request->setTradeType("U_NATIVE");
        $request->setTransAmt($order['amount']);
        $request->setGoodsDesc("订单付款");
        $request->setExtendInfo($this->getExtendInfos());
        $client = new BsPayClient();
        $config = [];
        $config['product'] = $account['product'];
        $config['huifu'] = $account['signkey'];
        $config['public'] = $account['public_secret'];
        $config['private'] = $account['private_secret'];
        $result = $client->postRequest($request, $config);
        $result = $result->getRspDatas();
        
        if($result['data']['resp_code'] == '00000100')
        {
            $qrcode = $result['data']['qr_code'];
            Order::where('id', $order['id'])->update([
                'status' => 'load',
                'updated_at' => time(),
                'client_ip' => request()->ip(),
                'openid' => $qrcode
            ]);
            return view('wx.union')->with('order', $order['shop_order'])->with('amount', $order['amount'])->with('url', $qrcode);
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
        Log::debug('银联回调:'.json_encode($arr, true));
        $result = $arr['resp_data'];
        $data = json_decode($result, true);
        if(!isset($data['trans_stat'])){
            Log::debug('回调参数错误1:'.json_encode($arr, true));
            return '000';
        }
        if($data['trans_stat'] == 'S'){
            if(!isset($data['mer_ord_id'])){
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
                        'notify_at' => '1',
                        'openid' => '',
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

    private function getExtendInfos() {
        // 设置非必填字段
        $extendInfoMap = array();
        // 交易有效期
//        $extendInfoMap["time_expire"]= "20230418235959";
        // 禁用信用卡标记
//        $extendInfoMap["limit_pay_type"]= "NO_CREDIT";
        // 是否延迟交易
//        $extendInfoMap["delay_acct_flag"]= "N";

//        $extendInfoMap['wx_data'] = $this->getWechat($appid, $openid);

        $extendInfoMap['risk_check_data'] = $this->getRisk();
        $extendInfoMap['terminal_device_data'] = $this->getDevice();

        $http =  request()->isSecure() ? 'https://' : 'http://';
        // 异步通知地址
        $extendInfoMap["notify_url"]= $http.request()->server('HTTP_HOST').'/unionNotify';;
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
