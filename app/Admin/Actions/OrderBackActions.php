<?php

namespace App\Admin\Actions;

use App\Admin\Forms\OrderBackForm;
use Dcat\Admin\Actions\Action;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class OrderBackActions extends RowAction
{
    /**
     * @return string
     */
	protected $title = '订单回调';

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

//

    public function render()
    {
        $form = OrderBackForm::make()->payload(['id' => $this->getKey()]);
        return Modal::make()->lg()->centered()->title($this->title)->body($form)->button('<i class="fa fa-bookmark">订单回调</i> ');
    }
}
