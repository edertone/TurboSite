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
 * A service with 3 GET params being the 2 last ones non mandatory
 */
class ServiceWithGetParams5LastNotMandatory extends WebService{


    protected function setup(){

        $this->enabledGetParams[] = [];
        $this->enabledGetParams[] = [];
        $this->enabledGetParams[] = [];
        $this->enabledGetParams[] = [WebService::NOT_TYPED, WebService::NOT_RESTRICTED, 'default3'];
        $this->enabledGetParams[] = [WebService::BOOL, WebService::NOT_RESTRICTED, 'true'];
    }


    public function run(){

        return [
            "0" => $this->getParam(0),
            "1" => $this->getParam(1),
            "2" => $this->getParam(2),
            "3" => $this->getParam(3),
            "4" => $this->getParam(4)
        ];
    }
}

?>