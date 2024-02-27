<a href="" class="btn btn-info">添加</a>
<div class="box box-primary">
    <div class="box-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
            <tr class="text-center">
                <th class="text-center">通道设置</th>
                <th class="text-center">通道费率</th>
                <th class="text-center">通道状态</th>
                <th class="text-center">操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $item)

            <tr>
                <td class="text-center"> {{$item['cid']}}</td>
                <td class="text-center">{{$item['rate']}}</td>
                <td class="text-center">开启</td>
                <td class="text-center">
                    <a href="" class="btn btn-success">编辑</a>
                    <a href="" class="btn btn-success">删除</a>
                </td>
            </tr>
            @endforeach
            </tbody>
            <tfoot>
            </tfoot>
        </table>
    </div>
</div>
<script>
    $(function(){

    })
</script>
