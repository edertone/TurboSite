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
use org\turbocommons\src\main\php\managers\SerializationManager;
use org\turbosite\src\main\php\managers\WebServiceManager;
use org\turbosite\src\main\php\managers\WebSiteManager;
use org\turbocommons\src\main\php\utils\StringUtils;


/**
 * Service that creates a new user and stores it into data base
 * We must extend this class in our application and perform any necessary extra step depending on our needs.
 *
 * (Uses the turbodepot users framework)
 */
class UserCreateService extends WebServiceManager{


    /**
     * Contains a UserObject instance filled with the user information received
     * at the POST userObject parameter
     *
     * @var UserObject $userObject
     */
    public $userObject;


    /**
     * Contains the password that will be assigned to this user
     * If specified, this value will override any psw received via POST
     *
     * @var string
     */
    public $psw = '';


    /**
     * Array wit a list of user mails to create
     * IMPORTANT:THIS OBJECT MUST BE FILLED BY THE SUPER CLASS IN ORDER FOR IT TO BE SAVED
     *
     * @var UserObject $userObject
     */
    public $userMails = [];


    /**
     * Contains an instance correctly filled with user custom fields
     * IMPORTANT:THIS OBJECT MUST BE FILLED BY THE SUPER CLASS IN ORDER FOR IT TO BE SAVED
     */
    public $userCustomFieldsObject = null;


    protected function setup(){

        // POST parameters expect a valid token and userObject to create
        $this->enabledPostParams[] = ['token'];
        $this->enabledPostParams[] = ['userObject'];
        $this->enabledPostParams[] = ['userCustomFieldsObject', WebServiceManager::NOT_TYPED, WebServiceManager::NOT_REQUIRED];
        $this->enabledPostParams[] = ['mails', WebServiceManager::NOT_TYPED, WebServiceManager::NOT_REQUIRED];
        $this->enabledPostParams[] = ['psw', WebServiceManager::NOT_TYPED, WebServiceManager::NOT_REQUIRED];

        // By default, service is open.
        // Override this authorizeMethod on the extended service class to control authorization
        $this->authorizeMethod = function () { return true; };
    }


    public function __construct(array $urlParameters = null, array $postParameters = null){

        parent::__construct($urlParameters, $postParameters);

        $serializationManager = new SerializationManager();

        // Obtain the user object instance from the received data
        $this->userObject = $serializationManager->jsonToClass($this->getPostParam('userObject'), new UserObject());
    }


    /**
     * Perform the saving of the user object instance that has been received via POST.
     * User must not exist to be correctly saved.
     * If a psw value is provided via POST, the password will also be assigned, but it is not mandatory
     *
     * @return True on success or an error structure if the saving fails.
     */
    public function run(){

        $usersManager = WebSiteManager::getInstance()->getDepotManager()->getUsersManager();

        // User must not exist on db
        if($usersManager->isUser($this->userObject->userName)){

            return $this->generateError(400, 'Could not create user: Already exists', '-', '-');
        }

        // If a POST password is provided, make sure it is valid
        if($this->getPostParam('psw') !== null && StringUtils::isEmpty($this->getPostParam('psw'))){

            return $this->generateError(400, 'Invalid password', '-', '-');
        }

        try {

            $usersManager->transactionBegin();

            // Save the user object
            $usersManager->saveUser($this->userObject);

            // Save user custom fields if specified
            if($this->userCustomFieldsObject !== null){

                $usersManager->saveUserCustomFields($this->userObject->userName, $this->userCustomFieldsObject);
            }

            // Save user mails if provided
            foreach ($this->userMails as $mail) {

                $usersManager->saveUserMail($this->userObject->userName, $mail);
            }

            // Set user password if specified
            // We prioritize the internal psw variable over the POST value
            if($this->psw !== ''){

                $usersManager->setUserPassword($this->userObject->userName, $this->psw);

            }elseif($this->getPostParam('psw') !== null){

                $usersManager->setUserPassword($this->userObject->userName, $this->getPostParam('psw'));
            }

            $usersManager->transactionCommit();

            return true;

        } catch (Throwable $e) {

            $usersManager->transactionRollback();

            return $this->generateError(500, 'Unknown error', $e->getMessage());
        }
    }
}
