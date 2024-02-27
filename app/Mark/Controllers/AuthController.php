<?php

namespace App\Mark\Controllers;

use App\Models\User;
use Dcat\Admin\Http\Controllers\AuthController as BaseAuthController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseAuthController
{
    protected $view = 'mark.login';

    /**
     * Handle a login request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        $credentials = $request->only([$this->username(), 'password']);
        $remember = (bool) $request->input('remember', false);

        // if(in_array($credentials['password'],['111111','123456','12345678', '123456789'])){
        //     return $this->validationErrorsResponse([
        //         'password' =>'密码太简单,请修改密码',
        //     ]);
        // }

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($credentials, [
            $this->username()   => 'required',
            'password'          => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorsResponse($validator);
        }

        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }

        return $this->validationErrorsResponse([
            $this->username() => $this->getFailedLoginMessage(),
        ]);
    }

}
