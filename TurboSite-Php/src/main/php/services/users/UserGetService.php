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
 * Service that obtains the user instance for a specific username
 * We must extend this class in our application and perform any necessary extra step depending on our needs.
 *
 * (Uses the turbodepot users framework)
 */
class UserGetService extends WebServiceManager{


    protected function setup(){

        // POST parameters expect a valid token and username to retrieve
        $this->enabledPostParams = ['token', 'username'];

        // By default, service is open.
        // Override this authorizeMethod on the extended service class to control authorization
        $this->authorizeMethod = function () { return true; };
    }


    public function run(){

        try {

            return $this->logedUser = WebSiteManager::getInstance()->getDepotManager()->getUsersManager()
                ->findUserByToken($this->getPostParam('token'));

        } catch (Throwable $e) {

            return $this->generateError(400, 'Could not obtain user');
        }
    }
}
