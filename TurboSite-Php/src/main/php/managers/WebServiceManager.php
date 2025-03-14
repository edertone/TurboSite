<?php

/**
 * TurboSite is a web site creation framework.
 *
 * Website : -> http://www.turboframework.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2018 Edertone Advanded Solutions. http://www.edertone.com
 */

namespace org\turbosite\src\main\php\managers;

use Exception;
use UnexpectedValueException;
use org\turbocommons\src\main\php\managers\SerializationManager;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbosite\src\main\php\model\WebServiceError;
use org\turbosite\src\main\php\model\UrlParamsBase;


/**
 * WebServiceManager
 */
abstract class WebServiceManager extends UrlParamsBase{


    /**
     * Specifies that a POST parameter is NOT mandatory: It can be avoided when calling the webservice
     */
    public const NOT_REQUIRED = 'NOT_REQUIRED';


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
     * False by default. If true, the cache http headers will not be explicitly disabled for the service output and browsers
     * will store in their cache the results of this service output. We usually set this to false on dynamic services, and enable
     * it when serving static content, images or requests that use url parameters.
     *
     * NOTICE that browsers will not cache responses for urls that work with POST parameters, even if this is set to true.
     *
     * @var boolean
     */
    public $isBrowserCacheEnabled = false;


    /**
     * Specifies how many URL parameters are accepted by this service and allows to setup type and value restrictions. URL parameters on
     * http services are passed in the following form: ../api/..../service-name/param0/param1/param2/... (parameters that are passed
     * via GET to the service url (?param1=v1&param2=v2...) are not accepted and will be ignored). Also any parameter on the URL which
     * index is not defined here will make the service fail.
     *
     * NOTE: This property can only be set when overriding the WebServiceManager setup() method by the service classes that extend WebServiceManager.
     *
     * <b>Two possible formats are accepted by this property:</b><br>
     *
     * 1 - An integer representing the exact number of URL parameters that are acepted by the service which will be non typed, mandatory
     * and with no default value<br>
     *
     * 2 - An array of arrays with the list of URL parameters that are accepted by this service and their respective type and value restrictions:
     *
     *     Each element on the enabledUrlParams array must be an array with between 0 and 3 elements:<br>
     *         0 - TYPE: (optional) Specifies the URL parameter data type restriction: WebServiceManager::NOT_TYPED (default), WebServiceManager::BOOL,
     *         WebServiceManager::INT, WebServiceManager::NUMBER, WebServiceManager::STRING, WebServiceManager::ARRAY, WebServiceManager::OBJECT<br>
     *
     *         1 - POSSIBLE VALUES: (optional) Specifies the URL parameter allowed values: WebServiceManager::NOT_RESTRICTED (default) or an
     *         array with all the possible values (withih the defined type) that the parameter is allowed to have.<br>
     *
     *         2 - DEFAULT VALUE: (optional) Specifies the URL parameter default value. This value will be used if the parameter is not
     *         received by the service.
     *
     *     The index for the parameter at the enabledUrlParams array is the same as the parameter at the URL.
     *
     * @var int|array
     */
    protected $enabledUrlParams = [];


    /**
     * Specifies how many POST parameters are accepted by this service and allows to setup type and value restrictions (Any POST parameter that is passed to the service
     * which is not enabled on this list will make the service fail).
     *
     * NOTE: This property can only be set when overriding the WebServiceManager setup() method by the service classes that extend WebServiceManager.
     *
     * <b>This property must be an array, where each element accepts two possible formats:</b><br>
     *
     * 1 - A string that represents the name for the POST parameter, which will be non typed, mandatory and with no default value<br>
     *
     * 2 - An array between 1 and 5 elements (in the same order as provided here):<br>
     *     0 - NAME: A string with the name for a POST parameter that will be accepted by the service.<br>
     *     1 - TYPE: (optional) Specifies the POST parameter data type restriction: WebServiceManager::NOT_TYPED (default), WebServiceManager::BOOL, WebServiceManager::NUMBER, WebServiceManager::STRING, WebServiceManager::ARRAY, WebServiceManager::OBJECT<br>
     *     2 - REQUIRED: (optional) Specifies if the POST parameter is mandatory and must be specified to the service or not: WebServiceManager::REQUIRED (default) or WebServiceManager::NOT_REQUIRED.<br>
     *     3 - POSSIBLE VALUES: (optional) Specifies the POST parameter allowed values: WebServiceManager::NOT_RESTRICTED (default) or an array with all the possible values (withih the defined type) that the parameter is allowed to have.<br>
     *     4 - DEFAULT VALUE: (optional) Specifies the POST parameter default value. This value will be used if the parameter is not received by the service.
     *
     * @var array
     */
    protected $enabledPostParams = [];


    /**
     * We must define here a method that will be used to authorize the webservice when necessary:
     *
     * If the web service does not require user authorization, the method must simply return true.
     * If authorization is required, the method must return true when authorization is successful and false when it fails.
     *
     * The service will throw an exception at constructor time if this method is not defined or the authorization fails
     *
     * @var callable
     */
    protected $authorizeMethod = null;


    /**
     * Stores the actual values of the POST parameters that have been passed to this service via POST or via service constructor
     */
    private $_receivedPostParams = [];


    /**
     * Contains a serialization manager instance to be used by this class
     *
     * @var SerializationManager
     */
    private $serializationManager = null;


    /**
     * This method is always called before any other thing at the web service constructor.

     * It must be declared to define the service setup values like enabling URL or POST parameters and
     * any other required customizations.
     *
     * @return void
     */
    abstract protected function setup();


    /**
     * This method is executed to perform the service operations and return a result.
     *
     * It must be declared by all WebServiceManager instances.
     *
     * It is called after the setup() and constructor() service methods.
     *
     * Add to this method all the actual service logic
     *
     * @return mixed The result of the service as any of the PHP basic types (bool, number, string, array, stdclass)
     *         or a WebServiceError instance
     */
    abstract public function run();


    /**
     * Defines the base class manager for all the project web services.
     *
     * Any web service that is accessible via API calls must extend this class and override the setup() and run() methods.
     *
     * The extended service class must follow this naming convention: "UrlStringService", where UrlString will contain the
     * text that will be used to call the service via the http url. For example, to create a service that will be called like
     * http://...../create-new-user we will extend WebServiceManager as "CreateNewUserService". Notice that the last part "Service"
     * will be ignored for the url, but is mandatory.
     *
     * @param array $urlParameters If we create this service via code we can pass the URL data here and it will be loaded
     *        by the service as if it was passed via HTTP. It must be an array containing each one of the parameter values that
     *        would be passed via the url, sorted in the same way as they would in the url. First array element will be the param 0,
     *        second will be param 1, and so. Type restrictions will be applied to the parameters if a type has been defined: Not typed
     *        parameters will be left as received if the value is a string, and json encoded if the received value is a non string type.
     *        Same rules as when calling the service via url apply.
     * @param array $postParameters If we create this service via code we can pass the POST data here and it will be loaded
     *        by the service as if it was passed via HTTP POST. It must be an associative array that contains the info we want to pass
     *        to the service where POST parameters are defined at the array keys. Type restrictions will be applied to the parameters if a type has been defined:
     *        Not typed parameters will be left as received if the value is a string, and json encoded if the received value is a non string type. Same rules as when
     *        calling the service via url apply.
     */
    public function __construct(array $urlParameters = null, array $postParameters = null){

        parent::__construct();

        $this->setup();

        // Obtain the received parameters from the active URL or from the constructor
        if($urlParameters !== null){

            $this->_setReceivedParamsFromArray($urlParameters);

        }else{

            // Notice that we will remove the last 'Service' string from the service class name, cause it is not present at the url
            // Service is mandatory at the end of the service class name, so we will basically remove the last 7 characters
            $serviceClassName = substr(StringUtils::getPathElement(get_class($this)), 0, -7);

            for ($i = 0, $l = count($this->_URIElements); $i < $l; $i++) {

                if(StringUtils::formatCase($this->_URIElements[$i], StringUtils::FORMAT_UPPER_CAMEL_CASE) === $serviceClassName){

                    $this->_setReceivedParamsFromUrl($i + 1);

                    break;
                }
            }
        }

        $this->_setEnabledUrlParams($this->enabledUrlParams);
        $this->_processUrlParams();

        if(!is_array($this->enabledPostParams)){

            throw new UnexpectedValueException('enabledPostParams must be an array of arrays');
        }

        // Process the service POST parameters
        if($postParameters !== null){

            foreach ($postParameters as $receivedPostParamName => $value) {

                $this->_receivedPostParams[$receivedPostParamName] = is_string($value) ? $value : json_encode($value);
            }

        }else{

            foreach ($_POST as $receivedPostParamName => $value) {

                $this->_receivedPostParams[$receivedPostParamName] = $value;
            }
        }

        // Format and verify all the enabled POST parameters
        $receivedPostParamNames = array_keys($this->_receivedPostParams);

        for ($i = 0, $l = count($this->enabledPostParams); $i < $l; $i++) {

            if(is_string($this->enabledPostParams[$i])){

                $this->enabledPostParams[$i] = [$this->enabledPostParams[$i]];
            }

            if(!is_array($this->enabledPostParams[$i]) || count($this->enabledPostParams[$i]) < 1 || count($this->enabledPostParams[$i]) > 5){

                throw new UnexpectedValueException('Each enabled POST parameter must be a string or an array with min 1 and max 5 elements');
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

            $this->_validateParameterExpectedType($this->enabledPostParams[$i][1], 'POST param <'.$this->enabledPostParams[$i][0].'> element[1] <'.$this->enabledPostParams[$i][1].'>');

            if($this->enabledPostParams[$i][2] !== self::REQUIRED && $this->enabledPostParams[$i][2] !== self::NOT_REQUIRED){

                throw new UnexpectedValueException('POST param <'.$this->enabledPostParams[$i][0].'> element[2] <'.$this->enabledPostParams[$i][2].'> must be WebServiceManager::REQUIRED or WebServiceManager::NOT_REQUIRED');
            }

            if($this->enabledPostParams[$i][2] === self::REQUIRED && !in_array($this->enabledPostParams[$i][0], $receivedPostParamNames)){

                throw new UnexpectedValueException('Missing mandatory POST parameter: '.$this->enabledPostParams[$i][0]);
            }

            if($this->enabledPostParams[$i][2] === self::NOT_REQUIRED && !in_array($this->enabledPostParams[$i][0], $receivedPostParamNames) && isset($this->enabledPostParams[$i][4])){

                $this->_receivedPostParams[$this->enabledPostParams[$i][0]] = is_string($this->enabledPostParams[$i][4]) ? $this->enabledPostParams[$i][4] : json_encode($this->enabledPostParams[$i][4]);
            }

            if($this->enabledPostParams[$i][3] !== self::NOT_RESTRICTED && !is_array($this->enabledPostParams[$i][3])){

                throw new UnexpectedValueException('POST param <'.$this->enabledPostParams[$i][0].'> element[3] <'.$this->enabledPostParams[$i][3].'> must be WebServiceManager::NOT_RESTRICTED or an array of values');
            }
        }

        // Validate all the received POST parameteres
        foreach ($this->_receivedPostParams as $receivedPostParamName => $receivedPostParamValue) {

            $isReceivedPostFound = false;

            foreach ($this->enabledPostParams as $enabledPostParam) {

                if($receivedPostParamName === $enabledPostParam[0]){

                    $this->_validateParameterType($receivedPostParamValue, json_decode($receivedPostParamValue), $enabledPostParam[1], 'Expected '.$receivedPostParamName.' POST param');

                    $isReceivedPostFound = true;

                    break;
                }
            }

            if(!$isReceivedPostFound){

                throw new UnexpectedValueException('Unexpected POST parameter received: '.$receivedPostParamName);
            }
        }

        if($this->authorizeMethod === null || call_user_func($this->authorizeMethod) !== true){

            throw new UnexpectedValueException('authorization failed');
        }
    }


    /**
     * Get the value for a POST parameter which has been passed to this service.
     *
     * If the POST parameter has some specific type defined, this method will return the value converted to that type, otherwise the raw string
     * will be given.
     *
     * NOTICE: Trying to obtain these values before the service constructor is called will throw an error.
     *
     * @see WebServiceManager::$enabledPostParams
     *
     * @param string $paramName The name for the POST parameter we want to read
     *
     * @return string The value of the received POST parameter converted to the correctly expected data type or null if
     *         the post parameter was not provided to the service
     */
    public function getPostParam(string $paramName){

        foreach ($this->enabledPostParams as $enabledPostParam) {

            if($enabledPostParam[0] === $paramName){

                if(!isset($this->_receivedPostParams[$paramName])){

                    return null;
                }

                if($enabledPostParam[1] !== self::NOT_TYPED){

                    return json_decode($this->_receivedPostParams[$paramName]);
                }

                return $this->_receivedPostParams[$paramName];
            }
        }

        throw new UnexpectedValueException('POST parameter is not enabled by the service: '.$paramName);
    }


    /**
     * Get the value for a POST parameter which has been passed to this service, serialized to the provided class instance
     *
     * NOTICE: Trying to obtain these values before the service constructor is called will throw an error.
     *
     * @see WebServiceManager::$enabledPostParams
     *
     * @param string $paramName The name for the POST parameter we want to read
     * @param mixed $classInstance A class instance that will be automatically filled with the data on the post parameter
     *
     * @throws UnexpectedValueException If serialization failed
     *
     * @return mixed The provided class instance filled with all the data from the post parameter
     */
    public function getPostParamSerialized(string $paramName, $classInstance){

        // Create the serialization manager instance if it does not exist
        if($this->serializationManager === null){

            $this->serializationManager = new SerializationManager();
        }

        return $this->serializationManager->jsonToClass($this->getPostParam($paramName), $classInstance);
    }


    /**
     * Creates a WebServiceError instance with the specified data.
     * This instance is normally used as the result for webservices that need to show an error to the user.
     * Notice that the error trace and message will not be visible if GlobalErrorManager::getInstance()->exceptionsToBrowser is false
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

        if($trace === ''){

            $trace = (new Exception)->getTraceAsString();
        }

        return WebServiceError::createInstance($code, $title, $message, $trace);
    }
}