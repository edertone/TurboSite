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
     * Defines if the web service accepts POST parameters. Note that when using POST, all the service parameters must be passed through
     * a POST variable called "data". We can pass there a single string or a JSON object containing all the service parameter values we
     * want.
     *
     * The variable "data" is the only POST value that will be accepted by the service. All the data to the service must be passed
     * via this variable. Any other POST variable that is sent to the service will make it fail.
     */
    public $isPostDataEnabled = false;


    /**
     * This flag defines if POST data must be passed to the service when POST data is enabled on the service.
     * If set to true, the "data" POST variable must be passed to the service and be non empty.
     */
    public $isPostDataMandatory = true;


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
     * Stores the actual values of the GET parameters that have been passed to this service via the URL, sorted in the same order as specified
     * in the url
     */
    private $_receivedURLGetParameters = [];


    /**
     * Stores the number of GET parameters that have been passed to this service via the URL
     */
    private $_receivedURLGetParametersCount = 0;


    /**
     * Contains the current url fragment that starts just after https://.../api/
     */
    private $_URI = '';


    /**
     * Contains the value for the current service url URI fragment just after https://.../api/ but splitted as an array
     * where each element is a URI fragment (fragments are divided by /).
     */
    private $_URIElements = [];


    /**
     * Class constructor
     *
     * @param array $getParameters If we create this service via code we can pass the GET data here and it will be loaded
 *            by the service as if it was passed via HTTP GET. It must be an array containing each one of the parameters that
 *            would be passed via the url, sorted in the same way as they would in the url.First array elemtn will be the param 1,
 *            second will be param 2, and so. Same rules as when calling the service via url apply.
     * @param string $postData If we create this service via code we can pass the POST data here and it will be loaded
     *        by the service as if it was passed via HTTP POST. It must be a string that contains the info we want to pass
     *        to the service, as a plain string or encoded any way we want.  Same rules as when calling the service via url apply.
     */
    public function __construct(array $getParameters = null, string $postData = null){

        $this->setup();

        $ws = WebSiteManager::getInstance();

        // Initialize service useful values
        $this->_URI = explode('/api/', $ws->getFullUrl())[1];
        $this->_URIElements = explode('/', $this->_URI);

        // Process the service GET parameters
        if($getParameters !== null){

            $this->_receivedURLGetParameters = $getParameters;

        }else{

            // Parse the service GET parameters if any exist and store them to _receivedURLGetParameters
            $serviceNameFound = false;
            $serviceName = StringUtils::getPathElement(get_class($this));

            foreach ($this->_URIElements as $uriElement) {

                if($serviceNameFound){

                    $this->_receivedURLGetParameters[] = $uriElement;

                }else if(StringUtils::formatCase($uriElement, StringUtils::FORMAT_UPPER_CAMEL_CASE) === $serviceName){

                    $serviceNameFound = true;
                }
            }
        }

        $this->_receivedURLGetParametersCount = count($this->_receivedURLGetParameters);

        // Check get parameters are valid
        if(($this->isGetDataMandatory && $this->_receivedURLGetParametersCount !== $this->enabledGetParams) ||
            $this->_receivedURLGetParametersCount > $this->enabledGetParams){

            throw new UnexpectedValueException('Invalid number of get parameters passed to service. Received '.
                $this->_receivedURLGetParametersCount.' but expected '.$this->enabledGetParams);
        }

        // Process the service POST parameters
        if($postData !== null){

            $_POST['data'] = $postData;
        }

        // Check post parameters are valid
        if($this->isPostDataEnabled && $this->isPostDataMandatory && count(array_keys($_POST)) === 0){

            throw new UnexpectedValueException('This service expects POST data');
        }

        if(!$this->isPostDataEnabled && count(array_keys($_POST)) > 0){

            throw new UnexpectedValueException('Received POST variables but POST not enabled on service');
        }

        $expectedPostVars = isset($_POST['data']) ? 1 : 0;

        if(count(array_keys($_POST)) !== $expectedPostVars){

            throw new UnexpectedValueException('Unexpected POST variables received. Only "data" variable is accepted');
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
    public function getParam(int $index = 0, bool $removeHtmlTags = true){

        if($index < 0){

            throw new UnexpectedValueException('Invalid service parameter index: '.$index);
        }

        if($index >= $this->enabledGetParams){

            if($this->isPostDataEnabled){

                return '';
            }

            throw new UnexpectedValueException('Disabled service parameter index '.$index.' requested');
        }

        if(!$this->isGetDataMandatory && !isset($this->_receivedURLGetParameters[$index])){

            return '';
        }

        return $removeHtmlTags ?
            strip_tags($this->_receivedURLGetParameters[$index]) :
            $this->_receivedURLGetParameters[$index];
    }


    /**
     * Get the POST information that has been passed to this service as raw string.
     *
     * @return string The value of the received POST "data" variable as a raw string or an empty string if no "data"
     *         variable has been passed to the service.
     */
    public function getPostData(){

        if(isset($_POST['data'])){

            return $_POST['data'];
        }

        return '';
    }


    /**
     * Get the POST data that has been passed to this service casted as an integer.
     *
     * @return number The value of the received POST "data" variable converted to its integer value or 0 if no "data"
     *         variable has been passed to the service.
     */
    public function getPostDataAsInt(){

        $result = $this->getPostData();

        return $result === '' ? 0 : (int) $result;
    }


    /**
     * Get the POST data that has been passed to this service casted as a float.
     *
     * @return number The value of the received POST "data" variable converted to its float value or 0 if no "data"
     *         variable has been passed to the service.
     */
    public function getPostDataAsFloat(){

        $result = $this->getPostData();

        return $result === '' ? 0 : (float) $result;
    }


    /**
     * Get the POST data that has been passed to this service converted to an associative array.
     *
     * @return array The value of the received POST "data" variable converted to an associative array or [] if no "data"
     *         variable has been passed to the service.
     */
    public function getPostDataAsArray(){

        $postData = $this->getPostData();

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
     * @return mixed The result of the service as any of the PHP basic types.
     */
    public function run(){

        // Override this method to add the actual service execution code
    }
}

?>