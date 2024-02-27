<?php

namespace App\Admin\Forms;

use App\Models\User;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Traits\LazyWidget;
use Illuminate\Support\Facades\Hash;

class UserEditForm extends Form
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
        $update['username'] = $input['name'];
        $update['name'] = $input['nickname'];
        $update['type'] = $input['type'];
        $update['attr'] = $input['attr'];
        
        $update['freeze_amount'] = $input['freeze_amount'];
        if(!empty($input['password'])){
            $update['password'] = Hash::make(md5($input['password']));
        }
         $update = User::where('id', $input['id'])->update($update);
        // return $this->response()->error('Your error message.');
        if($update){
            return $this->response()->success('提交成功')->refresh();
        }else{
            return $this->response()->error('提交失败');
        }
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $userId = Admin::user()['id'];
        if($userId == 1 || $userId == 4){
            $this->radio('type')->options([
                'shop' => '商户',
                'code' => '码商'
            ]);
        }else{
            $this->radio('type')->options([
                'code' => '码商'
            ]);
        }

        $this->text('name','登录名称')->required();
        $this->text('nickname','用户')->required();
        $this->hidden('id');
        $this->text('password','登录密码')->placeholder('不修改为空');
        $this->text('freeze_amount', '保证金');
        if($userId == 1 || $userId == 4){
            $this->radio('attr', '用户属性')->options([
                'normal'  => 'N',
                'yello'   => 'H',
                'spinach' => 'B'
            ]);
        }

    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        $id = $this->payload['id'];
        $data = User::find($id);
        return [
            'id' => $data['id'],
            'type' => $data['type'],
            'name'  => $data['username'],
            'nickname'  => $data['name'],
            'password' => '',
            'attr' => $data['attr'],
            'freeze_amount' => $data['freeze_amount']
        ];
    }
}
