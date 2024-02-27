<?php

namespace App\Admin\Forms;

use App\Models\Channel;
use App\Models\ChannelAccount;
use App\Models\User;
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
        unset($input['type']);
        // dump($input);
        $update = ChannelAccount::where('id', $input['id'])->update($input);
        if($update){
            return $this
                ->response()
                ->success('提交成功')
                ->refresh();
        }else{
            return $this
                ->response()
                ->error('提交失败')
                ->refresh();
        }
        // return $this->response()->error('Your error message.');


    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $id = $this->payload['id'];
        $account = ChannelAccount::find($id);
        $userData = User::where('type', 'code')->pluck('username','id');
        if($account['type'] == 'code'){
            $this->text('type', '账号类型')->default('码商')->readOnly();
            $this->select('uid', '账号所属')->options($userData);
        }
        if($account['type'] == 'sys'){
            $this->text('type', '账号类型')->default('系统')->disable();
        }
        $channel = Channel::get();
        $channelType = [];
        foreach ($channel as $item)
        {
            $channelType[$item['id']] = $item['name'] .'-'.$item['encode'];
        }
        $this->select('cid', '所属通道')->options($channelType)->required();
        $this->text('name')->required();
            $this->text('mchid')->required();
            $this->text('signkey')->required();
            $this->text('appid')->required();
            $this->text('secret')->required();
//        $this->image('qrcode')->accept('jpg,png,gif,jpeg', 'image/*')
//                                     ->autoUpload()
//                                     ->uniqueName();
        // $this->array('qrcode', function($item){
        //          $item->text('amount', '对应金额');
        //          $item->image('qrcode')->autoUpload()->uniqueName()->maxSize(2048)->url('channel_account');
        //     })->saving(function ($data){
        //         $decoder = new PHPZxingDecoder(['try_harder' => true]);
        //         $decoder->setJavaPath("/usr/bin/java");
        //         foreach ($data as $key => $value)
        //         {
        //             $result = $decoder->decode(storage_path('app/qrcode').'/'.$value['qrcode']);
        //             // dd($result);
        //             $data[$key]['link'] = $result->getImageValue();
                  
        //         }
                
        //         $linkArr = array_column($data, 'link');
        //         $linkArr = array_unique($linkArr);
        //         if(count($linkArr) != count($data)){
        //             throw new \Exception('二维码图片有重复;请检查');
        //         }
        //         return json_encode($data, true);
        //     });
        $this->radio('aptitude', '账号属性')->options([
                        'company' => '企业',
                        'person' => '个人',
        ]);
        $this->textarea('public_secret')->required();
        $this->textarea('private_secret')->required();
//        $this->text('time_limit', '订单间隔(秒数)');
        $this->time('start_time');
        $this->time('end_time');
        $this->text('day_limit')->placeholder('当天最大交易笔数');
        $this->text('min_amount')->placeholder('订单最小金额');
        $this->text('max_amount')->placeholder('订单最大金额');
        $this->hidden('id');
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
            'cid'  => $account['cid'],
            'name' => $account['name'],
            'mchid' => $account['mchid'],
            'signkey' => $account['signkey'],
            'appid' => $account['appid'],
            'secret' => $account['secret'],
            'qrcode' => $account['qrcode'],
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
            'id' => $account['id'],
        ];
    }
}
