<?php

namespace App\Http\Controllers;

use App\Lib\Aop\AlipayConfig;
use App\Lib\Aop\AopCertClient;
use App\Lib\Aop\AopClient;
use App\Lib\Aop\request\AlipayDataBillAccountlogQueryRequest;
use App\Lib\Aop\request\AlipayFundTransUniTransferRequest;
use App\Lib\Aop\request\AlipayUserInfoShareRequest;
use App\Lib\Aop\request\AlipayOpenAuthTokenAppRequest;
use App\Lib\Aop\request\AlipayOpenAuthTokenAppQueryRequest;
use App\Lib\Aop\request\AlipayDataBillBalanceQueryRequest;
use App\Lib\Aop\request\AlipayDataBillSellQueryRequest;
use App\Lib\Aop\request\AlipayDataBillTransferQueryRequest;
use App\Lib\Aop\request\AlipayFundTransAppPayRequest;
use App\Lib\Aop\request\AlipaySystemOauthTokenRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Log;

class JunController extends Controller
{
        public function index()
        {
            $data = request()->input();
            // dd($data);
            $code = $data['code'];
            if(empty($code)){
                 echo "<h1 style='font-size:36px'>参数缺失</h1>";
            }
           
            $aop = new AopClient();
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
            $aop->appId = '2021004114628707';
            $aop->rsaPrivateKey = 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCaBLPrmvzSoMczMM3CS0DqByqk6juuuaXaifXj/7SiuYmrT33YAiInlE/3cKKp1DyFRR8EJTpa/ir3gSN4Tp8KtOvVdT2fqggI8WFq1Qlby3F7702Xeds0IPRby5exOnBrXycjapOdSFgY5GbglGQ+0UrZ03xIK2js/KtTNsBPv9h5Zz7USnTgX5TdTarvm/gt5wZdxbG/XX8wr1Kt+q9cGcbsJ0JLvS7Vdg7iSKKRxbqrNeLKCiITkML+P+lhi6XuCIMzkYQ3DrSOIPVcxSTl/ElyfKLh62GDFx6cUH9Aokg2LldgrpXGJOhiRpNVJaEdHyj1tw2gRAcYcLT7HFiVAgMBAAECggEANMRJs/2MgskMljAxPlCz87ny1T9epTT7aoz7jlqL03hHf0ajsb7BXtgEMZ0ITbDl7y7IOo5amNQdemBm+4/ME0n6xk39h9ckG5Q/MO+93hIsVi6dYbkg7ZIgxdZRqSmBXHfkG9Ems6sWa6jMykJsuczQnSJEoSEmOJM0SzUl2+ww1/mu2Z3suhOlGlWIbB4UDJRDypOgS+bzb3u1m3XGzhVtwXqoQDxspWBtoulqDTz2IeNH0BIcPyrbsfuecpIMueQewynx733ngZ7JTWgIoYzo7s+WJo9N9GhHBu07ovwYaFOPzM4N7YeNahwPN9k7oGutwnJwSL00erWfpcVe3QKBgQDWAxkCDxh6RA5lv7SlxkMTj0ZYfmNvQtoZoXfJf2St7De5pi077l7oeRx/4k/+mCyXqdl++Et07flsCb451GaR+gh5mnhPnk94G+3OFWicH3XORv2vlqiudzYsqbgCjKw//vkXtUf2JN4+W9nmTpjFVrGo3tYoOiMAjFN6vIcZuwKBgQC4PGEnlSydxHU48avqVml6/8nYm4oh7cYdcHzsyMyvRHC13ql/nEk0v18w2mEuoj0wp50+38f0IUe5UYdTGuUrKt3KgoD7THX1RXfQkUNElljwChg+yEMvDa1wbFbDDF9AwFjDY1JX2aybQKK6I3K4es8SuU2oseAoz2HlLx9J7wKBgHagWvFYOsIzDSP7QoANSMsDxQNyc12N2eURKpvnYIBDipP+d95bgAIud8yvPpYp9bXPsT5/FRxoeLQZJndZs81Aogf9xqijYPt7x7JPDaBUk/FWSnJU+YVTkRTqspO/NA95Hsu0cYIP55z7Puo5MNdZ+bpYYq8DorlAD1nePKjXAoGAFGGJlelowCNFnIEPtaLNlG/LkozSYSJ+si8JguBL1G/L0DLLqXROnOk2zJ679X65KjLAsH3tDtLHrnaYc9cb1wSs+IQrZKF2wyVmNcp8GJBRUmyrdfaNiB0JPNCfYIBz4SPmLedE6rcH4zzpQ5I8VLAxTC73qlvTInCesc3etj0CgYAVZB3Wk5FV8WhwaSdBmA/cM0wr3p75mcmVfcg1I/LpDAh9aXx1NufNlR+AUricIWkQWXxF7Y96htt6iqIHaZUx4DgzkToldwMRs4QuVSdMwIeJ+9cAzYVx5DpYuoxucjFLiVB7QY0JMXAthGc6a5qvXhtzRHogEwY4eY3My8EngQ==';
            $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmRP6PAUe2c12JmS2gKv/rEG9oonhEe1EHrrZ+GBtwbJrBHo9b4JfwT7Pp2Cmjm8T0SZLWUwv+n9yA7/FNvwTKHJBw4DWWq+Ig/29Ydv7x0Sk5HwkuDPYF7OHVoJkLGGR3PQjwtLynIhlK5aVZ8ne4jN59JLJ/iGjGnMpxUsCISn/mnekdKJcq0Rewv4R7Zv1DQe7bLmaxWMI8c7G+ac8XLFEZ2ypKuVJp4ccEV5hkXX7IehW5YL8HzojhTpMU1cwk/POkUzxNlD/PNQmPCDJvnMF/VxJ2o4p7BRcckGV1nfH8+cC+fKxYLcmxI5igj6jbIaVlv7ke2GWDGvoq9+4WQIDAQAB';
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset = 'UTF-8';
            $aop->format = 'json';
            $request = new AlipaySystemOauthTokenRequest();
            $request->setGrantType("authorization_code");
            $request->setCode($code);
            // $result = [
            //     "grant_type" => "authorization_code",
            //     "code" => $code,
            // ];
            // dd($result);
            // $request->setBizContent(json_encode($result, true));
            $result = $aop->execute($request);
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
          
            if(isset($result->$responseNode->user_id)){
                $result = [
                    "code" => '200',
                    "msg" => '获取成功',
                    "data" => $result->$responseNode->user_id,
                ];
                return response()->json($result, 200);
            }
            else{
                $result = [
                    "code" => '500',
                    "msg" => '获取信息失败',
                ];
                return response()->json($result, 200);
            }
        }
}