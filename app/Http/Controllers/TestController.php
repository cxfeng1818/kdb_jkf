<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Lib\BsPaySdk\request\V2TradePaymentJspayRequest;
use App\Lib\BsPaySdk\core\BsPayClient;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Channel;
use App\Models\ChannelAccount;
use App\Models\User;
use App\Models\UserChannel;
use Illuminate\Support\Facades\Cache;

class TestController extends BaseController
{
    public function req()
    {
      $data = request()->input();
      Log::info('进件回调：'.json_encode($data, JSON_UNESCAPED_UNICODE));
    }

     public function trade()
    {
      $data = request()->input();
      Log::info('进件回调：'.json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public function clearAmount()
    {
        $list = ChannelAccount::get();
        foreach ($list as $one)
        {
            if(empty($one['msg'])){
                ChannelAccount::where('id', $one['id'])->update(['amount' => '0.00', 'status' => '1']);
            }

        }
    }


    public function show()
    {
        // $start = strtotime(date('20231024 00:00:00'));
        // $end = strtotime(date('20231024 23:59:59'));
        // $list = Order::where('status', 'success')->whereBetween('created_at', [$start, $end])->get();
        // $costRate = UserChannel::where('uid', 1032)->value("rate");
        // $codeRate = UserChannel::where('uid', 1025)->value("rate");
        // foreach ($list as $item)
        // {
        //     $codeAmount = $item['amount'] * ($codeRate / 100);
        //     $userAmount = $item['amount'] * ($costRate / 100);
        //     $shopAmount = $item['amount'] - $codeAmount - $userAmount;
        //     Order::where('id', $item['id'])->update([
        //         'shop_amount' => $shopAmount,
        //         'cost_amount' => $userAmount,
        //         'code_amount' => $codeAmount,
        //     ]);
        // }
        // die;
        if(request()->method() == 'POST'){
            $input = request()->input();
             $data = User::where("id", '1000')->first();
             $key = $data['api_key'];  //apikey
             $data = [
                'account' => $data['id'],  //账户号
                'order' => date('YmdHis') . rand(11111, 99999),  //订单号
                'time' => time(),  //下单时间
                'code' => $input['cid'] ?? '101',       //通道编码
                'notify_url' => 'https://'. request()->server('HTTP_HOST') .'/testNotify', //异步回调
                'callback_url' => 'https://'. request()->server('HTTP_HOST') .'/testNotify',  //同步回调//
                'amount' => $input['amount'] ?? '10.00',                  //金额
            ];
            $data['sign'] = $this->createSign($data, $key);

            $result = $this->curlPost('http://' . request()->server('HTTP_HOST') . '/submitOrder', $data);
            print_r($result);
            die;
            $result = json_decode($result, true);

            if($result['code'] == 500){
                exit('<h1 style="font-size:40px">下单失败['.$result['msg'].']</h1>');
            }
            header("location:". $result['data']['url']);
        }
        $channel = Channel::get();
        return view('test.test')->with('channel', $channel);
    }

    function curlPost($url, $post_data = array(), $timeout = 5, $header = "", $data_type = "")
    {
        $header = empty($header) ? '' : $header;
        //支持json数据数据提交
        if ($data_type == 'json') {
            $post_string = json_encode($post_data);
        } elseif ($data_type == 'array') {
            $post_string = $post_data;
        } elseif (is_array($post_data)) {
            $post_string = http_build_query($post_data, '', '&');
        }

        $ch = curl_init();    // 启动一个CURL会话
        curl_setopt($ch, CURLOPT_URL, $url);     // 要访问的地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // 对认证证书来源的检查   // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
//        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($ch, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);     // Post提交的数据包
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);     // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // 获取的信息以文件流的形式返回
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
        $result = curl_exec($ch);
        // 打印请求的header信息
        //$a = curl_getinfo($ch);
        //var_dump($a);
        curl_close($ch);
        return $result;
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


    public function test()
    {
        $device = request()->input('device_key');
        Log::debug('device:'.$device);
        //   if($device == '31'){
        $order = Order::where('aid', $device)->where("status", 'none')->first();
        if($order){
            $get = Cache::get($device.'_'.$order['amount']);
            if(!$get){
                exit(json_encode([
                    'status' => "1",
                    'orderId' => $order['shop_order'],
                    'money' => $order['amount'],
                    'mark' => $order['id'],
                ]));
            }
        }else{
            exit(json_encode([
                'status' => "0",
            ]));
        }
        //   }
        //   exit(json_encode([
        //                 'status' => "1",
        //                 'orderId' => '1234',
        //                 'money' => '15.00',
        //                 'mark' => '4321',
        //     ]));

    }

    public function code()
    {
        $data = request()->input();
        $device = Order::where('shop_order', $data['orderId'])->value('aid');
        Log::debug('device_key:'.$device.';code:'.json_encode($data, true));
        Cache::set($device.'_'.$data['monery'], $data['qrcode']);
    }

    public function notify()
    {
        $data = request()->input();
        Log::debug('notify:'.json_encode($data, true));
        exit("OK");
    }

}
