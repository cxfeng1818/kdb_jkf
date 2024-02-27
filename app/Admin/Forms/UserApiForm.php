<?php

namespace App\Admin\Forms;

use App\Models\User;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Traits\LazyWidget;

class UserApiForm extends Form
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
        $update = User::where('id', $input['id'])->update(['api_ip' => $input['api_ip']]);
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
        $this->text('api_ip')->required();
        $this->text('api_key')->readOnly();
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
        $data = User::find($id);

        return [
            'api_ip'  => $data['api_ip'],
            'api_key' => $data['api_key'],
            'id' => $id,
        ];
    }
}
