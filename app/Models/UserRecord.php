<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserRecord extends Model
{
    protected $table = 'user_record';
    public $timestamps = false;
    protected $guarded=['id'];

}
