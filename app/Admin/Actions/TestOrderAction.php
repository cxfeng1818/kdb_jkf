<?php

namespace App\Admin\Actions;

use App\Admin\Forms\TestOrderForm;
use Dcat\Admin\Actions\Action;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Traits\HasPermissions;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TestOrderAction extends Action
{
    /**
     * @return string
     */
	protected $title = '测试订单';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        // dump($this->getKey());

        return $this->response()->success('Processed successfully.')->redirect('/');
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

    public function render()
    {
        $form = TestOrderForm::make();
        return Modal::make()->lg()->centered()->title($this->title)->body($form)->button('<button class="btn btn-warning btn-outline"><i class="fa fa-edit">测试订单</i></button> ');
    }
}
