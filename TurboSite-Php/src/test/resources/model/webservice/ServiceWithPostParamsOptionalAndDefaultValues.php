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
 * A service with some required POST params, some optional POST params and some optional post params with default values
 */
class ServiceWithPostParamsOptionalAndDefaultValues extends WebService{


    protected function setup(){

        $this->enabledPostParams[] = ['a'];
        $this->enabledPostParams[] = ['b', WebService::NOT_TYPED, WebService::NOT_REQUIRED];
        $this->enabledPostParams[] = ['c', WebService::NOT_TYPED, WebService::NOT_REQUIRED, WebService::NOT_RESTRICTED, 'default'];
    }


    public function run(){

        return [
            "a" => $this->getPost('a'),
            "b" => $this->getPost('b'),
            "c" => $this->getPost('c')
        ];
    }

}

?>