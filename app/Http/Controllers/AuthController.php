<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends CommonController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * This method use for authenticate the user using the APIs.
     */
    public function authenticate(Request $request)
    {
        // validation for the request parameters
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        if($validator->fails()){
            return $this->sendError(__('messages.error'), $validator->errors());
        }

        // request auth check here if its authenticate then response as authenticated
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $authUser = Auth::user();
            $success['token'] =  $authUser->createToken(env('TOKEN_NAME'))->plainTextToken;
            $success['name'] =  $authUser->name;

            return $this->sendResponse($success, __('messages.authorised'));
        } else {
            return $this->sendError(__('messages.unauthorised'), ['error' => __('messages.unauthorised')]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * This method use for create new user in application.
     */
    public function createUser(Request $request)
    {
        // validation for the request parameters
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'confirm_password' => 'required|min:8|same:password',
        ]);

        if($validator->fails()){
            return $this->sendError(__('messages.error'), $validator->errors());
        }

        // create new user for application.
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken(env('TOKEN_NAME'))->plainTextToken;
        $success['name'] =  $user->name;

        return $this->sendResponse($success, __('messages.userCreated'));
    }
}
