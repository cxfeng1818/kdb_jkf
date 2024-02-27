<?php

namespace App\Models;

use Dcat\Admin\Admin;
use Dcat\Admin\Traits\HasDateTimeFormatter;

use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class User extends Model implements Authenticatable
{
    use \Illuminate\Auth\Authenticatable,
            HasPermissions,
            HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'user';

    protected $dates = ['deleted_at'];

    protected $dateFormat = 'U';



    public function getCreatedAtAttribute($timestamp)
    {
        $date = new \DateTime();
        $timestamp = $date->setTimestamp($timestamp);
        return $timestamp->format('Y/m/d H:i');
    }

    public function getUpdatedAtAttribute($timestamp)
    {
        $date = new \DateTime();
        $timestamp = $date->setTimestamp($timestamp);
        return $timestamp->format('Y/m/d H:i');
    }

    public function getAvatar()
    {
        $avatar = $this->avatar;
        if ($avatar) {
            if (! URL::isValidUrl($avatar)) {
                $avatar = Storage::disk(config('admin.upload.disk'))->url($avatar);
            }

            return $avatar;
        }

        return admin_asset(config('admin.default_avatar') ?: '@admin/images/default-avatar.jpg');
    }

    protected static function boot()
    {
        parent::boot();
        $user = Admin::user();
        if($user){
            if($user->table == 'admin_users'){
                if($user['id'] != '1'){
                    if($user['id'] != '4'){
                        static::addGlobalScope('avaiable', function(Builder $builder) use ($user){
                            $builder->where('admin', $user['id']);
                        });
                    }
                }
            }
        }


    }
}
