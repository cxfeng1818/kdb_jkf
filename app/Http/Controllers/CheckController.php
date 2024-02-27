<?php

namespace App\Http\Controllers;

use App\Lib\Aop\AlipayConfig;
use App\Lib\Aop\AopCertClient;
use App\Lib\Aop\AopClient;
use App\Lib\Aop\request\AlipayDataBillAccountlogQueryRequest;
use App\Lib\Aop\request\AlipayFundTransPagePayRequest;
use App\Lib\Aop\request\AlipayFundTransUniTransferRequest;
use App\Lib\Aop\request\AlipayUserInfoShareRequest;
use App\Lib\Aop\request\AlipayOpenAuthTokenAppRequest;
use App\Lib\Aop\request\AlipayOpenAuthTokenAppQueryRequest;
use App\Lib\Aop\request\AlipayDataBillBalanceQueryRequest;
use App\Lib\Aop\request\AlipayDataBillSellQueryRequest;
use App\Lib\Aop\request\AlipayDataBillTransferQueryRequest;
use App\Lib\Aop\request\AlipayFundTransAppPayRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckController extends Controller
{
        public function index()
        {
        }

        public function pcPage()
        {
            $privateKey = "MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQCTguZ6n84ejBzHG356/KIhKM/3UB2jTl4CLNgv5o6B+hh3mXLS+YiJ4P60jXg7oKj1i3H/org4Wn3hQjyrNxEOS6gDPi6Mle+rPeGLHsn0SyiZkdx8HCYzCToaqos034T6LJTFf9Feh7ZZLt/WomoyC71LLqn/sTpW35CYON3gYpj/dpUEc/ztCSaIiZwkg1EkoKYZIWaDL7cGPvNSL+i49yK6UKGDsMn1SBM1biIJyXCCQxWdLPJPyYhVO84RqHJq3d0DX9hR0ko9pv2ab81M8yqpl/7UIOw4VVA81on61r6zEu9ierFJ/wBiJcUhIOt+FHsty7QMFVrEeUVQnfsPAgMBAAECggEADuqGqdGtAxPB+TRDhuYy8s1wsh5s0M2k3e1w8u8KbARBIx7mKIVJFEAnIVTI2qa1TxcJsQLgQ/qKjhOnlNydoedult1b0aV7tcItI2c6nrRW56iDNh2Wk0SUlou2ReNwS+fkJ1B0eG2at8vwoICN4n+jZY3TbOKb9prmwS3G6eCG8mYpN/eFYNYT499h6P4edxpmCoIe2KocYlr6LZgU2xuWBgHLwnRo4blEr0wRXYcv0ZUG5J4rt5T80aQcaTYimvPgUIjiRKBCLz1dPwZ1gBwZMy3xC3LtOVZKOW8Lk33DabvSy4v6eT+65xoB+/r/g2AYu9yXd3Uh7WMuw7mDsQKBgQDZRVl26HfGWyEYrJDbZlWFSzjsH6JehRAsLDKqBZ9LLlaGekSWYfd7UUkv2x3crQFqbhRHhnPkFl3DHGRX6PJ1iu1y8E/6K17ntRU5ZDHj0DT9F6aR1c9luo3M22XnE9SJVK9Wwi90q2NtgWmSCmiJbUgCyW7wk4KWyaBf2j7ymQKBgQCtzjxYZZXKx2Qkzqwohtwddyy7rYsvmLi1Ne83UGr4+JbowMTHykxH4A8skrkCbrkPg3FYjUaBT+o6oerj7r65p8qV3WW/4iH/xr4EehZVtMWLWkWhUmDL+epzl3kExQo1Y1z4ViwF6TGu9+C9dhkX19+WfH5R+X8gcIxTe9iL5wKBgQCUZmy77f5ZSkcpzjxGzvfcyks/5D+H5cTtFucoAB0UQbsxsPOF77YU0al2eWhFIRnVcwiA7hHcGXbDXHIhJWft5w1WM1O9IMB67NFpWMlGvCBlOjE4bNxytGBEKxrIIWBKyL/WN14ohyOrWPgdmuRZ8X19Ac63nVlV2rOipLPYCQKBgQCcHH8FYxE9VwTzLpNhv2gcKzwJwuIeuU1GrpAUHe5HfHmHzAWz8le7w1u9zHfS4ILmlUiMojBu28nmKiNh4cJ73WNQYtEOnd2t8OQh/0rqZnl9JnMeaHC7JdnZQ3eWuijmNT5/bF8UXBWdM9YwNtBpIyQX9fA8Tx5Jk+3m2nlxhwKBgQCRRKyTZ9SGSPPn+gxrHH3h2yEcwVI+l7BmPpUaYH3/F7L/KbZ7rCDzyyxOX6snkVP0IMIz5CAjwZICLVO5p7ZlQIVt14n0TmSZie7EcdkIjJ3MaxYctGJ8yIRdKZ2CwLTz9vEuzay061aVigVc/GfwowZpFnVDNJUicOWXfh3Jzg==";
            $publicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmhcbu6qrpNLe7JcZI9gdcmyNEX/oVMHmrrTNXyoGzkh9Iu3M92N4mOWnZ9obscQt7e+dl1/BvJ96ud80ze1HHovpV8ng4ZpGQpNoZPAIYHl7UccXagr+vCkzV4xhIZvKE8qyc0ih93ndMCUZyy3k2QDqdomU01ohPInDo7MzSC9UkwUym/shCirbQGJyzxOaDnzm01peSyRIJAD42ObM+0XNWq1ZB6xDZZMZw+CffuuqF6+0KIKk+btIT2VX0tf0610e5t3Qlou2kXv0jFKT/ly9lvhw1iHZFFWkGKu/lgaEdF9NXAwOMj2MQn3Ve3sO9xIEOq/npvWnkDs3Ujy7fQIDAQAB";
            $config = new AlipayConfig();
            $config->setServerUrl("https://openapi.alipay.com/gateway.do");
            $config->setAppId("2018071560722144");
            $config->setPrivateKey($privateKey);
            $config->setFormat("json");
            $config->setAlipayPublicKey($publicKey);
            $config->setCharset("UTF-8");
            $config->setSignType("RSA2");
            $alipayClient = new AopClient($config);
            $request = new AlipayFundTransPagePayRequest();
            $arr = [
                "out_biz_no" => date('Ymd').rand(111,999),
                "trans_amount" => '1.00',
                "product_code" => "STD_APP_TRANSFER",
                "biz_scene" => "PERSONAL_PAY",
                'order_title' => "交际会红包",
//                "payer_info" => [
//                    "identity" => '2088822344299391',
//                    "identity_type" => "ALIPAY_USER_ID",
//                    "name" => "王远哲",
//                ],
            ];
//             dump($arr);
//             die;
            $json = json_encode($arr, true);
            $request->setBizContent($json);
            $result = $alipayClient->pageExecute($request);
            dd($result);
            echo $result;
        }


         public function pack()
        {
            $privateKey = 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDF3woPV6t3YdiGd+2vNFAf64wDHuyIDigP+1WpCciCDp5HMIGI3wOVcV2fXkh3I7PsGWSwKkmsGITEBPawon6frg7g4tmXzru18vhKfz0sv+n9hfHdUDpTE7EowUiPIid/pLHU7NYwQUVFnc2ZvcYHqGVrCDynG97RZjI2XYQ2JoFGr6+2RJIi7q0iIMg/8Wz11wVxo8F80xTtmZi1lqcsrG2lTTDnbw3VmHxMCJ1sUDngOii57NbN1IOipXfyczCKVqF3QL1844gWy+NHIzTPJX+e62fMXpijAGSHxibrQl0tF1JxhtqnqOe3rkGpMjMgM6eYDSGB7SoQFFUKAoOrAgMBAAECggEAOzptB3SllaGLL4z8b4JILZXTHigGgx9BrK790BBmnU+E3yhMzcx7hSMe9BJDvK3KMHTVZ8nwrXvVWdtmgC7TmjZ+q6Abo84079yfp6T4icmUX9fVMvrNyopNcDaS4o1Fp3aD6IlP/7e55YG7xjZA7Y/hc61OgnLcOm/Nveo2JgLP2DkqnBdcCenz9HSx13SBizjzndE+dLEBD6vJjRWCztgb+2Z3MXXKgT+jI0/AkXJtmk9kyiON8SecZEPqPZGpvQ65lCKeGtTiQiHVEGGznL26jRJkC2oPm6aHboS4+dA8ZZC8CXaNCLRBpa8GJdOQhbNRF9CynBY18PjoLBOWsQKBgQDhR7NR3Wk8zFITTMpNull93e+f7phsnmglgHln4zFD1eyWkeVj40d/jfKmU5SFhb3GVvRlOy3STkPsZftYYFQYLLjxafa/XhgAzbcsY3arbrePNR6xlTj3QTm4gDi9RYy7KF2twk0vmUsShIEsFgcJL5deP492bcKf+8Q1PtlW2QKBgQDg2oYzrfL3DJe/gMrDLoh0787gu2vzD4S89249QpXUqNxHNP12ZFUgYteJscoouCqqLfm1HsXNbnZTxURXK4zrTtm41hkyKE799gsOD1eOQ73GTchCnzSf296DWjIH4iq9cFvsj477Y1SndfTux4qokp03Ow2TvDz917losMpEIwKBgQDU2ar7AWiqUh6GL7rBP/3IJ8Z9ZLDNh8m9DSsoVge5IUmdZhHHk/l88kA+mJ+unJOW75eQgh6kIuCYXi7h7HnoMXE4X7cMTrn9IGEeZoe8KCr0+uqaPPSK4SzJPxTc9/ak6tnAD+Jfw8WjpGBrqBBTxIyPNxy0d7Y49GcJK+2r2QKBgGF1ljGUNflq1eNFeZ078B1vS+YQlmuV0FzvijK3R6YTQtcCWtIUDumore+axhr8KFH294LPwcCXHmaU3FhEIiJj3O7GrckVu5dMK+J+N98L8ZegYyqtQuv/KuUYFRNhrlDKAK36U2kW1rx23iEZEDqcwdQMnofoUS9db5m29xStAoGAJW6nZaCIkl6F0m274ulKzs1pBrs4XId9zoUcVj4Xn0YI6AaFp3mH7XdmVC+xd4ij+g1Ygt4Jqu3/dAdlYLkXeEGh5a/X1PSkfk78sL94Ety1uVB0hpRzCmFmXF76MlSQKjfHHh2CuGzI2Hszl8ROq1veXQzQhzyKyCY+65bHVoU=';
            $alipayConfig = new AlipayConfig();
            $alipayConfig->setPrivateKey($privateKey);
            $alipayConfig->setServerUrl("https://openapi.alipay.com/gateway.do");
            $alipayConfig->setAppId("2018071560722144");
            $alipayConfig->setCharset("UTF-8");
            $alipayConfig->setSignType("RSA2");
            $alipayConfig->setEncryptKey("");
            $alipayConfig->setFormat("json");
            $alipayConfig->setAppCertPath(base_path().'/pack/yygy.crt');
            $alipayConfig->setAlipayPublicCertPath(base_path().'/pack/zfbgy.crt');
            $alipayConfig->setRootCertPath(base_path().'/pack/gzs.crt');
            $alipayClient = new AopCertClient($alipayConfig);
            $alipayClient->isCheckAlipayPublicCert = true;

            $request = new AlipayFundTransUniTransferRequest();
            $json = json_encode([
                "out_biz_no" => date('Ymd').rand(111,999),
                "business_params" => [
                    "payer_show_name_use_alias" => "true"
                ],
                "biz_scene" => "DIRECT_TRANSFER",
                "payee_info" => [
                    "identity" => '2088822344299391',
                    "identity_type" => "ALIPAY_USER_ID",
                    "name" => "王远哲",
                ],
                "trans_amount" => '1.00',
                "product_code" => "TRANS_ACCOUNT_NO_PWD",
                "order_title" => date('mdHis')."代发"
            ], true);
            $request->setBizContent($json);
            $responseResult = $alipayClient->execute($request);
            $responseApiName = str_replace(".","_",$request->getApiMethodName())."_response";
            $response = $responseResult->$responseApiName;
            dd($response);
            if(!empty($response->code)&&$response->code==10000){
                echo("调用成功");
            }
            else{
                echo("调用失败");
            }

        }

        public function send()
        {
             $userId = request()->input('user_id');
            $aop = new AopClient ();
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
            $aop->appId = '2018071560722144';
            $aop->rsaPrivateKey = "MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQCTguZ6n84ejBzHG356/KIhKM/3UB2jTl4CLNgv5o6B+hh3mXLS+YiJ4P60jXg7oKj1i3H/org4Wn3hQjyrNxEOS6gDPi6Mle+rPeGLHsn0SyiZkdx8HCYzCToaqos034T6LJTFf9Feh7ZZLt/WomoyC71LLqn/sTpW35CYON3gYpj/dpUEc/ztCSaIiZwkg1EkoKYZIWaDL7cGPvNSL+i49yK6UKGDsMn1SBM1biIJyXCCQxWdLPJPyYhVO84RqHJq3d0DX9hR0ko9pv2ab81M8yqpl/7UIOw4VVA81on61r6zEu9ierFJ/wBiJcUhIOt+FHsty7QMFVrEeUVQnfsPAgMBAAECggEADuqGqdGtAxPB+TRDhuYy8s1wsh5s0M2k3e1w8u8KbARBIx7mKIVJFEAnIVTI2qa1TxcJsQLgQ/qKjhOnlNydoedult1b0aV7tcItI2c6nrRW56iDNh2Wk0SUlou2ReNwS+fkJ1B0eG2at8vwoICN4n+jZY3TbOKb9prmwS3G6eCG8mYpN/eFYNYT499h6P4edxpmCoIe2KocYlr6LZgU2xuWBgHLwnRo4blEr0wRXYcv0ZUG5J4rt5T80aQcaTYimvPgUIjiRKBCLz1dPwZ1gBwZMy3xC3LtOVZKOW8Lk33DabvSy4v6eT+65xoB+/r/g2AYu9yXd3Uh7WMuw7mDsQKBgQDZRVl26HfGWyEYrJDbZlWFSzjsH6JehRAsLDKqBZ9LLlaGekSWYfd7UUkv2x3crQFqbhRHhnPkFl3DHGRX6PJ1iu1y8E/6K17ntRU5ZDHj0DT9F6aR1c9luo3M22XnE9SJVK9Wwi90q2NtgWmSCmiJbUgCyW7wk4KWyaBf2j7ymQKBgQCtzjxYZZXKx2Qkzqwohtwddyy7rYsvmLi1Ne83UGr4+JbowMTHykxH4A8skrkCbrkPg3FYjUaBT+o6oerj7r65p8qV3WW/4iH/xr4EehZVtMWLWkWhUmDL+epzl3kExQo1Y1z4ViwF6TGu9+C9dhkX19+WfH5R+X8gcIxTe9iL5wKBgQCUZmy77f5ZSkcpzjxGzvfcyks/5D+H5cTtFucoAB0UQbsxsPOF77YU0al2eWhFIRnVcwiA7hHcGXbDXHIhJWft5w1WM1O9IMB67NFpWMlGvCBlOjE4bNxytGBEKxrIIWBKyL/WN14ohyOrWPgdmuRZ8X19Ac63nVlV2rOipLPYCQKBgQCcHH8FYxE9VwTzLpNhv2gcKzwJwuIeuU1GrpAUHe5HfHmHzAWz8le7w1u9zHfS4ILmlUiMojBu28nmKiNh4cJ73WNQYtEOnd2t8OQh/0rqZnl9JnMeaHC7JdnZQ3eWuijmNT5/bF8UXBWdM9YwNtBpIyQX9fA8Tx5Jk+3m2nlxhwKBgQCRRKyTZ9SGSPPn+gxrHH3h2yEcwVI+l7BmPpUaYH3/F7L/KbZ7rCDzyyxOX6snkVP0IMIz5CAjwZICLVO5p7ZlQIVt14n0TmSZie7EcdkIjJ3MaxYctGJ8yIRdKZ2CwLTz9vEuzay061aVigVc/GfwowZpFnVDNJUicOWXfh3Jzg==";
            $aop->alipayrsaPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmhcbu6qrpNLe7JcZI9gdcmyNEX/oVMHmrrTNXyoGzkh9Iu3M92N4mOWnZ9obscQt7e+dl1/BvJ96ud80ze1HHovpV8ng4ZpGQpNoZPAIYHl7UccXagr+vCkzV4xhIZvKE8qyc0ih93ndMCUZyy3k2QDqdomU01ohPInDo7MzSC9UkwUym/shCirbQGJyzxOaDnzm01peSyRIJAD42ObM+0XNWq1ZB6xDZZMZw+CffuuqF6+0KIKk+btIT2VX0tf0610e5t3Qlou2kXv0jFKT/ly9lvhw1iHZFFWkGKu/lgaEdF9NXAwOMj2MQn3Ve3sO9xIEOq/npvWnkDs3Ujy7fQIDAQAB";
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset='UTF-8';
            $aop->format='json';
            $request = new AlipayFundTransAppPayRequest ();
            $arr = [
                "out_biz_no" => date('Ymd').rand(111,999),
                "trans_amount" => '1.00',
                "product_code" => "STD_RED_PACKET",
                "biz_scene" => "PERSONAL_PAY",
                'order_title' => "交际会红包",
                "business_params" => [
                    "sub_biz_scene" => "REDPACKET",
                    "payer_binded_alipay_uid" => $userId,  // 2088341837368522
                ],
            ];
            // dump($arr);
            $json = json_encode($arr, true);
//            $request->setNotifyUrl('https://kkk.luozhansicun.cn/sendNotify');
            $request->setBizContent($json);
            $result = $aop->sdkExecute( $request);
            $res = [
                    "code" => '200',
                    "msg" => '获取成功',
                    "data" => $result,
                ];
            return response()->json($res, 200);
            // return view('ali.sdk')->with('data', htmlspecialchars($result));
        }


        public function notify()
        {
            $input = request()->input();
            Log::debug('红包回调:'. json_encode($input ,true));
        }
}
