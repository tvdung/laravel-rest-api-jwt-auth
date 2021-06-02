<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use App\Constants\ApiCode;
use App\Constants\LogStatus;
use App\Commons\Common;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public $loginAfterSignUp = true;

    public function login(Request $request)
    {
        try {
            $credentials = $request->only("email", "password");
            $token = null;
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    "status" => ApiCode::NON_AUTHORITATIVED,
                    "message" => "Unauthorized"
                ]);
            }
            Common::logip($request->ip(), 'Login', LogStatus::logined);
            $user = JWTAuth::user();
            return response()->json([
                "status" => ApiCode::SUCCESS_REQUESTED,
                "token" => $token,
                "user" => $user
            ]);
        } catch (Exception $e) {
            Common::logip($request->ip(), 'Login', LogStatus::Error);
            return response()->json([
                "status" => ApiCode::VALIDATION_ERROR,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function getAuthenticatedUser()
    {
        try {

            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());
        }

        return response()->json(compact('user'));
    }


    public function register(Request $request)
    {
        try {
            $rules = array(
                "name" => "required|string",
                "email" => "required|email|unique:users",
                "password" => [
                    'required',
                    'string',
                    'min:6',             // must be at least 6 characters in length
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&]/', // must contain a special character
                ],
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    "status" => ApiCode::VALIDATION_ERROR,
                    "user" => "Invalidation password"
                ]);
            }

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();

            // if ($this->loginAfterSignUp) {
            //     return $this->login($request);
            // }
            Common::logip($request->ip(), 'Register', LogStatus::Add);
            return response()->json([
                "status" => ApiCode::SUCCESS_REQUESTED,
                "user" => $user
            ]);
        } catch (Exception $e) {
            Common::logip($request->ip(), 'Register', LogStatus::Error);
            return response()->json([
                "status" => ApiCode::VALIDATION_ERROR,
                "user" => $e->getMessage()
            ]);
        }
    }

    public function updateUser(Request $request, $id)
    {
        try {
            $userData = $request->all();
            $user     = User::find($id);
            if ($user == null) {
                $return = ['status' => ApiCode::BAD_REQUEST, 'message' => 'ユーザーが既存ではありません。'];
                return response()->json($return);
            }
            $user->update($userData);
            Common::logip($request->ip(), 'Updated user', LogStatus::Update);
            $return = ['status' => ApiCode::SUCCESS_CREATED, 'message' => 'ユーザーを変更しました。'];
            return response()->json($return);
        } catch (Exception $e) {
            Common::logip($request->ip(), 'Updated user', LogStatus::Error);
            return response()->json(['status' => ApiCode::Internal_Server_Error, 'message' => $e->getMessage()]);
        }
    }

    public function changePassword(Request $request)
    {
        $input = $request->all();
        $rules = array(
            'old_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'min:6',             // must be at least 6 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],
            'confirm_password' => 'required|same:new_password',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(array("status" => ApiCode::BAD_REQUEST, "message" => $validator->errors()->first(), "data" => array()));
        } else {
            try {
                $user = JWTAuth::user();
                if ((Hash::check(request('old_password'), $user->password)) == false) {
                    return response()->json(array("status" => ApiCode::BAD_REQUEST, "message" => "Check your old password.", "data" => array()));
                } else if ((Hash::check(request('new_password'), $user->password)) == true) {
                    return response()->json(array("status" => ApiCode::BAD_REQUEST, "message" => "Please enter a password which is not similar then current password.", "data" => array()));
                } else {
                    $user->password = bcrypt($input['new_password']);
                    $user->save();
                    return response()->json(array("status" => ApiCode::SUCCESS_REQUESTED, "message" => "Password updated successfully.", "data" => $user));
                }
            } catch (\Exception $ex) {
                if (isset($ex->errorInfo[2])) {
                    $msg = $ex->errorInfo[2];
                } else {
                    $msg = $ex->getMessage();
                }
                return response()->json(array("status" => ApiCode::BAD_REQUEST, "message" => $msg, "data" => array()));
            }
        }
    }


    public function searchUser(Request $request)
    {
        $name = $request->name;
        $email = $request->email;
        if (empty($name) && empty($email)) {
            return User::query()->paginate(2);
        }

        $result = User::query();

        if (!empty($name)) {
            $result = $result->where('name', 'like', '%' . $name . '%');
        }

        if (!empty($email)) {
            $result = $result->where('email', '%' . $email . '%');
        }
        return $result->paginate(2);
    }

    public function logout(Request $request)
    {
        $this->validate($request, [
            "token" => "required"
        ]);

        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                "status" => ApiCode::SUCCESS_REQUESTED,
                "message" => "User logged out successfully"
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                "status" => ApiCode::BAD_REQUEST,
                "message" => $exception->getMessage()
            ]);
        }
    }
}
