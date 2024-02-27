<?php

namespace App\Admin\Controllers;

use App\Admin\Metrics\Examples;
use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Order;
use App\Models\User;
use App\Models\Complaint;
use App\Models\ComplaintList;
use Dcat\Admin\Http\Controllers\Dashboard;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        $start = strtotime(date('Ymd 00:00:00'));
        $end = strtotime(date('Ymd 23:59:59'));
        $msId = Order::distinct()->whereBetween('created_at', [$start, $end])->pluck('ms_id');
        $cost = Order::whereBetween('created_at', [$start, $end])->where('status', 'success')->sum('cost_amount');
        $html = '';
        foreach ($msId as $sid)
        {
            $sAmount = Order::where('ms_id', $sid)->whereBetween('created_at', [$start, $end])->where('status', 'success')->sum('amount');
            $name = User::where('id', $sid)->value('name');
            $html .= '<a class="btn btn-sm bg-blue-2" style="color: white !important;margin-right: 10px;" >'.$name.'：'.$sAmount.'</a>';
        }
        $tousu = '';
        $list = Complaint::get();
        foreach ($list as $item)
        {
            $count = ComplaintList::where('pid', $item['id'])->whereBetween('created_at', [$start, $end])->count();
            $tousu .= '<a class="btn btn-sm bg-danger" style="color: white !important;margin-right: 10px;" >'.$item['name'].'：'.$count.'</a>';
        }
        
        return $content
            ->header('控制台')
            ->row('<div style="position: absolute;top: -3.5rem;left: 9rem;">'.$html.$tousu.'</div><div style="position: absolute;right: 0;top:-3.5rem"><a class="btn btn-sm bg-olive" style="color: white !important;margin-right: 10px;" >系统费用:'.$cost.'</a></div>')
            ->body(function (Row $row) {
                $channel = Channel::where('status', '1')->get();
                //  $user = User::where("type", 'shop')->get();
                 $start = strtotime(date('Ymd 00:00:00'));
                 $end = strtotime(date('Ymd 23:59:59'));
                 $user = Order::distinct()->whereBetween('created_at', [$start, $end])->select('uid')->get();
                 $row->column(12, function (Column $column) use ($user) {
                    $column->row(function (Row $row) use ($user){
                         foreach ($user as $item){
                             if($item['uid'] == '1243'){
                                continue;
                             }
                             $row->column(3, new Examples\UserShow( $item['uid']));
                         }
                    });
                });
                // $row->column(6, function (Column $column) use ($channel){
                //     $column->row(function (Row $row) use ($channel){
                //         foreach ($channel as $item){
                //             $row->column(6, new Examples\NewDevices( $item['id']));
                //         }

                //     });
                // });
            });
    }


    public function test()
    {
        echo 1232133;
    }
}
