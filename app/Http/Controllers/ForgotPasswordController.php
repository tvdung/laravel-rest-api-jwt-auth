<?php

namespace App\Http\Controllers;

use App\Constants\ApiCode;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;


class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;


    protected function sendResetLinkResponse(Request $request, $response)
    {
        return response(['status' => ApiCode::SUCCESS_REQUESTED, 'message' => $response]);
    }


    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return response(['status' => ApiCode::Unprocessable_Entity, 'error' => $response]);
    }
}
