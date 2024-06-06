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
     * Creates a WebServiceError instance with the specified data.
     * This instance is normally used as the result of webservices that need to show an error to the user.
     *
     * @param int $code The http response code that will defined for this error instance. Common values are:<br>
     *        - 400 (Bad Request): is used to tell the client that there was an incorrect value on the request. Normally is a controled problem and client side related.<br>
     *        - 500 (Internal Server Error): is the generic REST API error response which means that something went wrong at the server side. Normally an uncontrolled exception
     * @param string $title The title for the error that we want to create
     * @param string $message The description for the error message that we want to create
     * @param string $trace The code trace for the error message that we want to create
     *
     * @return WebServiceError A newly created error instance, filled with the specified data, so we can return it on the webservice run() method
     */
    public static function createInstance(int $code, string $title, string $message = '', string $trace = ''){

        $error = new WebServiceError();

        $error->code = $code;
        $error->title = $title;
        $error->message = $message;
        $error->trace = $trace;

        return $error;
    }


    /**
     * Contains the http code for the webservice error (see WebServiceError::createInstance method docs for more info on error codes)
     *
     * @see WebServiceError::createInstance
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