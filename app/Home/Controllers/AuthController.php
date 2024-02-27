<?php

namespace App\Home\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Http\Controllers\AuthController as BaseAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseAuthController
{
    protected $view = 'home.login';


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
