<?php

namespace App\Admin\Forms;

use App\Models\Channel;
use App\Models\User;
use App\Models\UserChannel;
use Dcat\Admin\Admin;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class UserChannelForm extends Form
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
        $update = UserChannel::where('id', $input['id'])->update($input);
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
        $admin = Admin::user();
          if($admin['id'] == 1 || $admin['id'] == 4){
            $user = User::get();
            $userArr = [];
            foreach ($user as $item)
            {
                if($item['type'] == 'shop'){
                    $type = '商户';
                }else{
                    $type = '码商';
                }
                $userArr[$item['id']] = $item['username'].'-'.$type;
            }
            $this->select('uid', '所属用户')->options($userArr);
        }else{
            $user = User::where('admin', $admin['id'])->get();
            $userArr = [];
            foreach ($user as $item)
            {
                if($item['type'] == 'shop'){
                    $type = '商户';
                }else{
                    $type = '码商';
                }
                $userArr[$item['id']] = $item['username'].'-'.$type;
            }
            $this->select('uid', '所属用户')->options($userArr);
        }


        $channel = Channel::get();
        $channelType = [];
        foreach ($channel as $item)
        {
            $channelType[$item['id']] = $item['name'] .'-'.$item['encode'];
        }
        $this->select('cid')->options($channelType);
        $this->rate('rate', '费率设置');
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
        $data = UserChannel::where('id', $id)->first();
        return [
            'id'   => $data['id'],
            'uid'  => $data['uid'],
            'cid'  => $data['cid'],
            'rate' => $data['rate']
        ];
    }
}
