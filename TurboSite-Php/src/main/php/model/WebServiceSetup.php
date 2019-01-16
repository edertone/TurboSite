<?php

/**
 * TurboSite is a web site creation framework.
 *
 * Website : -> http://www.turboframework.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2018 Edertone Advanded Solutions. http://www.edertone.com
 */

namespace org\turbosite\src\main\php\model;


/**
 * Contains the configuration parameters for the project services
 */
class WebServiceSetup{


    /**
     * Defines if the web service accepts POST parameters. Note that when using POST, all the service parameters must be sent
     * inside a single JSON object that is passed via the "params" POST variable.
     *
     * The variable "params" is the only POST value that will be accepted by the service. All the data to the service must be passed
     * via this variable. Any other POST variable that is sent to the service will make it fail.
     */
    public $enablePostParams = false;


    /**
     * Defines how many GET parameters are accepted by this service. Anyones beyond this limit will make the service fail.
     * If a service has a missing value for any of the enabled parameters and there's no default value defined, the service fail.
     */
    public $enabledGetParams = 0;
}

?>