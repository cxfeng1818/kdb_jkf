<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\UserChannelEditAction;
use App\Admin\Actions\UserChoseAction;
use App\Admin\Actions\UserEditAction;
use App\Admin\Repositories\UserChannel;
use App\Models\Channel;
use App\Models\User;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserChannelController extends AdminController
{
    protected $title = '用户通道';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new UserChannel(), function (Grid $grid) {
//            $grid->setActionClass(\Dcat\Admin\Grid\Displayers\Actions::class); // 列中操作直接显示 不要默认的三个点
            $grid->enableDialogCreate();
            $grid->disableViewButton();
            $grid->disableEditButton();
//            $grid->disableBatchDelete();
//            $grid->disableBatchActions();
//            $grid->disableFilterButton();
            $grid->disableRowSelector();
            $grid->toolsWithOutline(false);

            $grid->column('id')->sortable();
            $grid->column('uid')->display(function ($uid){
                $name = User::where('id', $uid)->value('name');
                $type = User::where('id', $uid)->value('type');
                if($type == 'shop'){
                    $type = '商户';
                }else{
                    $type = '码商';
                }
                return $type.'['.$name.']';
            });

            $grid->column('cid')->display(function ($cid){
                $name = Channel::where('id', $cid)->value('name');
                $code = Channel::where('id', $cid)->value('encode');
                return $name.'-'.$code;
            });;

            $grid->column('rate')->display(function($rate){
                return $rate.'%';
            });
            $grid->column('status')->switch('green');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $user = User::pluck('username', 'id');
                $filter->equal('uid')->select($user);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->append(UserChannelEditAction::make());
            });

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
        return Show::make($id, new UserChannel(), function (Show $show) {
                $show->field('id');
                $show->field('uid');
                $show->field('cid');
                $show->field('rate');
                $show->field('status');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new UserChannel(), function (Form $form) {
            $form->display('id');
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
                    $userArr[$item['id']] = $item['name'].'-'.$type;
                }
                $form->select('uid', '所属用户')->options($userArr)->required();
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
                $form->select('uid', '所属用户')->options($userArr)->required();
            }


            $channel = Channel::get();
            $channelType = [];
            foreach ($channel as $item)
            {
                $channelType[$item['id']] = $item['name'] .'-'.$item['encode'];
            }
            $form->select('cid')->options($channelType)->required();
            $form->rate('rate')->required();
            $form->hidden('status')
                ->customFormat(function ($v) {
                    return $v == '打开' ? 1 : 0;
                })
                ->saving(function ($v) {
                    return $v ? 1 : 0;
                })->default(1);
        });
    }
}
