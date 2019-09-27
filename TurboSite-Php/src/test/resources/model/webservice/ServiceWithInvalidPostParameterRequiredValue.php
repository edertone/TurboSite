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
 * A service that defines a post parameter with an invalid required value, so an exception must be thrown when constructed
 */
class ServiceWithInvalidPostParameterRequiredValue extends WebService{


    protected function setup(){

        $this->enabledPostParams[] = ['a', WebService::NOT_TYPED, WebService::ARRAY];
    }


    public function run(){

        return '';
    }

}

?>