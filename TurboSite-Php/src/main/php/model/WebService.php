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
     * Specifies that a parameter has no specific type restriction
     */
    public const NOT_TYPED = 'NOT_TYPED';


    /**
     * Specifies that a parameter is of boolean type
     * It must be received by the service as a json encoded string containing a boolean value or an exception will be thrown
     */
    public const BOOL = 'BOOL';


    /**
     *Specifies that a parameter is of number type
     *It must be received by the service as a json encoded string containing a number value or an exception will be thrown
     */
    public const NUMBER = 'NUMBER';


    /**
     * Specifies that a parameter is of string type
     * It must be received by the service as a json encoded string containing a string value or an exception will be thrown
     */
    public const STRING = 'STRING';


    /**
     * Specifies that a parameter is of array type
     * It must be received by the service as a json encoded string containing an array value or an exception will be thrown
     */
    public const ARRAY = 'ARRAY';


    /**
     * Specifies that a parameter is of object type
     * It must be received by the service as a json encoded string containing an object value or an exception will be thrown
     */
    public const OBJECT = 'OBJECT';


    /**
     * Specifies that a parameter is mandatory: Any webservice call that does not specify the parameter will throw an exception
     */
    public const REQUIRED = 'REQUIRED';


    /**
     * Specifies that a parameter is NOT mandatory: It can be avoided when calling the webservice
     */
    public const NOT_REQUIRED = 'NOT_REQUIRED';


    /**
     * Specifies that a parameter can have any of its defined type possible values. If we want to restrict the parameter possible values, we must
     * set an array of items that match the parameter's declared type
     */
    public const NOT_RESTRICTED = 'NOT_RESTRICTED';


    /**
     * Defines which content type will be generated by the response of this webservice.
     *
     * Note: This value will only apply when the webservice is executed through a website api call. It won't have any effect when the
     * webservice is instantiated and executed at the code side.
     *
     * Common values are:
     *
     * 'text/plain' - Simple text data, (generally ASCII or ISO 8859-n)<br>
     * 'text/html' - HyperText Markup Language (HTML)<br>
     * 'text/css' - Cascading Style Sheets (CSS)<br>
     * 'text/csv' - Comma-separated values (CSV)<br>
     * 'text/javascript' - JavaScript code<br>
     * 'image/jpg' - jpg images<br>
     * 'image/gif' - Graphics Interchange Format (GIF)<br>
     * 'image/png' - Portable Network Graphics<br>
     * 'image/svg+xml' - Scalable Vector Graphics (SVG)<br>
     * 'application/json' - (Default) Data in JSON format<br>
     * 'application/php' - Hypertext Preprocessor (Personal Home Page)<br>
     * 'application/pdf' - Pdf binary files<br>
     * 'application/gzip' - GZip Compressed Archive<br>
     * 'application/octet-stream' - Any kind of raw binary data<br>
     * 'audio/mpeg' - MP3 audio<br>
     * 'video/mpeg' - MPEG Video
     *
     * @var string
     */
    public $contentType = 'application/json';


    /**
     * Array of arrays with the list of POST parameters that are accepted by this service.
     *
     * Each array element must be another array with between 1 and 4 elements:<br>
     *     1 - NAME: A string with the name for a POST parameter that will be accepted by the service.<br>
     *     2 - TYPE: (optional) Specifies the POST parameter data type restriction: WebService::NOT_TYPED (default), WebService::BOOL, WebService::NUMBER, WebService::STRING, WebService::ARRAY, WebService::OBJECT<br>
     *     3 - REQUIRED: (optional) Specifies if the POST parameter is mandatory or not: WebService::REQUIRED (default) or WebService::NOT_REQUIRED.<br>
     *     4 - POSSIBLE VALUES: (optional) Specifies the POST parameter allowed values: WebService::NOT_RESTRICTED or an array with all the possible values (withih the defined type) that the parameter is allowed to have.<br>
     *     5 - DEFAULT VALUE: (optional) Specifies the POST parameter default value. This value will be used if the POST parameter is not received by the service.
     *
     * Any POST parameter that is passed to the service which is not enabled on this list will make the service fail.
     *
     * NOTE: This vaule can only be initialized when overriding the WebService setup() method by the service itself.
     *
     * @var array
     */
    protected $enabledPostParams = [];


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
    protected $enabledGetParams = 0;


     /**
     * This flag defines if all GET parameters must be passed to the service when GET parameters are enabled on the service.
     * If set to true, all the GET parameter values must be passed to the service.
     */
    protected $isGetDataMandatory = true;


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
     *        to the service where POST parameters are defined at the array keys. The array values will be json_encoded if they are not strings.
     *        Same rules as when calling the service via url apply here.
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

        // Check GET parameters are valid
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

            foreach ($postParameters as $receivedPostParamName => $value) {

                $this->_receivedPostParameters[$receivedPostParamName] = is_string($value) ? $value : json_encode($value);
            }

        }else{

            foreach ($_POST as $receivedPostParamName => $value) {

                $this->_receivedPostParameters[$receivedPostParamName] = $value;
            }
        }

        // Format and verify all the enabled POST parameters
        $receivedPostParamNames = array_keys($this->_receivedPostParameters);

        for ($i = 0, $l = count($this->enabledPostParams); $i < $l; $i++) {

            if(!is_array($this->enabledPostParams[$i]) || count($this->enabledPostParams[$i]) < 1 || count($this->enabledPostParams[$i]) > 5){

                throw new UnexpectedValueException('Each enabled POST parameter must be an array with min 1 and max 5 elements');
            }

            if(!isset($this->enabledPostParams[$i][0]) || !is_string($this->enabledPostParams[$i][0])){

                throw new UnexpectedValueException('Each enabled POST parameter array first value must be a string');
            }

            if(!isset($this->enabledPostParams[$i][1])){

                $this->enabledPostParams[$i][] = self::NOT_TYPED;
            }

            if(!isset($this->enabledPostParams[$i][2])){

                $this->enabledPostParams[$i][] = self::REQUIRED;
            }

            if(!isset($this->enabledPostParams[$i][3])){

                $this->enabledPostParams[$i][] = self::NOT_RESTRICTED;
            }

            if($this->enabledPostParams[$i][1] !== self::NOT_TYPED && $this->enabledPostParams[$i][1] !== self::BOOL &&
               $this->enabledPostParams[$i][1] !== self::NUMBER && $this->enabledPostParams[$i][1] !== self::STRING &&
               $this->enabledPostParams[$i][1] !== self::ARRAY && $this->enabledPostParams[$i][1] !== self::OBJECT){

                throw new UnexpectedValueException(
                    'POST param <'.$this->enabledPostParams[$i][0].'> element[1] <'.$this->enabledPostParams[$i][1].'> must be WebService::NOT_TYPED, WebService::BOOL, WebService::NUMBER, WebService::STRING, WebService::ARRAY or WebService::OBJECT');
            }

            if($this->enabledPostParams[$i][2] !== self::REQUIRED && $this->enabledPostParams[$i][2] !== self::NOT_REQUIRED){

                throw new UnexpectedValueException('POST param <'.$this->enabledPostParams[$i][0].'> element[2] <'.$this->enabledPostParams[$i][2].'> must be WebService::REQUIRED or WebService::NOT_REQUIRED');
            }

            if($this->enabledPostParams[$i][2] === self::REQUIRED && !in_array($this->enabledPostParams[$i][0], $receivedPostParamNames)){

                throw new UnexpectedValueException('Missing mandatory POST parameter: '.$this->enabledPostParams[$i][0]);
            }

            if($this->enabledPostParams[$i][2] === self::NOT_REQUIRED && !in_array($this->enabledPostParams[$i][0], $receivedPostParamNames) && isset($this->enabledPostParams[$i][4])){

                $this->_receivedPostParameters[$this->enabledPostParams[$i][0]] = is_string($this->enabledPostParams[$i][4]) ? $this->enabledPostParams[$i][4] : json_encode($this->enabledPostParams[$i][4]);
            }

            if($this->enabledPostParams[$i][3] !== self::NOT_RESTRICTED && !is_array($this->enabledPostParams[$i][3])){

                throw new UnexpectedValueException('POST param <'.$this->enabledPostParams[$i][0].'> element[3] <'.$this->enabledPostParams[$i][3].'> must be WebService::NOT_RESTRICTED or an array of values');
            }
        }

        // Validate all the received POST parameteres
        foreach ($this->_receivedPostParameters as $receivedPostParamName => $receivedPostParamValue) {

            $isReceivedPostFound = false;

            foreach ($this->enabledPostParams as $enabledPostParam) {

                if($receivedPostParamName === $enabledPostParam[0]){

                    $isReceivedPostFound = true;

                    if($enabledPostParam[1] === self::NOT_TYPED){

                        continue;
                    }

                    $jsonDecodedValue = json_decode($receivedPostParamValue);

                    if($enabledPostParam[1] === self::BOOL && !is_bool($jsonDecodedValue)){

                        throw new UnexpectedValueException('Expected '.$receivedPostParamName.' POST param to be a json encoded boolean but was '.$receivedPostParamValue);
                    }

                    if($enabledPostParam[1] === self::NUMBER && !is_numeric($jsonDecodedValue)){

                        throw new UnexpectedValueException('Expected '.$receivedPostParamName.' POST param to be a json encoded number but was '.$receivedPostParamValue);
                    }

                    if($enabledPostParam[1] === self::STRING && !is_string($jsonDecodedValue)){

                        throw new UnexpectedValueException('Expected '.$receivedPostParamName.' POST param to be a json encoded string but was '.$receivedPostParamValue);
                    }

                    if($enabledPostParam[1] === self::ARRAY && !is_array($jsonDecodedValue)){

                        throw new UnexpectedValueException('Expected '.$receivedPostParamName.' POST param to be a json encoded array but was '.$receivedPostParamValue);
                    }

                    if($enabledPostParam[1] === self::OBJECT && (!is_object($jsonDecodedValue) || get_class($jsonDecodedValue) !== 'stdClass')){

                        throw new UnexpectedValueException('Expected '.$receivedPostParamName.' POST param to be a json encoded object but was '.$receivedPostParamValue);
                    }
                }
            }

            if(!$isReceivedPostFound){

                throw new UnexpectedValueException('Unexpected POST parameter received: '.$receivedPostParamName);
            }
        }
    }


    /**
     * Get the value for a service url GET parameter, given its parameter index number (starting at 0).
     * If the parameter index is valid, but no value has been passed into the url, it will return an empty string.
     * URL parameters are the custom values that can be passed via url to the framework services.
     * They are encoded this way: http://.../api/site/service-category/service-name/parameter0/parameter1/parameter2/...
     *
     * @param int $index The numeric index for the requested parameter (starting at 0). Invalid index value will throw an exception
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
     * Get the value for the specified POST parameter that has been passed to this service.
     *
     * If the POST parameter has some specific type defined, this method will return the value converted to that type, otherwise the raw string
     * will be given.
     *
     * @param string $paramName The name for the POST parameter we want to read
     *
     * @return string The value of the received POST parameter converted to the correctly expected data type or null if
     *         the post parameter was not provided to the service
     */
    public function getPost(string $paramName){

        foreach ($this->enabledPostParams as $enabledPostParam) {

            if($enabledPostParam[0] === $paramName){

                if(!isset($this->_receivedPostParameters[$paramName])){

                    return null;
                }

                if($enabledPostParam[1] !== self::NOT_TYPED){

                    return json_decode($this->_receivedPostParameters[$paramName]);
                }

                return $this->_receivedPostParameters[$paramName];
            }
        }

        throw new UnexpectedValueException('POST parameter is not enabled by the service: '.$paramName);
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
     * This method is executed to perform the service operations and return a result. It must be overriden by all webservice instances.
     *
     * Override this method with the actual service logic
     *
     * @return mixed The result of the service as any of the PHP basic types (bool, number, string, array, stdclass)
     *         or a WebServiceError instance
     */
    public function run(){

        // Override this method to add the actual service execution code
    }


    /**
     * Creates a WebServiceError instance with the specified data.
     * This instance is normally used as the result for webservices that need to show an error to the user.
     *
     *  @see WebServiceError::createInstance
     *
     * @param int $code @see WebServiceError::createInstance
     * @param string $title @see WebServiceError::createInstance
     * @param string $message @see WebServiceError::createInstance
     * @param string $trace @see WebServiceError::createInstance
     *
     * @return WebServiceError A newly created error instance, filled with the specified data, so we can return it on the webservice run() method
     */
    public function generateError(int $code, string $title, string $message = '', string $trace = ''){

        return WebServiceError::createInstance($code, $title, $message, $trace);
    }
}

?>