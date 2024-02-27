<?php

namespace App\Http\Controllers;

use App\Models\FreezeLog;
use App\Models\Order;
use App\Models\User;
use App\Models\UserRecord;
use App\Models\ChannelAccount;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Lib\Aop\AopClient;
use App\Lib\Aop\request\AlipayOpenAuthTokenAppRequest;
use App\Lib\Aop\request\AlipayDataBillAccountlogQueryRequest;

class KongController extends BaseController
{

    //定时
    public function autoFail()
    {
        $order = Order::whereIn('status', ['load', 'none'])->get();
        foreach ($order as $row){
            $time = time() - 599;
            if(strtotime($row->created_at) < $time){
                if($row['status'] == 'load'){
                    Order::where('id', $row['id'])->update(['status' => 'load_fail']);
                }else{
                    Order::where('id', $row['id'])->update(['status' => 'fail']);
                }
                
                // $freezeAmount = User::where('id', $row['ms_id'])->value('freeze_amount');
                // FreezeLog::create([
                //     'uid' => $row['uid'],
                //     'before' => $freezeAmount,
                //     'amount' => $row->amount,
                //     'after' => $freezeAmount + $row->amount,
                //     'oid' => $row->id,
                //     'type' => 'add',
                //     'created_at' => time(),
                // ]);
                // User::where('id', $row['ms_id'])->increment('freeze_amount', $row->amount);
            }
        }
    }


    public function reqPost($data, $link, $header)
    {
        $notifystr = "";
        foreach ($data as $key => $val) {
            $notifystr = $notifystr . $key . "=" . $val . "&";
        }
        $notifystr = rtrim($notifystr, '&');
        dump($notifystr);
        $ch        = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        dump($header);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $link);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $notifystr);
        $contents = curl_exec($ch);
        // var_dump($contents);
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        dump(curl_errno($ch));
        dump($httpCode);
        dd($contents);
        curl_close($ch);

    }


    public function checkNotify()
    {
        // $order = Order::where('id', '513')->first();
        // $return_array = [    //返回字段
        //     "account"        => $order["uid"], // 商户ID
        //     "order"          => $order['shop_order'], // 订单号
        //     "transaction_id" => $order["sys_order"], //支付流水号
        //     "amount"         => $order["amount"] + $order['decline_amount'], // 交易金额
        //     "datetime"       => date("YmdHis"), // 交易时间
        //     "status"         => "00", // 交易状态
        // ];
        // $apikey = User::where('id', $order['uid'])->value('api_key');
        // $sign                   = $this->createSign($return_array, $apikey);
        // $return_array["sign"]   = $sign;
        // echo "<pre>";
        // var_dump($return_array);
        // var_dump( $order['notify_url']);
        // $this->curlPost($return_array, $order['notify_url'], $order['id']);
    }

    public function auth()
    {
        // exit();
        $data = request()->input();
        $code = $data['app_auth_code'];
        if(empty($code)){
             echo "<h1 style='font-size:36px'>参数缺失</h1>";
        }
        $id = $data['account'];
        $aop = new AopClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = '2021004113683064';
        $aop->rsaPrivateKey = 'MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQCUUW2woXkDNX6mA/1qK6fYoMet6qd0GeI+Jv2pymfHfacnYntFGwRyhrex0nCfR3YeFLJcS42RPPEPBTaoEpzF4BK8nq7er+S92mu9B3kmBd7p8SnIV3jILwOTYgIIxqJ05/e6IuM4IPcm/yM/03utranJmVyfhJ2m7fsWihQt5A/rnkUDVbumv0ZblBjkiwjH/5hov7g+kSpgwl2DAFb4LEsvkZZAPmQqCaZHt4tfZIkotOueh6xXIkkF4Q/t8CyUOHmqX5BXz20xXLHbxMpsiAOvvPQ/QYTTrXWGp43dWGyJSspkIFg2Kp6JJIFr5EVPKdkP+hhcvrd2Ioh1a+LnAgMBAAECggEAbXp5pBJcl8DJ3lM9oDvfJijvm3GE708Xz9bKEKVl2zvlwU2RPh5GNx83wptaJEgEaQnP6g6ezaEVMogfowoyDGFskyvyWk+tYXIzapF0nFtjAn3P4KCod+M0GXsTk1TDn9WF5zBPViQHKxLS+w7o0PdKR5PbaafjEs/BMg7z/DalVtOBKYqamW+cBBYJcdYP0/HqkXBmF7ccQgv0JVVozc4wDFv6Y0cN0QWHT+zMgSoTwRc8oq1XYHGuWHkaEu570chK/hgvHo1Sy5vc3zoD8aQLZV6ALws759UnYMX6kkdxCS5K3EP5qpnAWIc1n67mRplMQK3AvexdJjuqMXcWoQKBgQDX7WDAOxcOmL+e5lDKdccU+U7sj8myyFhMX4hWK9qRKxMWq4GdKs54s63qAIdO9s+f0fHWSe4sq4yhQUtnrICoCdv+UH1x1vLJ2GO+HyRlZWYORLG8U4NBoRc/tZVA2x5msBWAZXC9wXrZcp4YPwO0Nd4TLwnXyVCUXJxqLWOakQKBgQCv1/bUDlPwPzJ528zbtq5PVmz76HuLxvYAyG03kfGj+pq0yRdJsKIOOvWPrhnlbfic7U8BRjdSSYt9lFBh2HM+nw1mSBnsC68tlHjcu2j0cU5Phcz4nCOOhf+wZTq8LnOlSZu4d6113+EBv6NiOOHIvypCc8UezID3MxG+w/wx9wKBgQCEWYGWEiH91m6NjQo0LqDKIE3Z/gBZTyegGGZOuy6LEGXw3Uri9Nv9a9TLu2s0YV+9zEd5W3NbegMGzCjV8Qwi83gel2NspZ3HTpVEcX4zkEVPtZdjoaSwU3cWm8xSJnyp4IAtZQAHr1IHepOgtO8mRGZMR+a2lzSG24UcJ5oHoQKBgQCbKCFIHVhdPTYpphNNrxSBNs41PH5+6FC6gh80kFc6DG8jo+gjgnKLweRZIhMBiRZ8STyT8EPyHKizZwHCRD3z/F3TaT1ONUUeH8rBYbALOEsUCgjBOOx63qutFrVLxGUQbDG2BAuPXbrpUxI0IeK/wkXkmshNE7+b33kB7WaDIwKBgQC8rYTvv1j73MCav6EpBRBunDS6Q2GfyTAD8u0NkYY91sUpXphLG/QE4GFxmLIoe1YC3pAJ2wO3YTHKc/mzkdMFxd83VWtZhr3Q+bqOVl2143Y1+OQ62uMWNihKEM/Vnd4WNUC1HUVrm7RM1ZwYgBH6gZOB/Vq45+c+8DxZG+JJDg==';
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmRP6PAUe2c12JmS2gKv/rEG9oonhEe1EHrrZ+GBtwbJrBHo9b4JfwT7Pp2Cmjm8T0SZLWUwv+n9yA7/FNvwTKHJBw4DWWq+Ig/29Ydv7x0Sk5HwkuDPYF7OHVoJkLGGR3PQjwtLynIhlK5aVZ8ne4jN59JLJ/iGjGnMpxUsCISn/mnekdKJcq0Rewv4R7Zv1DQe7bLmaxWMI8c7G+ac8XLFEZ2ypKuVJp4ccEV5hkXX7IehW5YL8HzojhTpMU1cwk/POkUzxNlD/PNQmPCDJvnMF/VxJ2o4p7BRcckGV1nfH8+cC+fKxYLcmxI5igj6jbIaVlv7ke2GWDGvoq9+4WQIDAQAB';
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'UTF-8';
        $aop->format = 'json';
        $request = new AlipayOpenAuthTokenAppRequest();
        $result = [
            "grant_type" => "authorization_code",
            "code" => $code,
        ];
        $request->setBizContent(json_encode($result, true));
        $result = $aop->execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            dump($result->$responseNode);
            $userId = $result->$responseNode->tokens[0]->user_id;
            $token = $result->$responseNode->tokens[0]->app_auth_token;
            $aop = new AopClient();
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
            $aop->appId = '2021004113683064';
            $aop->rsaPrivateKey = 'MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQCUUW2woXkDNX6mA/1qK6fYoMet6qd0GeI+Jv2pymfHfacnYntFGwRyhrex0nCfR3YeFLJcS42RPPEPBTaoEpzF4BK8nq7er+S92mu9B3kmBd7p8SnIV3jILwOTYgIIxqJ05/e6IuM4IPcm/yM/03utranJmVyfhJ2m7fsWihQt5A/rnkUDVbumv0ZblBjkiwjH/5hov7g+kSpgwl2DAFb4LEsvkZZAPmQqCaZHt4tfZIkotOueh6xXIkkF4Q/t8CyUOHmqX5BXz20xXLHbxMpsiAOvvPQ/QYTTrXWGp43dWGyJSspkIFg2Kp6JJIFr5EVPKdkP+hhcvrd2Ioh1a+LnAgMBAAECggEAbXp5pBJcl8DJ3lM9oDvfJijvm3GE708Xz9bKEKVl2zvlwU2RPh5GNx83wptaJEgEaQnP6g6ezaEVMogfowoyDGFskyvyWk+tYXIzapF0nFtjAn3P4KCod+M0GXsTk1TDn9WF5zBPViQHKxLS+w7o0PdKR5PbaafjEs/BMg7z/DalVtOBKYqamW+cBBYJcdYP0/HqkXBmF7ccQgv0JVVozc4wDFv6Y0cN0QWHT+zMgSoTwRc8oq1XYHGuWHkaEu570chK/hgvHo1Sy5vc3zoD8aQLZV6ALws759UnYMX6kkdxCS5K3EP5qpnAWIc1n67mRplMQK3AvexdJjuqMXcWoQKBgQDX7WDAOxcOmL+e5lDKdccU+U7sj8myyFhMX4hWK9qRKxMWq4GdKs54s63qAIdO9s+f0fHWSe4sq4yhQUtnrICoCdv+UH1x1vLJ2GO+HyRlZWYORLG8U4NBoRc/tZVA2x5msBWAZXC9wXrZcp4YPwO0Nd4TLwnXyVCUXJxqLWOakQKBgQCv1/bUDlPwPzJ528zbtq5PVmz76HuLxvYAyG03kfGj+pq0yRdJsKIOOvWPrhnlbfic7U8BRjdSSYt9lFBh2HM+nw1mSBnsC68tlHjcu2j0cU5Phcz4nCOOhf+wZTq8LnOlSZu4d6113+EBv6NiOOHIvypCc8UezID3MxG+w/wx9wKBgQCEWYGWEiH91m6NjQo0LqDKIE3Z/gBZTyegGGZOuy6LEGXw3Uri9Nv9a9TLu2s0YV+9zEd5W3NbegMGzCjV8Qwi83gel2NspZ3HTpVEcX4zkEVPtZdjoaSwU3cWm8xSJnyp4IAtZQAHr1IHepOgtO8mRGZMR+a2lzSG24UcJ5oHoQKBgQCbKCFIHVhdPTYpphNNrxSBNs41PH5+6FC6gh80kFc6DG8jo+gjgnKLweRZIhMBiRZ8STyT8EPyHKizZwHCRD3z/F3TaT1ONUUeH8rBYbALOEsUCgjBOOx63qutFrVLxGUQbDG2BAuPXbrpUxI0IeK/wkXkmshNE7+b33kB7WaDIwKBgQC8rYTvv1j73MCav6EpBRBunDS6Q2GfyTAD8u0NkYY91sUpXphLG/QE4GFxmLIoe1YC3pAJ2wO3YTHKc/mzkdMFxd83VWtZhr3Q+bqOVl2143Y1+OQ62uMWNihKEM/Vnd4WNUC1HUVrm7RM1ZwYgBH6gZOB/Vq45+c+8DxZG+JJDg==';
            $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmRP6PAUe2c12JmS2gKv/rEG9oonhEe1EHrrZ+GBtwbJrBHo9b4JfwT7Pp2Cmjm8T0SZLWUwv+n9yA7/FNvwTKHJBw4DWWq+Ig/29Ydv7x0Sk5HwkuDPYF7OHVoJkLGGR3PQjwtLynIhlK5aVZ8ne4jN59JLJ/iGjGnMpxUsCISn/mnekdKJcq0Rewv4R7Zv1DQe7bLmaxWMI8c7G+ac8XLFEZ2ypKuVJp4ccEV5hkXX7IehW5YL8HzojhTpMU1cwk/POkUzxNlD/PNQmPCDJvnMF/VxJ2o4p7BRcckGV1nfH8+cC+fKxYLcmxI5igj6jbIaVlv7ke2GWDGvoq9+4WQIDAQAB';
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset='UTF-8';
            $aop->format='json';
            $request = new AlipayDataBillAccountlogQueryRequest();
            $param = json_encode([
                "start_time" => date('Y-m-d', strtotime('-29 days')).' 00:00:00',
                "end_time" => date('Y-m-d H:i:s'),
                "bill_user_id" => $userId
            ], true);
            $request->setBizContent($param);
            $result = $aop->execute ( $request, null, $token);
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
             $resultCode = $result->$responseNode->code;
             if(!empty($resultCode)&&$resultCode == 10000){
                 if($result->$responseNode->total_size >= 1){
                     foreach ($result->$responseNode->detail_list as $item)
                     {
                         if($item->type == '交易' && $item->direction == '收入'){
                             ChannelAccount::where('id', $id)->update([
                                    'appid' => $userId,
                                    'secret' => $token,
                                    'aptitude' => 'company',
                            ]);
                            exit("<h1 style='font-size:36px'>配置成功</h1>");
                         }
                     }

                 }else{
                     ChannelAccount::where('id', $id)->update([
                            'aptitude' => 'person',
                    ]);
                       echo "<h1 style='font-size:36px'>未拿到账单数据;CK监控失败;请使用PC端</h1>";
                 }
            } else {
                echo "<h1 style='font-size:36px'>查询失败2</h1>";
            }

            } else {
                echo "<h1 style='font-size:36px'>配置失败1</h1>";
            }
    }

}
