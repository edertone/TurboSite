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

use UnexpectedValueException;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbosite\src\main\php\managers\WebSiteManager;


/**
 * Defines the base class for all the project web services.
 * Any web service that is accessible via API calls must extend this class
 * and override the setup() and run() methods.
 */
class WebService{


    /**
     * Defines which content type will be generated by the response of this webservice. Common possible values are:
     *
     * 'application/json' - When the webservice returns json data
     * 'image/jpg' - When the webservice returns a jpg image
     * 'text/plain' - When the webservice returns simple text data
     *
     * TODO - add more
     *
     * @var string
     */
    public $contentType = 'application/json';


    /**
     * Defines if the web service accepts POST parameters. A list of strings must be provided where each one of the elements
     * is the name of a POST parameter that will be accepted by the service.
     *
     * Any POST parameter that is passed to the service which is not enabled on this list will make the service fail.
     */
    public $enabledPostParams = [];


    /**
     * This flag defines if POST data must be passed to the service when POST data is enabled on the service.
     * If set to true, all the POST parameters must be passed to the service (they can be empty but must be passed via POST).
     */
    public $isPostDataMandatory = true;


    /**
     * Stores the actual values of the POST parameters that have been passed to this service via POST or via service constructor
     */
    private $_receivedPostParameters = [];


   /**
     * Defines how many GET parameters are accepted by this service. Anyones beyond this limit will make the service fail.
     *
     * Get parameters on http services can only be passed in the form .../api/../../service-name/param1/param2/param3/... The standard way
     * to encode GET parameter in urls (?param1=v1&param2=v2...) is not accepted and will be ignored.
     */
    public $enabledGetParams = 0;


     /**
     * This flag defines if all GET parameters must be passed to the service when GET parameters are enabled on the service.
     * If set to true, all the GET parameter values must be passed to the service.
     */
    public $isGetDataMandatory = true;


    /**
     * Stores the actual values of the GET parameters that have been passed to this service via the URL or via service constructor,
     * sorted in the same order as specified in the url or constructor array
     */
    private $_receivedGetParameters = [];


    /**
     * Stores the number of GET parameters that have been passed to this service via the URL
     */
    private $_receivedGetParametersCount = 0;


    /**
     * Class constructor
     *
     * @param array $getParameters If we create this service via code we can pass the GET data here and it will be loaded
     *        by the service as if it was passed via HTTP GET. It must be an array containing each one of the parameter values that
     *        would be passed via the url, sorted in the same way as they would in the url. First array elemtn will be the param 1,
     *        second will be param 2, and so. Same rules as when calling the service via url apply.
     * @param array $postParameters If we create this service via code we can pass the POST data here and it will be loaded
     *        by the service as if it was passed via HTTP POST. It must be an associative array that contains the info we want to pass
     *        to the service where POST parameters are defined at the array keys.  Same rules as when calling the service via url apply.
     */
    public function __construct(array $getParameters = null, array $postParameters = null){

        $this->setup();

        $ws = WebSiteManager::getInstance();

        // Process the service GET parameters
        if($getParameters !== null){

            $this->_receivedGetParameters = $getParameters;

        }else if(strpos($ws->getFullUrl(), '/api/') !== false){

            // Parse the service GET parameters if any exist and store them to _receivedGetParameters
            $URI = explode('/api/', $ws->getFullUrl())[1];
            $URIElements = explode('/', $URI);

            $serviceNameFound = false;
            $serviceName = StringUtils::getPathElement(get_class($this));

            foreach ($URIElements as $uriElement) {

                if($serviceNameFound){

                    $this->_receivedGetParameters[] = $uriElement;

                }else if(StringUtils::formatCase($uriElement, StringUtils::FORMAT_UPPER_CAMEL_CASE) === $serviceName){

                    $serviceNameFound = true;
                }
            }
        }

        $this->_receivedGetParametersCount = count($this->_receivedGetParameters);

        // Check get parameters are valid
        if(($this->isGetDataMandatory && $this->_receivedGetParametersCount !== $this->enabledGetParams) ||
            $this->_receivedGetParametersCount > $this->enabledGetParams){

            throw new UnexpectedValueException('Invalid number of GET parameters passed to service. Received '.
                $this->_receivedGetParametersCount.' but expected '.$this->enabledGetParams);
        }

        // All GET parameters must be strings
        foreach ($this->_receivedGetParameters as $value) {

            if(!is_string($value)){

                throw new UnexpectedValueException('All GET parameters must be strings');
            }
        }

        // Process the service POST parameters
        if($postParameters !== null){

            foreach ($postParameters as $key => $value) {

                $this->_receivedPostParameters[$key] = $value;
            }

        }else{

            foreach ($_POST as $key => $value) {

                $this->_receivedPostParameters[$key] = $value;
            }
        }

        // Check post parameters are valid
        $postKeys = array_keys($this->_receivedPostParameters);
        $receivedPostParamsCount = count($postKeys);
        $enabledPostParamsCount = count($this->enabledPostParams);

        if($enabledPostParamsCount > 0 && $this->isPostDataMandatory && $receivedPostParamsCount === 0){

            throw new UnexpectedValueException('This service expects POST data');
        }

        if($enabledPostParamsCount === 0 && $receivedPostParamsCount > 0){

            throw new UnexpectedValueException('Received POST variables but POST not enabled on service');
        }

        if(($receivedPostParamsCount > 0 && $receivedPostParamsCount !== $enabledPostParamsCount) ||
            ($this->isPostDataMandatory && array_diff($postKeys, $this->enabledPostParams) !== [])){

            throw new UnexpectedValueException('Unexpected POST variables received.');
        }

        // All POST parameters must be strings
        foreach ($postKeys as $key) {

            if(!is_string($this->_receivedPostParameters[$key])){

                throw new UnexpectedValueException('All POST parameters must be strings');
            }
        }
    }


    /**
     * Get the value for a service url parameter, given its parameter index number.
     * If the parameter index is valid, but no value has been passed into the url, it will return an empty string.
     * URL parameters are the custom values that can be passed via url to the framework services.
     * They are encoded this way: http://.../api/site/service-category/service-name/parameter1/parameter2/parameter3/...
     *
     * @param int $index The numeric index for the requested parameter. Invalid index value will throw an exception
     * @param bool $removeHtmlTags To prevent HTML injection attacks, all html and php tags are removed from the parameter values.
     *        If we specifically need this tags to be preserved, we can set this flag to false. Normally not necessary
     *
     * @return string The requested parameter value
     */
    public function getParam(int $index = 0){

        if($index < 0){

            throw new UnexpectedValueException('Invalid GET parameter index: '.$index);
        }

        if($index >= $this->enabledGetParams){

            throw new UnexpectedValueException('Disabled service parameter index '.$index.' requested');
        }

        if(!$this->isGetDataMandatory && !isset($this->_receivedGetParameters[$index])){

            return '';
        }

        return $this->_receivedGetParameters[$index];
    }


    /**
     * Get the value as a raw string for the specified POST parameter that has been passed to this service.
     *
     * @param string $paramName The name for the POST parameter we want to read
     *
     * @return string The value of the received POST variable as a raw string or an empty string if the
     *         variable has not been passed to the service.
     */
    public function getPost(string $paramName){

        if(array_search($paramName, $this->enabledPostParams) === false){

            throw new UnexpectedValueException('Invalid POST parameter name: '.$paramName);
        }

        if(isset($this->_receivedPostParameters[$paramName])){

            return $this->_receivedPostParameters[$paramName];
        }

        return '';
    }


    /**
     * Get the value casted as an integer for the specified POST parameter that has been passed to this service.
     *
     * @param string $paramName The name for the POST parameter we want to read
     *
     * @return number The value of the received POST variable converted to its integer value or 0 if no
     *         variable has been passed to the service.
     */
    public function getPostAsInt(string $paramName){

        $result = $this->getPost($paramName);

        return $result === '' ? 0 : (int) $result;
    }


    /**
     * Get the value casted as a float for the specified POST parameter that has been passed to this service.
     *
     * @param string $paramName The name for the POST parameter we want to read
     *
     * @return number The value of the received POST variable converted to its float value or 0 if no
     *         variable has been passed to the service.
     */
    public function getPostAsFloat(string $paramName){

        $result = $this->getPost($paramName);

        return $result === '' ? 0 : (float) $result;
    }


    /**
     * Get the value converted as an associative array for the specified POST parameter that has been passed to this service.
     *
     * @param string $paramName The name for the POST parameter we want to read
     *
     * @return array The value of the received POST variable converted to an associative array or [] if no variable
     *         has been passed to the service.
     */
    public function getPostAsArray(string $paramName){

        $postData = $this->getPost($paramName);

        if($postData === ''){

            return [];
        }

        $result = json_decode($postData);

        if(!is_array($result)){

            throw new UnexpectedValueException('Could not convert '.$postData.' to an associative array');
        }

        return $result;
    }


    /**
     * This method is always called before any other thing at the web service constructor.

     * Override it to define the service setup values like enabling GET or POST parameters and
     * any other required customization.
     *
     * @return void
     */
    protected function setup(){

        // Override this method to modify the setup of your service
    }


    /**
     * This method is executed to perform the service operations and return a result.
     *
     * Override this method with the actual service logic
     *
     * @return mixed The result of the service as any of the PHP basic types (bool, stint, string, array, stdclass)
     *         or a WebServiceError instance
     */
    public function run(){

        // Override this method to add the actual service execution code
    }


    /**
     * Creates a WebServiceError instance with the specified data.
     * This instance is normally used as the result for webservices that need to show an error to the user.
     *
     * @param int $code The http response code that will defined for this error instance. Common values are:
     *        - 400 (Bad Request): is used to tell the client that there was an incorrect value on the request. Problem is client side related.
     *        - 500 (Internal Server Error): is the generic REST API error response which means that something went wrong at the server side. Normally an exception
     * @param string $title The title for the error that we want to create
     * @param string $message The description for the error message that we want to create
     *
     * @return WebServiceError A newly created error instance, filled with the specified data, so we can return it on the webservice run() method
     */
    public function generateError(int $code, string $title, string $message = '', string $trace = ''){

        $error = new WebServiceError();

        $error->code = $code;
        $error->title = $title;
        $error->message = $message;
        $error->trace = $trace;

        return $error;
    }
}

?>