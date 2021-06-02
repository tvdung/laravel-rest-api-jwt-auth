<?php

namespace App\Constants;

class ApiCode {
    public const SOMETHING_WENT_WRONG = 250;
    public const INVALID_CREDENTIALS = 251;
    public const VALIDATION_ERROR = 252;
    public const EMAIL_ALREADY_VERIFIED = 253;
    public const INVALID_EMAIL_VERIFICATION_URL = 254;
    public const INVALID_RESET_PASSWORD_TOKEN = 255;
    public const SUCCESS_REQUESTED = 200;
    public const SUCCESS_CREATED = 201;
    public const SUCCESS_ACCEPTED = 202;
    public const NON_AUTHORITATIVED = 203;
    public const NO_CONTENT = 204;
    public const RESET_CONTENT = 205;
    public const BAD_REQUEST = 400;
    public const UNAUTHENTICATED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;
    public const NOT_ACCEPTABLE = 406;
    public const PROXY_AUTHENTICATION_REQUIRED = 407;
    public const REQUEST_TIMEOUT = 408;
    public const Unprocessable_Entity = 422;
    public const Internal_Server_Error = 500;
}