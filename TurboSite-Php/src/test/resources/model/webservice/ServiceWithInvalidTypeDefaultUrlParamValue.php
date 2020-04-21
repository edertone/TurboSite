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
 * A service with a default value that does not match its defined type
 */
class ServiceWithInvalidTypeDefaultUrlParamValue extends WebServiceManager{


    protected function setup(){

        $this->enabledUrlParams[] = [];
        $this->enabledUrlParams[] = [WebServiceManager::NOT_TYPED];
        $this->enabledUrlParams[] = [WebServiceManager::INT, [1,3,5,6], 'string'];
    }


    public function run(){

        return [
            "0" => $this->getUrlParam(0),
            "1" => $this->getUrlParam(1),
            "2" => $this->getUrlParam(2)
        ];
    }

}

?>