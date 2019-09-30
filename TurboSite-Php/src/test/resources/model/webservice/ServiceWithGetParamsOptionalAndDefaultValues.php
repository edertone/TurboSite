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
 * A service with some required GET params and some optional GET params with default values
 */
class ServiceWithGetParamsOptionalAndDefaultValues extends WebService{


    protected function setup(){

        $this->enabledGetParams[] = [];
        $this->enabledGetParams[] = [WebService::NOT_TYPED];
        $this->enabledGetParams[] = [WebService::NOT_TYPED, WebService::NOT_RESTRICTED, 'default'];
    }


    public function run(){

        return [
            "0" => $this->getParam(0),
            "1" => $this->getParam(1),
            "2" => $this->getParam(2)
        ];
    }

}

?>