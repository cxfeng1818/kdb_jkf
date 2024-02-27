<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\ChannelAccount;
use App\Models\User;

class WechatController extends BaseController
{
    public function test()
    {
        $appid = 'wxbcd6600382b38025';
        $host = 'http://'.request()->server('HTTP_HOST').'/code';
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$host.'&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
        // dd($url);
        return redirect("Location:".$url);
    }
    
    
    public function code()
    {
        $code = request()->input('code');
        $appid = 'wxbcd6600382b38025';
        $secret = '6b190b185ed294d743a842e8b31c89ad';
        $info = $this->httpGet("https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$code}&grant_type=authorization_code");
         dump($info);
         $info = json_decode($info, true);
         dump($info);
         $openid = get_val_array('openid', $info);
         dump($openid);
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
}