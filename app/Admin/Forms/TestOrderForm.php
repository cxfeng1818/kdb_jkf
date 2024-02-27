<?php

namespace App\Admin\Forms;

use App\Models\ChannelAccount;
use Dcat\Admin\Widgets\Form;

class TestOrderForm extends Form
{
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        $http = request()->isSecure() ? 'https://' : 'http://';
        $domain = request()->host();
        $prot = request()->getPort();
        if($prot == '80' || $prot == '443'){
             $url = $http.$domain.'/testOrder';
        }else{
             $url = $http.$domain.':'.$prot.'/testOrder';
        }
        
        $key = 1243;
        $data = [
            'account'  => 1243,  //账户号
            'order'   => date('ymdHis').rand(111,999),
            'time' => time(),
            'channel_account' => $input['channel_account'],
            'amount'       =>  $input['amount'],
            'notify_url'   => $http.$domain.'/testNotify'
        ];
        $data['sign'] = $this->createSign($data, $key);
        // dd($data);
        // dd($url);
        $result = $this->curlPost($url, $data);
        // dd($result);
        $result = json_decode($result, true);
        // dd($result);
        if($result['code'] == '200'){
            return $this->response()->success($result['msg'])->script('window.open("'.$result['data']['url'].'");');
       
        }else{
            return $this->response()->error($result['msg']);
        }
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $account = ChannelAccount::pluck('name','id');
        $this->select('channel_account','选择账号')->options($account);
        $this->text('amount');
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {

        return [
            'amount'  => '1.00'
        ];
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

    public function curlPost($url, $post_data = array(), $timeout = 5, $header = "", $data_type = "") {
        $header = empty($header) ? '' : $header;
        //支持json数据数据提交
        if($data_type == 'json'){
            $post_string = json_encode($post_data);
        }elseif($data_type == 'array') {
            $post_string = $post_data;
        }elseif(is_array($post_data)){
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
}
