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
 * A service with all POST parameters required
 */
class ServiceWithPostParams extends WebServiceManager{


    protected function setup(){

        $this->enabledPostParams[] = ['a'];
        $this->enabledPostParams[] = 'b';
    }


    public function run(){

        return [
            "a" => $this->getPostParam('a'),
            "b" => $this->getPostParam('b')
        ];
    }

}

?>