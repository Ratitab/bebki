<?php

namespace App\Services;

use App\Traits\Resp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class AuthenticationService
{
    use Resp;

    public function __construct(private readonly UserService    $userService, private readonly UserInformationService $userInformationService,
                                private readonly OtpCodeService $otpCodeService)
    {
    }

    public function exclusive_users($username)
    {
        return $this->userInformationService->addExclusiveUser($username);
    }
    public function login($username, $password)
    {
        $user = $this->userInformationService->findUserId($username);

        if (!$user || !\Hash::check($password, $user->password)) {
            return false;
        }

        $authUser = auth()->loginUsingId($user->user_id);

        $existingToken = $authUser->tokens()
            ->where('name', 'Bearer')
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingToken) {
            return Cache::remember('user_token_' . $user->user_id, now()->addDays(30), function () use ($authUser) {
                return $authUser->createToken('Bearer')->accessToken;
            });
        }

        return $authUser->createToken('Bearer')->accessToken;
    }

    public function registration($username, $password, $user_information)
    {
        $user = $this->userService->create($username, $password);
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user_information['email'] = $username;
        } elseif (preg_match('/^\+?[0-9]{7,15}$/', $username)) {
            $user_information['phone'] = $username;
        }
        $this->userInformationService->create($user->id, $user_information);
        $user['access_token'] = $user->createToken('Bearer')->accessToken;
        return $user;
    }

    public function otpRegistration($username,$phone=null)
    {
        $otp = random_int(100000, 999999);

        // Create the OTP code in the service
        $this->otpCodeService->create($username,$otp, 'registration',$phone);

        // Email content
        $emailContent = "Hello,\n\nYour OTP code for registration is: $otp\n\nIf you did not request this, please ignore this email.\n\nThank you!";

        // Send OTP via email
        Mail::raw($emailContent, function ($message) use ($username) {
            $message->to($username) // Assuming $username is the email address
            ->subject('Your OTP Code for Registration');
        });

        return true;
    }

    public function otpUpdateEmailOrPhone($username)
    {
        $otp = random_int(100000, 999999);
        $this->otpCodeService->create($username, $otp, 'update_email_or_phone');
        // Email content
        $emailContent = "Hello,\n\nYour OTP code for updating email is: $otp\n\nIf you did not request this, please ignore this email.\n\nThank you!";

        Mail::raw($emailContent, function ($message) use ($username) {
            $message->to($username) // Assuming $username is the email address
            ->subject('Your OTP Code for Registration');
        });
        return true;
    }

    public function otpForgotPassword($username)
    {
        $otp = random_int(100000, 999999);
        $this->otpCodeService->create($username, $otp, 'forgot_password');
        $emailContent = "Hello,\n\nYour OTP code for forgetting pass is: $otp\n\nIf you did not request this, please ignore this email.\n\nThank you!";
        Mail::raw($emailContent, function ($message) use ($username) {
            $message->to($username) // Assuming $username is the email address
            ->subject('Your OTP Code for Registration');
        });
        return true;
    }

    public function updateEmailOrPhone($user, $username)
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $userInformation = $this->userInformationService->findByUserIdAndType($user->id, 6);
            if ($userInformation) {
                $userInformation->value = $username;
                $userInformation->save();
            } else {
                $user_information['email'] = $username;
                $this->userInformationService->create($user->id, $user_information);
            }
        } elseif (preg_match('/^\+?[0-9]{7,15}$/', $username)) {
            $userInformation = $this->userInformationService->findByUserIdAndType($user->id, 5);
            if ($userInformation) {
                $userInformation->value = $username;
                $userInformation->save();
            } else {
                $user_information['phone'] = $username;
                $this->userInformationService->create($user->id, $user_information);
            }
        } else {
            return false;
        }
        $user->username = $username;
        $user->save();
        return $user;
    }

    public function updateUserInformation($userId, $userInformation)
    {
        return $this->userInformationService->updateUserInformation($userId, $userInformation);
    }

    public function changePassword($user, $old_password, $password)
    {

        if (!\Hash::check($old_password, $user->password)) {
            return 0;
        }
        $this->userService->changePassword($user, $password);
        return 1;
    }

    public function forgotPassword($username, $password)
    {
        $this->userService->changeForgotPassword($username, $password);
        return 1;
    }


    public function logout()
    {
        Cache::forget('user_token_' . auth()->user()->id);
        auth()->user()->tokens()->delete();
        return ['status' => 200, 'message' => 'Successfully logouted'];
    }

}
