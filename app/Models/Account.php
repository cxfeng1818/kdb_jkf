<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'account';

    public $dateFormat = 'U';
}
