<?php

namespace App\Http\Controllers;

use App\Constants\ApiCode;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{

    use ResetsPasswords;

    protected function sendResetResponse(Request $request, $response)
    {
        return response(['status' => ApiCode::SUCCESS_REQUESTED, 'message' => trans($response)]);
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        return response(['status' => ApiCode::Unprocessable_Entity, 'error' => trans($response)]);
    }
}
