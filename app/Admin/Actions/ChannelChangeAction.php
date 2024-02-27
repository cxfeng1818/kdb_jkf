<?php

namespace App\Admin\Actions;

use App\Admin\Forms\ChannelAccountEditForm;
use App\Admin\Forms\UserEditForm;
use Dcat\Admin\Actions\Action;
use App\Models\ChannelAccount;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ChannelChangeAction extends RowAction
{
    /**
     * @return string
     */
	protected $title = '<button class="btn btn-danger">一类投诉开启</button>';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $list = ChannelAccount::where('status', '0')->get();
        $count = 0;
        foreach ($list as $item)
        {
            if($item['msg'] == '一类投诉'){
                $count++;
                ChannelAccount::where('id', $item['id'])->update(['status' => '1', 'msg' => '']);
            }
        }
        return $this->response()->success('操作成功;已开启账号：'.$count)->refresh();
    }

    /**
     * @return string|array|void
     */
    public function confirm()
    {
        // return ['Confirm?', 'contents'];
    }

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [];
    }

    // public function render()
    // {
        // $form = ChannelAccountEditForm::make()->payload(['id' => $this->getKey()]);
        // return Modal::make()->lg()->centered()->title($this->title)->body($form)->button('<i class="fa fa-edit">编辑</i> ');
        // $form = TestOrderForm::make();
        // return Modal::make()->lg()->centered()->title($this->title)->body($form)->button('<button class="btn btn-warning btn-outline"><i class="fa fa-edit">一类投诉开启</i></button> ');
    // }
}
