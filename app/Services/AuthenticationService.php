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
        $this->otpCodeService->create($username, random_int(100000, 999999), 'registration');
        return true;
    }


    public function logout()
    {
        auth()->user()->AuthAcesToken()->delete();

        return ['status' => 200, 'message' => 'Successfully logouted'];
    }

}
