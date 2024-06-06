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
 * Service that is used to perform the logout of a user by invalidating his token.
 * (Uses the turbodepot users framework)
 */
class LogoutService extends WebServiceManager{


    protected function setup(){

        $this->enabledPostParams = ['token'];

        $this->authorizeMethod = function () { return true; };
    }


    public function run(){

        return WebSiteManager::getInstance()->getDepotManager()->getUsersManager()
            ->logout($this->getPostParam('token'));
    }
}

?>