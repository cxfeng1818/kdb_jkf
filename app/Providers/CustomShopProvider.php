<?php
/**
 * 自定义登录控制逻辑
 */
namespace App\Providers;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Facades\Hash;

class CustomShopProvider extends EloquentUserProvider
{
    public function __construct()
    {
        $this->model = User::class;
    }

    // 用$credentials里面的用户名密码校验用户，返回true或false
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $password = $credentials['password'];
        $username = $credentials['username'];

        $one = User::where('type', 'shop')->where('username', $username)->first();
        if(!$one){
            return false;
        }
        if(Hash::check(md5($password), $one['password'])){
            return true;
        }else{
            return false;
        }
    }
}

