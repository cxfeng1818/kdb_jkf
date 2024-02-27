<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ChannelAccountEditAction;
use App\Admin\Actions\UserEditAction;
use App\Admin\Actions\ChannelChangeAction;
use App\Admin\Actions\ChannelClearAction;
use App\Admin\Repositories\ChannelAccount;
use App\Models\Channel;
use Dcat\Admin\Admin;
use App\Models\User;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Support\LazyRenderable;
use PHPZxing\PHPZxingDecoder;

class ChannelAccountController extends AdminController
{
    protected $title  = '账号管理';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new ChannelAccount(), function (Grid $grid) {
//            $grid->setActionClass(\Dcat\Admin\Grid\Displayers\Actions::class); // 列中操作直接显示 不要默认的三个点
            $admin = Admin::user();
            if($admin['id'] != 4){
                $grid->disableCreateButton();
            }
            $grid->enableDialogCreate();
            $grid->disableViewButton();
            $grid->disableEditButton();



            $grid->column('id')->sortable();
            $grid->column('cid')->display(function($cid){
                return Channel::where('id', $cid)->value('name');
            });
            $grid->column('uid', '账号所属')->display(function($uid){
                if($uid == '0'){
                    return '系统';
                }else{
                   return User::where('id', $uid)->value('username');
                }
            });
            $grid->column('name');
            $grid->column('amount', '账号营收');
//            $grid->column('signkey');
            $grid->column('mchid', '商户号');
//            $grid->column('secret');
//            $grid->column('qrcode');
//            $grid->column('start_time');
//            $grid->column('end_time');
//            $grid->column('public_secret');
//            $grid->column('private_secret');
            $grid->column('day_limit')->display(function($day){
                if(strlen($this->msg) >= 1){
                    return '<span class="badge badge-danger" title="'.$this->msg.'">'.$day.'</span>';
                }else{
                    return '<span class="badge badge-info">'.$day.'</span>';
                }
            });
//            $grid->column('min_amount');
//            $grid->column('max_amount');
            $grid->column('status')->switch('green');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->like('name');
                $filter->equal('mchid');
                $user = User::where('type', 'code')->pluck('name', 'id');
                $filter->equal('uid', '所属码商')->select($user);
                $filter->equal('msg','错误描述');
            });

            $grid->actions(function (Grid\Displayers\Actions $actions){
                $actions->append(ChannelAccountEditAction::make());
            });
            
            $grid->tools(function (Grid\Tools  $tools)  {
                $tools->append(ChannelClearAction::make());
            });
            
            $grid->tools(function (Grid\Tools  $tools)  {
                $tools->append(ChannelChangeAction::make());
            });  
              
                
             $grid->async(true);

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
        return Show::make($id, new ChannelAccount(), function (Show $show) {
            $show->field('id');
            $show->field('cid');
            $show->field('type');
            $show->field('name');
            $show->field('mchid');
            $show->field('signkey');
            $show->field('appid');
            $show->field('secret');
            // $show->field('qrcode');
            $show->field('start_time');
            $show->field('end_time');
            $show->field('public_secret');
            $show->field('private_secret');
            $show->field('day_limit');
            $show->field('min_amount');
            $show->field('max_amount');
            $show->field('status');
//            $show->field('created_at');
//            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new ChannelAccount(), function (Form $form) {
            $channel = Channel::get();
            $channelType = [];
            foreach ($channel as $item)
            {
                $channelType[$item['id']] = $item['name'] .'-'.$item['encode'];
            }

            $form->display('id');
            $form->select('cid')
                ->options($channelType)->required();
            $form->hidden('type')->value('sys');
            $form->text('name')->required();
            $form->text('mchid')->required();
            $form->text('signkey')->required();
            $form->text('appid')->required();
            $form->text('secret')->required();
            // $form->image('qrcode')->accept('jpg,png,gif,jpeg', 'image/*')->cache();
//                                         ->autoUpload()
//                                         ->uniqueName();
        //   $form->array('qrcode', function($item){
        //          $item->text('amount', '对应金额');
        //          $item->image('qrcode')->autoUpload()->uniqueName()->maxSize(2048);
        //     })->saving(function ($data){
        //         $decoder = new PHPZxingDecoder(['try_harder' => true]);
        //         $decoder->setJavaPath("/usr/bin/java");
        //         foreach ($data as $key => $value)
        //         {
        //             $result = $decoder->decode(storage_path('app/qrcode').'/'.$value['qrcode']);
        //             $data[$key]['link'] = $result->getImageValue();
        //         }

        //         $linkArr = array_column($data, 'link');
        //         $linkArr = array_unique($linkArr);
        //         if(count($linkArr) != count($data)){
        //             throw new \Exception('二维码图片有重复;请检查');
        //         }
        //         return json_encode($data, true);
        //     });
            $form->radio('aptitude', '账号属性')->options([
                    'company' => '企业',
                    'person' => '个人',
            ])->default('company');

            $form->textarea('public_secret')->required();
            $form->textarea('private_secret')->required();
//            $form->text('time_limit', '订单间隔(秒数)')->default(0);

            $form->time('start_time')->default('00:00:00');
            $form->time('end_time')->default('23:59:59');
            $form->text('day_limit')->default(0)->placeholder('当天最大交易笔数');
            $form->text('min_amount')->default(0)->placeholder('订单最小金额');
            $form->text('max_amount')->default(50000)->placeholder('订单最大金额');

            $form->hidden('status')
                ->saving(function ($v) {
                    return $v ? 1 : 0;
                });

            if($form->isEditing()){
                if($form->input('status')){
                    $form->msg = '';
                }
            }
            $form->hidden('msg');
//            $form->display('created_at');
//            $form->display('updated_at');
        });
    }
}
