<?php

namespace App\Http\Controllers;

use App\Rules\ValidForgotPasswordCode;
use App\Rules\ValidRegistrationCode;
use App\Rules\ValidUniqueUser;
use App\Rules\ValidUpdateEmailOrPhoneCode;
use App\Services\AuthenticationService;
use App\Services\OtpCodeService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    use Resp;

    public function __construct(private readonly AuthenticationService $authenticationService, private readonly OtpCodeService   $otpCodeService)
    {
    }

    public function login(Request $request)
    {
        $validator = Validator::make(
            [
                'username' => $request->username,
                'password' => $request->password,
            ],
            [
                'username' => 'required',
                'password' => 'required',
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $login = $this->authenticationService->login($request->username, $request->password);
        if ($login) {
            return $this->apiResponseSuccess(['data' => $login]);
        }
        return $this->apiResponseFail('Something went wrong');
    }

    public function registration(Request $request)
    {
        $validator = Validator::make(
            [
                'username' => $request->username,
                'password' => $request->password,
                'code' => $request->code,
            ],
            [
                'username' => ['required',new ValidUniqueUser],
                'password' => 'required',
                'code' => ['required', new ValidRegistrationCode($request->username)],
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $register = $this->authenticationService->registration($request->username, $request->password, $request->user_information);
        if ($register) {
            $this->otpCodeService->makeUsed($request->username,$request->code,'registration',1);
            return $this->apiResponseSuccess(['data' => $register]);
        }
        return $this->apiResponseFail('User Already Exists');
    }

    public function otp_registration(Request $request)
    {
        $validator = Validator::make(
            [
                'username' => $request->username,
            ],
            [
                'username' => ['required',new ValidUniqueUser]
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $otp = $this->authenticationService->otpRegistration($request->username);
        if ($otp) {
            return $this->apiResponseSuccess(['data' => $otp]);
        }
        return $this->apiResponseFail('Something Went Wrong');
    }

    public function otp_update_email_or_phone(Request $request)
    {
        $validator = Validator::make(
            [
                'username' => $request->username,
            ],
            [
                'username' => ['required',new ValidUniqueUser]
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $otp = $this->authenticationService->otpUpdateEmailOrPhone($request->username);
        if ($otp) {
            return $this->apiResponseSuccess(['data' => $otp]);
        }
        return $this->apiResponseFail('Something Went Wrong');
    }
    public function update_email_or_phone(Request $request)
    {
        $validator = Validator::make(
            [
                'username' => $request->username,
                'code' => $request->code,
            ],
            [
                'username' => ['required',new ValidUniqueUser],
                'code' => ['required', new ValidUpdateEmailOrPhoneCode($request->username)],
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $register = $this->authenticationService->updateEmailOrPhone(auth()->user(), $request->username);
        if ($register) {
            $this->otpCodeService->makeUsed($request->username,$request->code,'update_email_or_phone',1);
            return $this->apiResponseSuccess(['data' => $register]);
        }
        return $this->apiResponseFail('User Already Exists');
    }


    public function update_user_information(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_information' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $update = $this->authenticationService->updateUserInformation(auth()->user()->id, $request->user_information);

        if ($update) {
            return $this->apiResponseSuccess(['message' => 'User information updated successfully']);
        }
        return $this->apiResponseFail('Failed to update user information');
    }

    public function change_password(Request $request)
    {
        $validator = Validator::make(
            [
                'password' => $request->password,
                'old_password' => $request->old_password,
            ],
            [
                'password' => ['required'],
                'old_password' => ['required'],
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $change_password = $this->authenticationService->changePassword(auth()->user(), $request->old_password,$request->password);
        if ($change_password) {
            return $this->apiResponseSuccess(['data' => $change_password]);
        }
        return $this->apiResponseFail('User Already Exists');
    }


    public function otp_forgot_password(Request $request)
    {
        $validator = Validator::make(
            [
                'username' => $request->username,
            ],
            [
                'username' => ['required'],
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $otp = $this->authenticationService->otpForgotPassword($request->username);
        if ($otp) {
            return $this->apiResponseSuccess(['data' => $otp]);
        }
        return $this->apiResponseFail('Something Went Wrong');
    }

    public function forgot_password(Request $request)
    {
        $validator = Validator::make(
            [
                'username' => $request->username,
                'code' => $request->code,
                'password' => $request->password,
            ],
            [
                'username' => ['required'],
                'code' => ['required', new ValidForgotPasswordCode($request->username)],
                'password' => ['required'],
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $forgot_password = $this->authenticationService->forgotPassword($request->username,$request->password);
        if ($forgot_password) {
            $this->otpCodeService->makeUsed($request->username,$request->code,'forgot_password',1);
            return $this->apiResponseSuccess(['data' => $forgot_password]);
        }
        return $this->apiResponseFail('User Already Exists');
    }

    public function logout(Request $request)
    {
        $logout = $this->authenticationService->logout();
        return $this->apiResponseSuccess(['data' => $logout['message']]);
    }
}
