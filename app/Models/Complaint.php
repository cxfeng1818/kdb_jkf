<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'complaint';
    public $timestamps = false;

}
