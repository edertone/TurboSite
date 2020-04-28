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
 * A service with 3 URL params being the 2 last ones non mandatory
 */
class ServiceWithUrlParams5LastNotMandatory extends WebServiceManager{


    protected function setup(){

        $this->enabledUrlParams[] = [];
        $this->enabledUrlParams[] = [];
        $this->enabledUrlParams[] = [];
        $this->enabledUrlParams[] = [WebServiceManager::NOT_TYPED, WebServiceManager::NOT_RESTRICTED, 'default3'];
        $this->enabledUrlParams[] = [WebServiceManager::BOOL, WebServiceManager::NOT_RESTRICTED, true];

        $this->authorizeMethod = function () { return true; };
    }


    public function run(){

        return [
            "0" => $this->getUrlParam(0),
            "1" => $this->getUrlParam(1),
            "2" => $this->getUrlParam(2),
            "3" => $this->getUrlParam(3),
            "4" => $this->getUrlParam(4)
        ];
    }
}

?>