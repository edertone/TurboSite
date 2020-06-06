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

use org\turbosite\src\main\php\managers\WebServiceManager;
use org\turbosite\src\main\php\managers\WebSiteManager;

/**
 * Service that performs a user login and returns the user instance and the generated token.
 * (Uses the turbodepot users framework)
 */
class Login extends WebServiceManager{


    protected function setup(){

        $this->enabledPostParams = ['data'];

        $this->authorizeMethod = function () { return true; };
    }


    public function run(){

        $logInResult = WebSiteManager::getInstance()->getDepotManager()->getUsersManager()
            ->loginFromEncodedCredentials($this->getPostParam('data'));

        if($logInResult === []){

            return '';
        }

        $result = [];
        $result['token'] = $logInResult[0];
        $result['user'] = $logInResult[1];

        return $result;
    }
}

?>