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
use org\turbodepot\src\main\php\model\UserObject;
use org\turbosite\src\main\php\managers\WebServiceManager;
use org\turbosite\src\main\php\managers\WebSiteManager;

/**
 * Service that deletes the specified user from data base.
 * We must extend this class in our application and perform any necessary extra step depending on our needs.
 *
 * (Uses the turbodepot users framework)
 */
class UserDeleteService extends WebServiceManager{


    /**
     * Stores the user instance as obtained from the provided token
     * @var UserObject $currentUser
     */
    public $logedUser = null;


    protected function setup(){

        // POST parameters expect a valid token and username to delete
        $this->enabledPostParams = ['token', 'username'];

        // By default, only the user itself is allowed to request the deletion.
        // Override this authorizeMethod on the extended service class if a different behaviour is wanted
        $this->authorizeMethod = function () {

            $this->logedUser = WebSiteManager::getInstance()->getDepotManager()->getUsersManager()
                ->findUserByToken($this->getPostParam('token'));

             return $this->logedUser->userName === $this->getPostParam('username');
        };
    }


    public function run(){

        try {

            WebSiteManager::getInstance()->getDepotManager()->getUsersManager()
                ->deleteUser($this->getPostParam('username'));

            return 'User '.$this->getPostParam('username').' deleted';

        } catch (Throwable $e) {

            return $this->generateError(400, 'Could not delete user');
        }
    }
}
