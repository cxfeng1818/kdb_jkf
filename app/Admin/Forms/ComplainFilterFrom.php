<?php

namespace App\Admin\Forms;

use App\Lib\BsPaySdk\core\BsPayClient;
use App\Lib\BsPaySdk\request\V2MerchantComplaintListInfoQueryRequest;
use App\Models\Complaint;
use App\Models\ComplaintList;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class ComplainFilterFrom extends Form
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
        require app_path().'/Lib/BsPaySdk/loader.php';
        $one = Complaint::where('id', $input['id'])->first();

        $request = new V2MerchantComplaintListInfoQueryRequest();
        $request->setReqSeqId(date("YmdHis").mt_rand());
        $request->setReqDate(date("Ymd"));
        $request->setBeginDate($input['start']);
        $request->setEndDate($input['end']);
        $extendInfoMap = $this->getExtendInfos();
        $request->setExtendInfo($extendInfoMap);
        $client = new BsPayClient();
        $config = [];
        $config['huifu'] = $one['huifu_id'];
        $config['public'] = $one['public'];
        $config['private'] = $one['private'];

        $result = $client->postRequest($request, $config);
        $data = $result->getRspDatas()['data'];
        $list = json_decode($data['complaint_list'], true);

        foreach ($list as $row)
        {
            $find = ComplaintList::where('transaction_id', $row['transaction_id'])->first();
            if(!$find){
                ComplaintList::create([
                    'pid' => $input['id'],
                    'amount' => $row['amount'],
                    'apply_refund_amount' => $row['apply_refund_amount'],
                    'complaint_detail' => $row['complaint_detail'],
                    'complaint_full_refunded' => $row['complaint_full_refunded'],
                    'complaint_id' => $row['complaint_id'],
                    'complaint_media_list' => $row['complaint_media_list'],
                    'complaint_state' => $row['complaint_state'],
                    'complaint_time' => $row['complaint_time'],
                    'complainted_mchid' => $row['complainted_mchid'],
                    'first_agent_id' => $row['first_agent_id'],
                    'first_agent_name' => $row['first_agent_name'],
                    'huifu_id' => $row['huifu_id'],
                    'incoming_user_response' => $row['incoming_user_response'],
                    'mchid' => $row['mchid'],
                    'out_trade_no' => $row['out_trade_no'],
                    'payer_phone' => $row['payer_phone'],
                    'problem_description' => $row['problem_description'],
                    'problem_type' => $row['problem_type'],
                    'reg_name' => $row['reg_name'],
                    'transaction_id' => $row['transaction_id'],
                    'upper_huifu_id' => $row['upper_huifu_id'],
                    'user_complaint_times' => $row['user_complaint_times'],
                    'user_tag_list' => $row['user_tag_list'],
                ]);
            }
        }
        return $this
				->response()
				->success('操作成功')
				->refresh();
    }

    private function getExtendInfos() {
        // 设置非必填字段
        $extendInfoMap = array();
        // 分页开始位置
        $extendInfoMap["offset"]= "";
        // 分页大小
        $extendInfoMap["limit"]= "50";
        // 被诉的汇付商户ID
        $extendInfoMap["huifu_id"]= "";
        // 被诉的商户名称
        $extendInfoMap["reg_name"]= "";
        // 微信订单号
        $extendInfoMap["transaction_id"]= "";
        // 微信投诉单号
        $extendInfoMap["complaint_id"]= "";
        // 投诉状态
        $extendInfoMap["complaint_state"]= "";
        // 用户投诉次数
        $extendInfoMap["user_complaint_times"]= "";
        // 是否有待回复的用户留言
        $extendInfoMap["incoming_user_response"]= "0";
        return $extendInfoMap;
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->hidden('id');
        $this->dateRange('start', 'end', '选择时间');
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        $ids = $this->payload['id'];
        return [
            'start' => date('Y-m-d'),
            'end'   => date('Y-m-d'),
            'id' => $ids,
        ];
    }
}
