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
 * A service with 3 URL params being the second one non mandatory. This must make the service creation fail
 */
class ServiceWithUrlParams3IncorrectMandatory extends WebService{


    protected function setup(){

        $this->enabledUrlParams[] = [];
        $this->enabledUrlParams[] = [WebService::NOT_TYPED, WebService::NOT_RESTRICTED, 'default'];
        $this->enabledUrlParams[] = [];
    }


    public function run(){

        return '';
    }
}

?>