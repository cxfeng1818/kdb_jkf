<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use App\Models\UserRecord;

class BaseController extends Controller
{
    public function returnJson($code, $msg, $data=[])
    {
        $result = [
            "code" => $code,
            "msg" => $msg,
            "data" => $data,
        ];
        return response()->json($result, 200)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }


    protected function log($msg)
    {
        $log = '---------------';
        $log .= $msg;
        $log .= '---------------';
        Log::error($log);
    }


     public function checkSign($data)
    {
        if(!isset($data['sign'])){
            return false;
        }
        $sign = $data['sign'];
        unset($data['sign']);
        if($data['account'] == '1243'){
            $key = '1243';
        }else{
            $key = User::where('id', $data['account'])->value('api_key');
        }
        $checkSign = $this->createSign($data, $key);
        if($sign == $checkSign){
            return false;
        }else{
            return true;
        }
    }

    public function checkIp()
    {
        $ip = request()->ip();
        $userIp = explode(',', $this->user['api_ip']);
        $isCheck = true;
        foreach ($userIp as $row)
        {
            if($row == $ip){
                $isCheck = false;
            }
        }
        return $isCheck;
    }

    public function createSign($list,$Md5key)
    {
        ksort($list);
        $md5str = "";
        foreach ($list as $key => $val) {
            if (!empty($val)) {
                $md5str = $md5str . $key . "=" . $val . "&";
            }
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key));
        return $sign;
    }
    
     public function notifyBack($url, $order)
    {
        $return_array = [    //返回字段
            "account"        => $order["uid"], // 商户ID
            "order"          => $order['shop_order'], // 订单号
            "transaction_id" => $order["sys_order"], //支付流水号
            "amount"         => $order["amount"] + $order['decline_amount'], // 交易金额
            "datetime"       => date("YmdHis"), // 交易时间
            "status"         => '00', // 交易状态
        ];
        $apikey = User::where('id', $order['uid'])->value('api_key');
        $sign                   = $this->createSign($return_array, $apikey);
        $return_array["sign"]   = $sign;
        $this->httpPost($return_array, $url, $order['id']);
    }
    
    public function httpPost($data,$notify,$orderId)
    {
        $notifystr = "";
        foreach ($data as $key => $val) {
            $notifystr = $notifystr . $key . "=" . $val . "&";
        }
        $notifystr = rtrim($notifystr, '&');
        $ch        = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $notify);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $notifystr);
        $contents = curl_exec($ch);
        // var_dump($contents);
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (strstr(strtolower($contents), "ok") != false) {
                Order::where('id',$orderId)->update(['status'=>'success', 'notify_at' => time()]);
        }
    }
    
    public function curlPost( $post_data = array(), $url, $orderId) {
       
        $ch = curl_init(); // 启动一个CURL会话
        curl_setopt($ch, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查 // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($ch, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); // Post提交的数据包
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        //curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取的信息以文件流的形式返回 
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
        $result = curl_exec($ch);
        var_dump($result);
        // 打印请求的header信息
        if (strstr(strtolower($result), "ok") != false) {
                Order::where('id',$orderId)->update(['status'=>'success', 'notify_at' => time()]);
        }
        curl_close($ch);
        return $result;
        }

    
    public function createLog($oid, $uid, $amount)
    {
        $beforeAmount = User::where('id',$uid)->value('amount');
        UserRecord::create([
            'uid' => $uid,
            'oid' => $oid,
            'befor_amount' => $beforeAmount,
            'amount' => $amount,
            'after_amount' => ($beforeAmount + $amount),
            'created_at' => time()
        ]);
    }
}
