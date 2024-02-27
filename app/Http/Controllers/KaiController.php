<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ChannelAccount;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class KaiController extends BaseController
{
     public function backLink($data)
    {
        $this->signIn($data);
        $account = ChannelAccount::where("id", $data['aid'])->first();
        $url = 'http://kdbmaxvs.jf.bjprd:39999/epos-server/trade/qrLink';
        $hex= dechex(strlen($data['sys_order']));
        $data = [
            'equipCsn' => $account['signkey'],
            'userId' => $data['mchid'],
            'tradeAmt' =>  $data['amount'],
            'selfDefined48' => 'C10743383033303030C20'.$hex.bin2hex($data['sys_order']),
            // 'selfDefined62' => 'FF02213436307C30307C343532337C313238363132323432FF06023032FF07023032FF08203030303035303032363839393930303031323337FF09203839383630344134313932323830313435363837FF1006BFAAB8B6B1A6FF110856302E302E342E30FF130843302E302E302E31FF14057C30307C7CFF15057C30307C7C',
            'termLocationInfo' => '01'.mb_strlen('117.574057').bin2hex('117.574057').'02'.mb_strlen('24.511016').bin2hex('24.511016').'05'.mb_strlen($data['signkey']).bin2hex($data['signkey']),
        ];
        $data = [
                'appId' => $data['appid'],
                'reqSeqId' => date('YmdHis').rand(1111,9999),
                'reqData' => json_encode($data, true),
                'signType' => "rsa",
                'timestamp' => date('YmdHis'),
        ];

        $sign = $this->setSign($data);
        $signData = $this->buildSign($sign, $account['private_secret']);
        $data['signData'] = $signData;
        $result = $this->jsonPost($url, $data);
        $result = json_decode($result, true);
        if($result['respCode'] == '0000'){
            $data = hex2bin($result['respData']['selfDefined60']);
            $utf8String = mb_convert_encoding($data, 'UTF-8', 'ASCII');
            $data = trim($utf8String);
            $data = str_replace('$', '', $data);
            $link = str_replace('_ñ_', '', $data);
            Order::where('id', $data['id'])->update(['organize' => $link]);
            return $this->returnJson(200, '下单成功', [ 'url' => (request()->isSecure() ? 'https://' : 'http://').request()->server('HTTP_HOST').'/yunShow/'.$data['shop_order']]);
        }else{
            Log::error('用户主扫错误:'.json_encode($result, true));
            exit($result['respMsg']);
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
        return view('wx.union')->with('order', $order['shop_order'])->with('amount', $order['amount'])->with('url', $order['organize']);
    }

    public function notify()
    {
        $param = request()->input();
        Log::debug('支付回调:'.json_encode($param, true));
    }

     public function setSign($params)
    {
        // ksort($params);
         // 第一步：获取参数集合并排序
        $keys = array_keys($params);
        sort($keys);
        // 第二步：把所有参数名和参数值串在一起
        $query = "";
        foreach ($keys as $key) {
            $v = $params[$key];
            if ("signData" === $key) {
                continue;
            }
            if (isset($key) && isset($v)) {
                $query .= $key . "=" . $v . "&";
            }
        }

        return rtrim($query, "&");
     }


    private function buildSign(string $bizContent, string $key)
    {
            $key = str_replace("\r\n", "", $key);
            $key = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";

            openssl_sign($bizContent, $signedMsg, $key, OPENSSL_ALGO_SHA256);

            return base64_encode($signedMsg);
    }


    public function signIn($data)
    {
        $account = ChannelAccount::where("id", $data['aid'])->first();
        $url = 'http://kdbmaxvs.jf.bjprd:39999/epos-server/trade/signIn';
        $orderNo = $data['sys_order'];
        $flowNo = substr($data['sys_order'], -6);
        $hex= dechex(strlen($orderNo));
        $csnNo = dechex(strlen($account['signkey']));
        $data = [
            'equipCsn' => $account['signkey'],
            'userId' => $account['mchid'],
            'tradeAmt' => $data['amount'],
            'selfDefined60' => '6010'.mb_strlen('00').bin2hex('00').'6020'.$hex.bin2hex($orderNo),
            // 'selfDefined62' => 'FF02213436307C30307C343532337C313238363132323432FF06023032FF07023032FF08203030303035303032363839393930303031323337FF09203839383630344134313932323830313435363837FF1006BFAAB8B6B1A6FF110856302E302E342E30FF130843302E302E302E31FF14057C30307C7CFF15057C30307C7C',
            'flowNo' => $flowNo,
            'selfDefined60' => '00'.$flowNo.'003',
//            'termLocationInfo' => '040202'.'0514ABZ8800000000000000106060000000708EE8D3A27080824013001',
            'termLocationInfo' => '040202'.'05'.$csnNo.$account['signkey'].'06060000000708EE8D3A27080824013001',
        ];

        $data = [
            'appId' => $account['appid'],
            'reqSeqId' =>  date('YmdHis').rand(1111,9999),
            'reqData' => json_encode($data, true),
            'signType' => "rsa",
            'timestamp' => date('YmdHis'),
        ];
        $sign = $this->setSign($data);
        $signData = $this->buildSign($sign, $account['private_secret']);
        $data['signData'] = $signData;
        $result = $this->jsonPost($url, $data);
        $result = json_decode($result, true);
        if($result['respCode'] == '0000')
        {
            return true;
        }else{
            Log::error('交易签到错误:'.json_encode($result, true));
            exit($result['respMsg']);
        }

    }


    private function jsonPost($url, $data = NULL)
    {
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS,  $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, false); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 获取的信息以文件流的形式返回
        $result = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            return 'Error POST'.curl_error($curl);
        }
        curl_close($curl); // 关键CURL会话
        return $result; // 返回数据
    }
}
