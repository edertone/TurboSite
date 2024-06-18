<?php

/**
 * TurboSite is a web site creation framework.
 *
 * Website : -> http://www.turboframework.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2018 Edertone Advanded Solutions. http://www.edertone.com
 */

namespace org\turbosite\src\main\php\services\users;

use Throwable;
use org\turbosite\src\main\php\managers\WebServiceManager;
use org\turbosite\src\main\php\managers\WebSiteManager;

/**
 * Service that performs a user login and returns the user instance and the generated token.
 * We must extend this class in our application and perform any necessary extra step depending on our needs.
 *
 * (Uses the turbodepot users framework)
 */
class LoginService extends WebServiceManager{


    protected function setup(){

        // The data POST parameter expects to receive a valid encoded credentials string.
        // The string must be encoded using UsersManager::encodeUserAndPassword()
        $this->enabledPostParams = ['data'];

        $this->authorizeMethod = function () { return true; };
    }


    public function run(){

        try {

            $logInResult = WebSiteManager::getInstance()->getDepotManager()->getUsersManager()
                ->loginFromEncodedCredentials($this->getPostParam('data'));

        } catch (Throwable $e) {

            return '';
        }

        return [
            'token' => $logInResult->token,
            'user' => $logInResult->user,
            'operations' => $logInResult->operations
        ];
    }
}
