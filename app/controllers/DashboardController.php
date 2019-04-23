<?php

namespace App\Controllers;

use App\API\YandexDirectAPI;
use App\Models\Resource;
use Respect\Validation\Validator as v;

class DashboardController extends Controller
{
    // App id = e38428b0273d4f06a2ea58c0b672f600;
    // Get premission = https://oauth.yandex.ru/authorize?response_type=token&client_id=e38428b0273d4f06a2ea58c0b672f600

    // Premission accepted
    // http://localhost:8000/public/dashboard/#access_token=AQAAAAAUwmfoAAV9ascPoH4e7kA4nWKOtxGCEyY&token_type=bearer&expires_in=31536000

    public function getShowAllAccounts($request, $response)
    {
        $accounts = Resource::where('user_id', $_SESSION['user'])->get();

        if ($accounts->count() === 0) {
            $data['accounts'] = [];
            return $this->view->render($response, 'dashboard/dashboard.twig', $data);
        };

        // last update
        $date = $accounts[0]->updated_at;
        $date->timezone('Europe/Moscow');
        //$date->locale('ru');
        $updated_at = $date->diffForHumans();

        foreach ($accounts as $account) {

            $data['accounts'][$account->name] = [
                'id' => $account->id,
                'yandex_direct_balance' => $account->yandex_direct_balance,
                'yandex_market_balance' => $account->yandex_market_balance,
                'google_ads_balance' => $account->google_ads_balance,
                'updated_at' => $updated_at
            ];
        }
        
        // проверяем какие сервисы заполнены и обращаемся к их апишке

        if (!empty($account->yandex_direct_balance)) {

        }

        // foreach ($accounts as $name => $account) {
        //     $obj = new YandexDirectAPI($account);
        //     $resp = $obj->getAccountData();
        //     $data['accountsData'][$name] = $resp;
        // }

        return $this->view->render($response, 'dashboard/dashboard.twig', $data);
    }

    // ОБНОВЛЕНИЕ РЕЗУЛЬТАТОВ

    public function postShowAllAccounts($request, $response)
    {
        // получаем ресурсы пользователя
        $resources = Resource::where('user_id', $_SESSION['user'])->get();

        $data = ['accountsData' => []];

        // проходим по каждому аккаунту, вызываем метод получения и записи данных для каждого заполненного
        foreach ($resources as $resource) {
            if ($resource->yandex_direct_token) {
                $obj = new YandexDirectAPI($resource->yandex_direct_login, $resource->yandex_direct_token);
                $resp = $obj->getAccountData();
                $amount = $resp->getAccounts()[0]->getAmount();
                $resource->yandex_direct_balance = $amount;
                $resource->updated_at = time();
                $resource->save();
            }
        }
        $this->flash->addMessage('success', 'Успешно обновлено.');
        return 'success';
    }

    // ДОБАВЛЕНИЕ АККАУНТА

    public function getAddAccount($request, $response)
    {
        if ($request->isXhr() ) {
            $access_token = $request->getParam('access_token');
            $expires_in = date('Y-m-d h:m:s', time() + $request->getParam('expires_in'));

            $resource = Resource::find($_SESSION['resource_id']);

            $resource->yandex_direct_token = $access_token;
            $resource->yandex_direct_token_expire = $expires_in;
            $resource->yandex_direct_login = $_SESSION['account_login'];
            $resource->yandex_direct_balance = '0';
            $resource->save();
            
            $this->flash->addMessage('success', 'Аккаунт успешно подключен.');

            unset($_SESSION['resource_id']);
            unset($_SESSION['account_login']);

            return 'success';
        }

        $account = $request->getQueryParam('account');
        $resource_id = $request->getQueryParam('id');

        // $data = ['account' => [
        //     'account' => $account,
        //     'id' => $resource_id
        // ]];

        return $this->view->render($response, 'dashboard/account/account.add.twig');
    }

    public function postAddAccount($request, $response)
    {   
        // проверка на пустоту
        $validation = $this->validator->validate($request, [
            'login' => v::notEmpty(),
        ]);

        if ($validation->failed()) {
            //return $response->withRedirect($this->router->pathFor('dashboard.add.account'));
            sleep(1);
            return 'validation failed';
        }

        $_SESSION['resource_id'] = $request->getQueryParam('id');
        $_SESSION['account_login'] = $request->getParam('login');

        sleep(1);
        return 'ok';

        // какой аккаунт добавляем?
            //

        // есть ли в базе аккаунт этой рекламной системы?
            //
        
        // редирект на страницу получения токена
            //

        // далее обработка в getAddAccount (берем токен js-ом, пуляем ajax-ом на сервер)



        // $login = $request->getParam('login');
        // $account = $request->getQueryParam('account');
        // $id = $request->getQueryParam('id');
        // if ($account === "yandex_direct") {
        //     return $response->withRedirect("https://oauth.yandex.ru/authorize?response_type=token&client_id=e38428b0273d4f06a2ea58c0b672f600&redirect_uri=http://localhost:8000/public/dashboard/add/&login_hint={$login}");
        // }
    }

    // ДОБАВЛЕНИЕ РЕСУРСА

    public function getAddResource($request, $response)
    {
        return $this->view->render($response, 'dashboard/resource/resource.add.twig');
    }

    public function postAddResource($request, $response)
    {
        $validation = $this->validator->validate($request, [
            'name' => v::notEmpty()
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('dashboard.add.resource'));
        }

        $name = $request->getParsedBodyParam('name');
        
        $checkName = Resource::where('name', $name)->first();
        
        if ($checkName === NULL) {
            $resource = Resource::create([
                'name' => $name,
                'user_id' => $_SESSION['user'],
                'created_at' => time(),
            ]);
            $this->flash->addMessage('success', 'Ресурс успешно добавлен.');
            return $response->withRedirect($this->router->pathFor('dashboard'));
        } else {
            $this->flash->addMessage('error', 'Ресурс с таким именем уже существует.');
            return $response->withRedirect($this->router->pathFor('dashboard.add.resource'));
        }
    }
}