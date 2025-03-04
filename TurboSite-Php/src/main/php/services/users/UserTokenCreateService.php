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
 * Service that allows us to create extra tokens for a user.
 * We must extend this class in our application and perform any necessary extra step depending on our needs.
 *
 * (Uses the turbodepot users framework)
 */
class UserTokenCreateService extends WebServiceManager{


    protected function setup(){

        // POST parameters expect an object with the options setup ({useCount: N, lifeTime: N, etc...}) for the create token method and
        // a valid token representing the user. Check the docs for createToken method to get detailed info about the token creation options
        $this->enabledPostParams = ['options', 'token'];

        // By default, service is open.
        // Override this authorizeMethod on the extended service class to control authorization
        $this->authorizeMethod = function () { return true; };
    }


    public function run(){

        try {

            $usersManager = WebSiteManager::getInstance()->getDepotManager()->getUsersManager();

            $userName = $usersManager->findUserByToken($this->getPostParam('token'))->userName;

            return $usersManager->createToken($userName, json_decode($this->getPostParam('options'), true));

        } catch (Throwable $e) {

            return $this->generateError(400, 'Could not create token');
        }
    }
}
