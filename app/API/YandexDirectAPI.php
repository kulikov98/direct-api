<?php

namespace App\API;

use Biplane\YandexDirect\User;
use Biplane\YandexDirect\Api\V4\Contract\AccountManagementRequest;
use Biplane\YandexDirect\Api\V4\Contract\AccountManagementResponse;

class YandexDirectAPI
{
    protected $login;
    protected $token;

    public function __construct($login, $token)
    {
        $this->login = $login;
        $this->token = $token;
    }

    public function getAccountData()
    {
        $user = new User([
            'access_token' => $this->token,
            'login' => $this->login,
            'locale' => User::LOCALE_RU,
        ]);

        $accountManagementRequest = AccountManagementRequest::create()
            ->setAction('Get');

        $apiResponse = $user->getApiService()->accountManagement($accountManagementRequest);

        return $apiResponse;
    }
}