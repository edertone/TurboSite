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
 * Defines an the structure for webservice error data.
 */
class WebServiceError{


    /**
     * Contains the http code for the webservice error (see WebService::generateError method docs for more info)
     *
     * @see WebService::generateError
     *
     * @var int
     */
    public $code = 0;


    /**
     * Contains the title for the webservice error
     *
     * @var string
     */
    public $title = '';


    /**
     * Contains the message for the webservice error
     *
     * @var string
     */
    public $message = '';


    /**
     * Contains the error trace (if any)
     *
     * @var string
     */
    public $trace = '';

}

?>