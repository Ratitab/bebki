<?php

namespace App\Services;

use App\Traits\Resp;

class AuthenticationService
{
    use Resp;

    public function __construct(private readonly UserService    $userService, private readonly UserInformationService $userInformationService,
                                private readonly OtpCodeService $otpCodeService)
    {
    }

    public function login($username, $password)
    {
        $user = $this->userInformationService->findUserId($username);
        if ($user && \Hash::check($password, $user->password)) {
            return auth()->loginUsingId($user->user_id)->createToken('Bearer')->accessToken;
        }
        return false;
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

    public function otpRegistration($username)
    {
        return $this->otpCodeService->create($username, random_int(100000, 999999), 'registration');
        return true;
    }

    public function otpUpdateEmailOrPhone($username)
    {
        return $this->otpCodeService->create($username, random_int(100000, 999999), 'update_email_or_phone');
        return true;
    }

    public function updateEmailOrPhone($user, $username)
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $userInformation = $this->userInformationService->findByUserIdAndType($user->id, 6);
            if ($userInformation) {
                $userInformation->value = $username;
                $userInformation->save();
            }else{
                $user_information['email'] = $username;
                $this->userInformationService->create($user->id, $user_information);
            }
        } elseif (preg_match('/^\+?[0-9]{7,15}$/', $username)) {
            $userInformation = $this->userInformationService->findByUserIdAndType($user->id, 5);
            if ($userInformation) {
                $userInformation->value = $username;
                $userInformation->save();
            }else{
                $user_information['phone'] = $username;
                $this->userInformationService->create($user->id, $user_information);
            }
        }else{
            return false;
        }
        $user->username = $username;
        $user->save();
        return $user;
    }

    public function logout()
    {
        auth()->user()->AuthAcesToken()->delete();

        return ['status' => 200, 'message' => 'Successfully logouted'];
    }

}
