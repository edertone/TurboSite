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
 * A service that defines a STRING typed URL parameter
 */
class ServiceWithUrlParameterStringTyped extends WebServiceManager{


    protected function setup(){

        $this->enabledUrlParams[] = [WebServiceManager::STRING];

        $this->authorizeMethod = function () { return true; };
    }


    public function run(){

        return $this->getUrlParam(0);
    }

}

?>