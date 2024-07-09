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
 * Validate an email account for the provided user
 * (Uses the turbodepot users framework)
 */
class UserMailVerifyService extends WebServiceManager{


    protected function setup(){

        // token is enabled as it is usually sent by api calls, but will be ignored
        $this->enabledPostParams[] = ['token'];

        // userName
        $this->enabledPostParams[] = ['userName'];

        // The user mail that we want to validate
        $this->enabledPostParams[] = ['mail'];

        // hashcode required to validate the provided email
        $this->enabledPostParams[] = ['hash'];

        // Service is open.
        // It can be overriden at the super class, but it will not be necessary most of the time
        $this->authorizeMethod = function () { return true; };
    }


    public function run(){

        $usersManager = WebSiteManager::getInstance()->getDepotManager()->getUsersManager();

        try {

            return $usersManager->verifyUserMail($this->getPostParam('userName'), $this->getPostParam('mail'), $this->getPostParam('hash'));

        } catch (Throwable $e) {

            return -1;
        }
    }
}
