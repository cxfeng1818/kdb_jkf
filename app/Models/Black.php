<?php

namespace App\Models;

// use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Black extends Model
{
// 	use HasDateTimeFormatter;
    protected $table = 'black';

    public $dateFormat = 'U';
    
    protected $guarded=['id'];

    //  public function getCreatedAtAttribute($timestamp)
    // {
    //     $date = new \DateTime();
    //     $timestamp = $date->setTimestamp($timestamp);
    //     return $timestamp->format('Y-m-d H:i');
    // }

    // public function getUpdatedAtAttribute($timestamp)
    // {
    //     $date = new \DateTime();
    //     $timestamp = $date->setTimestamp($timestamp);
    //     return $timestamp->format('Y-m-d H:i');
    // }
}
