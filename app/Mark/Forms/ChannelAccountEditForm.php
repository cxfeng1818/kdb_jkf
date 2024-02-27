<?php

namespace App\Mark\Forms;

use App\Models\Channel;
use App\Models\ChannelAccount;
use App\Models\User;
use App\Models\UserChannel;
use Dcat\Admin\Admin;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use PHPZxing\PHPZxingDecoder;

class ChannelAccountEditForm extends Form
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
        $update = ChannelAccount::where('id', $input['id'])->update($input);
        // return $this->response()->error('Your error message.');
        if($update){
            return $this
                ->response()
                ->success('提交成功')
                ->refresh();
        }else{
            return $this
                ->response()
                ->error('提交失败');
        }

    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $user = Admin::user();
        $channel = UserChannel::where('uid', $user['id'])->with(['channel'])->get();
        $channelType = [];
        foreach ($channel as $item)
        {
            $channelType[$item->channel['id']] = $item->channel['name'] .'-'.$item->channel['encode'];
        }
        $this->select('cid', '所属通道')->options($channelType)->required();
        $this->text('name', '账号名称')->required();
        $this->hidden('mchid', '商户号');
        $this->hidden('signkey', '渠道号');
        $this->hidden('appid', 'Appid');
        $this->hidden('secret', '密钥');
        $this->array('qrcode', function($item){
            $item->text('amount', '对应金额');
            $item->image('qrcode')->uniqueName()->maxSize(2048)->url('account');
        })->saving(function ($data){
                $decoder = new PHPZxingDecoder(['try_harder' => true]);
                $decoder->setJavaPath("/usr/bin/java");
                
                foreach ($data as $key => $value)
                {
                     if(empty($value['qrcode'])){
                         throw new \Exception('请检查图片是否上传');
                    }
                    $result = $decoder->decode(storage_path('app/qrcode').'/'.$value['qrcode']);
                    if(!empty($result)){
                        $data[$key]['link'] = $result->getImageValue();
                    }else{
                        throw new \Exception($value['amount'].'金额;二维码解析错误.请重新生成');
                    } 
                  
                }
                $linkArr = array_column($data, 'link');
                $linkArr = array_unique($linkArr);
                if(count($linkArr) != count($data)){
                    throw new \Exception('二维码图片有重复;请检查');
                }
                
                return json_encode($data, true);
            });
//        $this->image('qrcode', '二维码')->accept('jpg,png,gif,jpeg', 'image/*')
//                                     ->autoUpload()
//                                     ->uniqueName()->url('account');
        $this->hidden('aptitude', '账号属性')->options([
                        'company' => '企业',
                        'person' => '个人',
        ]);
        $this->hidden('public_secret', '公钥');
        $this->hidden('private_secret', '私钥');
        $this->time('start_time', '开始时间');
        $this->time('end_time', '结束时间');
        $this->text('day_limit', '当天最大交易笔数')->placeholder('当天最大交易笔数');
        $this->text('min_amount', '订单最小金额')->placeholder('订单最小金额');
        $this->text('max_amount', '订单最大金额')->placeholder('订单最大金额');
        $this->hidden('id');
        // $this->saving(function (Form $form) {
        //         $linkArr = array_column($form->qrcode, 'link');
        //         $linkArr = array_unique($linkArr);
        //         if(count($linkArr) != count($form->qrcode)){
        //             return $form->response()->error('二维码图片有重复;请检查');
        //         }
        //   });
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        $id = $this->payload['id'];
        $account = ChannelAccount::find($id);
        return [
            'id' => $account['id'],
            'cid'  => $account['cid'],
            'name' => $account['name'],
            'mchid' => $account['mchid'],
            'signkey' => $account['signkey'],
            'appid' => $account['appid'],
            'secret' => $account['secret'],
//            'qrcode' => $account['qrcode'],
            'aptitude' => $account['aptitude'],
            'time_limit' => $account['time_limit'],
            'start_time' => $account['start_time'],
            'end_time' => $account['end_time'],
            'public_secret' => $account['public_secret'],
            'private_secret' => $account['private_secret'],
            'day_limit' => $account['day_limit'],
            'min_amount' => $account['min_amount'],
            'max_amount' => $account['max_amount'],
            'uid' => $account['uid'],
            'qrcode' => json_decode($account['qrcode'], true)
            
        ];
    }
}
