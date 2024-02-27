<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\UserApiAction;
use App\Admin\Actions\UserEditAction;
use App\Admin\Repositories\User;
use App\Models\UserChannel;
use Carbon\Carbon;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Hash;

class UserController extends AdminController
{
    protected $title = '用户列表';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new User(), function (Grid $grid) {
            $userId = Admin::user()['id'];
//            $grid->setActionClass(\Dcat\Admin\Grid\Displayers\Actions::class); // 列中操作直接显示 不要默认的三个点
//            $grid->showColumnSelector(); // 开启右边的字段显示选择

            $grid->column('id')->sortable();
            $grid->column('type')->display(function($type){
                if($type == 'shop'){
                    return '<span class="label bg-blue">商户</span>';
                }else{
                    return '<span class="label bg-green">码商</span>';
                }
            });
            $grid->column('name')->display(function($name){
                if($this->attr == 'yello')
                {
                    return "<span class='text-warning'>".$name."</span>";
                }else if($this->attr == 'normal'){
                    return $name;
                }else if($this->attr == 'spinach'){
                    return "<span class='text-primary'>".$name."</span>";
                }
                dump($this->attr);
            });
            $grid->column('amount');
            $grid->column('freeze_amount', '保证金');
            $grid->column('created_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                 $filter->equal('id');
            });
            if($userId == 1 || $userId == 4){
                $grid->actions(function (Grid\Displayers\Actions $actions){
                    $actions->append(UserApiAction::make());
                });
            }

            $grid->enableDialogCreate();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableBatchDelete();
            $grid->disableBatchActions();
            $grid->disableFilterButton();
            $grid->disableRowSelector();

            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->append(UserEditAction::make());
            });


            $grid->quickSearch('username')->placeholder('输入用户名');

            $grid->toolsWithOutline(false);

        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new User(), function (Show $show) {
            $show->field('id');
            $show->field('type');
            $show->field('username');
            $show->field('password');
            $show->field('amount');
            $show->field('freeze_amount');
            $show->field('api_ip');
            $show->field('api_key');
            $show->field('token');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new User(), function (Form $form) {
            $userId = Admin::user()['id'];


            $form->display('id');
            if($userId != 3){
                $form->radio('type')->options([
                    'shop' => '商户',
                    'code' => '码商'
                ]);
            }else{
                $form->radio('type')->options([
                    'code' => '码商'
                ])->default('code');
            }

            $form->text('username', "登录账号");
            $form->text('name', '用户名');
            $form->text('password')->minLength(6);
            if($userId != 3){
                $form->radio('attr', '用户属性')->options([
                    'normal'  => 'N',
                    'yello'   => 'H',
                    'spinach' => 'B'
                ])->default('normal');
            }

//            $form->text('amount');
            $form->text('freeze_amount', '保证金')->default("0.00");
//            $form->text('api_ip');
            $form->hidden('api_key');
            $form->hidden('admin')->default( $userId );
//            $form->text('token');
//
//            $form->display('created_at');
//            $form->display('updated_at');
            $form->disableResetButton();
            $form->disableViewCheck();
            $form->disableEditingCheck();
            $form->disableCreatingCheck();

            $form->submitted(function(Form $form){
                $form->api_key = sha1($form->username.date('Ymd'));
                $form->password = Hash::make(md5($form->password));
            });

//            if ($form->isCreating()) {
//                $form->api_key = sha1($form->username.date('Ymd'));
//
//            }
        });
    }

    public function destroy($id)
    {
        \App\Models\User::where('id', $id)->delete();
        UserChannel::where('uid', $id)->delete();
        return response()->json([
            "status" => true,
            "data" => [
                "alert" => true,
                "message" => '删除成功！'
            ]
        ]);
    }

}
