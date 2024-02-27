<?php

namespace App\Models;

use Dcat\Admin\Admin;
use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FreezeLog extends Model
{
    use HasDateTimeFormatter;

    protected $table = 'freeze_log';
//    protected $dateFormat = 'U';
    public $timestamps = false;
    protected $guarded=['id'];


}
