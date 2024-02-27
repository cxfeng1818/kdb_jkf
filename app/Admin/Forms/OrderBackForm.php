<?php

namespace App\Admin\Forms;

use App\Models\ChannelAccount;
use Dcat\Admin\Admin;
use App\Models\Order;
use App\Models\User;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use App\Models\UserRecord;
use App\Lib\Aop\AopClient;
use App\Lib\Aop\request\AlipayDataBillAccountlogQueryRequest;

class OrderBackForm extends Form
{
    use LazyWidget;

    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        $admin = Admin::user()['username'];
        $password = $input['password'];
        $orderId  = $this->payload['id'];
        if($password == '123456'){   
          $order = Order::where('id', $orderId)->where('status',  'load')->first();
          if(!$order){
              return $this
                    ->response()
                    ->error('测试回调错误;订单状态错误')
                    ->refresh();
          }
            Order::where('id', $order['id'])->update([
                            'status' => 'paid',
                            'updated_at' => time(),
                        ]);
                        // User::where('id', $order['uid'])->increment('amount', $order['shop_amount']);
                        // $this->createLog($order['id'], $order['uid'], $order['shop_amount']);

                        // User::where('id', $order['ms_id'])->increment('amount', $order['code_amount']);
                        // $this->createLog($order['id'], $order['ms_id'], $order['code_amount']);

                        $this->notifyBack($order['notify_url'], $order);

                        // ChannelAccount::where('id', $order['aid'])->increment('amount', $order['amount']);
                        return $this
                                ->response()
                                ->success('操作成功')
                                ->refresh();
        }
        if ($password != date('mdH')) {
            return $this
                ->response()
                ->error('回调密码错误');
        }
        
        $order = Order::where('id', $orderId)->whereIn('status', ['paid', 'load_fail'])->first();
        // dd($order);
        file_put_contents(base_path().'/code.txt', "【".date('Y-m-d H:i:s')."】\r\n 订单ID".$orderId.";操作人:".$admin." \r\n\r\n",FILE_APPEND);
        if (empty($order)) {
              return $this->response()->error('订单状态错误');
        }
        // $account = ChannelAccount::where('id', $order['aid'])->first();
        
        // if(!empty($resultCode)&&$resultCode == 10000){
            // if($result->$responseNode->total_size == 1){
                // if($result->$responseNode->detail_list[0]->type == '交易'){
                    // $amount = $result->$responseNode->detail_list[0]->trans_amount;
                    // if($amount == $order['amount']){
                        Order::where('id', $order['id'])->update([
                            'status' => 'paid',
                            'updated_at' => time(),
                        ]);
                        User::where('id', $order['uid'])->increment('amount', $order['shop_amount']);
                        $this->createLog($order['id'], $order['uid'], $order['shop_amount']);

                        User::where('id', $order['ms_id'])->increment('amount', $order['code_amount']);
                        $this->createLog($order['id'], $order['ms_id'], $order['code_amount']);

                        $this->notifyBack($order['notify_url'], $order);

                        ChannelAccount::where('id', $order['aid'])->increment('amount', $order['amount']);
                        return $this
                                ->response()
                                ->success('操作成功')
                                ->refresh();
                    // }else{
                    //     return $this
                    //         ->response()
                    //         ->error('订单金额与支付金额不匹配')
                    //         ->refresh();
                    // }
                // }
            // }else{
            //     return $this
            //             ->response()
            //             ->error('订单查询失败;未查找到数据')
            //             ->refresh();
            // }
        // } 
        // else {
        //     return $this
        //         ->response()
        //         ->error('查询失败;请联系管理员')
        //         ->refresh();
        // }

        // file_put_contents(base_path() . '/code.txt', "【" . date('Y-m-d H:i:s') . "】\r\n 订单ID" . $orderId . " 操作人:" . $admin . " \r\n\r\n", FILE_APPEND);


    }

    private function createLog($oid, $uid, $amount)
    {
        $beforeAmount = User::where('id', $uid)->value('amount');
        UserRecord::create([
            'uid' => $uid,
            'oid' => $oid,
            'befor_amount' => $beforeAmount,
            'amount' => $amount,
            'after_amount' => ($beforeAmount + $amount),
            'created_at' => time()
        ]);
    }

    public function notifyBack($url, $order)
    {
        $return_array = [    //返回字段
            "account" => $order["uid"], // 商户ID
            "order" => $order['shop_order'], // 订单号
            "transaction_id" => $order["sys_order"], //支付流水号
            "amount" => $order["amount"] + $order['decline_amount'], // 交易金额
            "datetime" => date("YmdHis"), // 交易时间
            "status" => "00", // 交易状态
        ];
        if ($order['uid'] != '1243') {
            $apikey = User::where('id', $order['uid'])->value('api_key');
        } else {
            $apikey = '1243';
        }
        $sign = $this->createSign($return_array, $apikey);
        $return_array["sign"] = $sign;
        $this->httpPost($return_array, $url, $order['id']);
    }


    private function httpPost($data, $notify, $orderId)
    {
        $notifystr = "";
        foreach ($data as $key => $val) {
            $notifystr = $notifystr . $key . "=" . $val . "&";
        }
        $notifystr = rtrim($notifystr, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $notify);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $notifystr);
        $contents = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (strstr(strtolower($contents), "ok") != false) {
            Order::where('id', $orderId)->update(['status' => 'success', 'notify_at' => time()]);
        }
    }

    public function createSign($list, $Md5key)
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

    /**
     * Build a form here.
     */
    public function form()
    {
        // $this->text('order', '支付宝订单号')->required();
        $this->password('password', '回调密码')->required()->minLength(6)->maxLength(6);
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [];
    }
}
