<?php

/**
 * TurboSite is a web site creation framework.
 *
 * Website : -> http://www.turboframework.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2018 Edertone Advanded Solutions. http://www.edertone.com
 */

namespace org\turbosite\src\test\resources\model\webservice;

use org\turbosite\src\main\php\managers\WebServiceManager;


/**
 * A service with some required POST params, some optional POST params and some optional post params with default values
 */
class ServiceWithPostParamsOptionalAndDefaultValues extends WebServiceManager{


    protected function setup(){

        $this->enabledPostParams[] = ['a'];
        $this->enabledPostParams[] = ['b', WebServiceManager::NOT_TYPED, WebServiceManager::NOT_REQUIRED];
        $this->enabledPostParams[] = ['c', WebServiceManager::NOT_TYPED, WebServiceManager::NOT_REQUIRED, WebServiceManager::NOT_RESTRICTED, 'default'];
    }


    public function run(){

        return [
            "a" => $this->getPostParam('a'),
            "b" => $this->getPostParam('b'),
            "c" => $this->getPostParam('c')
        ];
    }

}

?>