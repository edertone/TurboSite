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

use org\turbosite\src\main\php\model\WebService;


/**
 * A service that defines 4 URL parameters directly with an INT number
 */
class ServiceWithUrl4ParametersDeclaredViaInt extends WebService{


    protected function setup(){

        $this->enabledUrlParams = 4;
    }


    public function run(){

        return [$this->getUrlParam(0),
            $this->getUrlParam(1),
            $this->getUrlParam(2),
            $this->getUrlParam(3)
        ];
    }

}

?>