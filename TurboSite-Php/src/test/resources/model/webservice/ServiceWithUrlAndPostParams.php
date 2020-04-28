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
 * A service with mandatory URL and post params
 */
class ServiceWithUrlAndPostParams extends WebServiceManager{


    protected function setup(){

        $this->enabledUrlParams[] = [];
        $this->enabledUrlParams[] = [];

        $this->enabledPostParams[] = ['a'];
        $this->enabledPostParams[] = ['b'];

        $this->authorizeMethod = function () { return true; };
    }


    public function run(){

        return [
            "0" => $this->getUrlParam(0),
            "1" => $this->getUrlParam(1),
            "a" => $this->getPostParam('a'),
            "b" => $this->getPostParam('b')
        ];
    }

}

?>