<?php

namespace App\Http\Controllers;

use App\Rules\ValidForgotPasswordCode;
use App\Rules\ValidRegistrationCode;
use App\Rules\ValidUniqueUser;
use App\Rules\ValidUpdateEmailOrPhoneCode;
use App\Rules\ValidUser;
use App\Rules\ValidUniqueCompanyIdentification;
use App\Rules\ValidGuestLoginCode;
use App\Services\AuthenticationService;
use App\Services\EmailSendingService;
use App\Services\OtpCodeService;
use App\Services\CompanyService;
use App\Services\PlatformFeatureService;
use App\Services\UploadService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class AuthenticationController extends Controller
{
    use Resp;

    public function __construct(private readonly AuthenticationService $authenticationService, private readonly OtpCodeService   $otpCodeService,
                                private readonly UploadService $uploadService,
                                private readonly PlatformFeatureService $platformFeatureService,
                                private readonly CompanyService $companyService,
                                private readonly EmailSendingService $emailSendingService)
    {
    }


    public function exclusive_users(Request $request)
    {
        $validator = Validator::make(
            [
                'username' => $request->username,
            ],
            [
                'username' => 'required',
                ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $login = $this->authenticationService->exclusive_users($request->username);
        if ($login) {
            return $this->apiResponseSuccess(['data' => $login]);
        }
        return $this->apiResponseFail('Something went wrong');
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
        $username = strtolower(trim($request->username));
        $login = $this->authenticationService->login($username, $request->password);
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
                'code' => ['required', new ValidRegistrationCode(strtolower(trim($request->username)))],
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        $username = strtolower(trim($request->username));
        $register = $this->authenticationService->registration($username, $request->password, $request->user_information);
        if ($register) {
            $this->otpCodeService->makeUsed($username,$request->code,'registration',1);
            return $this->apiResponseSuccess(['data' => $register]);
        }
        return $this->apiResponseFail('User Already Exists');
    }

    public function register_shop(Request $request)
    {

        $validator = Validator::make(
            [
                'identification_number' => $request->identification_number,
                'company_type_id' => $request->company_type_id,
                'company_information' => $request->company_information,
            ],
            [
                'identification_number' => ['required', new ValidUniqueCompanyIdentification()],
                'company_type_id' => ['required'],
                'company_information' => ['required'],
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $password = Str::random(8);
        
        // send email to user the generated password
        $username = strtolower(trim($request->company_information['email']));
        $register = $this->authenticationService->registration($username, $password, $request->company_information);
        
        auth()->loginUsingId($register['id']);

        $company = $this->companyService->create(auth()->user(), $request->identification_number, $request->company_type_id, $request->company_information, $request->addresses);
        if ($company) {

        $otp = random_int(100000, 999999);
        $otpCode = $this->otpCodeService->create($request->company_information['email'], $otp, 'guest_login');

             $this->emailSendingService->sendDynamic(
                to: $request->company_information['email'],
                template: 'emails.company-register-one-time-pass',
                content: [
                    'otp' => $otp,
                    'email' => $request->company_information['email'],
                    'expiry_minutes' => 10,
                ],
                subject: 'Please fill the new password for your account'
            );
            
            return $this->apiResponseSuccess(['data' => $register]);
        }
        return $this->apiResponseFail('Company Already Exists');
    }

    public function password_assign(Request $request, $token)
    {
        $validator = Validator::make(
            [
                'token' => $token,
                'email' => $request->email,
                'password' => $request->password,
                'confirm_password' => $request->confirm_password,
            ],
            [
                'token' =>  ['required', new ValidGuestLoginCode($request->email)],
                'email' => 'required',
                'password' => 'required',
                'confirm_password' => 'required|same:password',
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $this->otpCodeService->makeUsed($request->email, $token, 'guest_login', 1);
        $user = $this->authenticationService->passwordAssign($request->email, $request->password);
     
        auth()->loginUsingId($user->id);
        $user = auth()->user();
        $user['access_token'] = $user->createToken('Bearer')->accessToken;
        
        return $this->apiResponseSuccess(['data' => $user]);
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
        $username = strtolower(trim($request->username));
        $phone = trim($request?->user_information['phone']);
        $otp = $this->authenticationService->otpRegistration($username,$phone);
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
        $username = strtolower(trim($request->username));
        $otp = $this->authenticationService->otpUpdateEmailOrPhone($username);
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
        $username = strtolower(trim($request->username));
        $register = $this->authenticationService->updateEmailOrPhone(auth()->user(), $username);
        if ($register) {
            $this->otpCodeService->makeUsed($username,$request->code,'update_email_or_phone',1);
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
        $username = strtolower(trim($request->username));
        $otp = $this->authenticationService->otpForgotPassword($username);
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
                'username' => ['required', new ValidUser()],
                'code' => ['required', new ValidForgotPasswordCode($request->username)],
                'password' => ['required'],
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        $username = strtolower(trim($request->username));
        $forgot_password = $this->authenticationService->forgotPassword($username,$request->password);
        if ($forgot_password) {
            $this->otpCodeService->makeUsed($username,$request->code,'forgot_password',1);
            return $this->apiResponseSuccess(['data' => $forgot_password]);
        }
        return $this->apiResponseFail('User Already Exists');
    }
    public function upload_images(Request $request)
    {
        $validator = Validator::make(
            [
                'images' => $request->file('images'),
                'image_for' => $request->image_for,
            ],
            [
                'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Add image validation rules for each image
                'image_for' => ['required','in:individual,store,pawnshop,stock_exchange'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        $images = $request->file('images');
        $user = auth()->user();
        return $this->apiResponseSuccess(['data' => $this->uploadService->uploadProductImages($images,$user,$request->image_for)]);
    }

    public function check_user()
    {
        $user = auth()->user();
        $user->setAttribute(
            'platform_features',
            $this->platformFeatureService->findByUserId($user->id)
        );
        return $user->append('information');
    }

    public function logout(Request $request)
    {
        $logout = $this->authenticationService->logout();
        return $this->apiResponseSuccess(['data' => $logout['message']]);
    }
}
