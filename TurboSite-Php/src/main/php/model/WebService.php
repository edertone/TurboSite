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
 * Contains the configuration parameters for the project services
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
    public $enablePostData = false;


    /**
     * Defines how many GET parameters are accepted by this service. Anyones beyond this limit will make the service fail.
     * If a service has a missing value for any of the enabled parameters and there's no default value defined, the service will fail.
     *
     * Get parameters on http services can only be passed in the form .../api/../../service-name/param1/param2/param3/... The standard way
     * to encode GET parameter in urls (?param1=v1&param2=v2...) is not accepted and will be ignored.
     */
    public $enabledGetParams = 0;


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
     * @param string $data If we create this service via code we can pass the POST $data here and it will be loaded
     *        by the service as if it was passed via HTTP POST
     */
    public function __construct($data = null){

        $this->setup();

        $ws = WebSiteManager::getInstance();

        // Initialize service useful values
        $this->_URI = explode('/api/', $ws->getFullUrl())[1];
        $this->_URIElements = explode('/', $this->_URI);

        // Parse the service parameters if any exist and store them to _receivedURLGetParameters
        $serviceNameFound = false;
        $serviceName = StringUtils::getPathElement(get_class($this));

        foreach ($this->_URIElements as $uriElement) {

            if($serviceNameFound){

                $this->_receivedURLGetParameters[] = $uriElement;

            }else if(StringUtils::formatCase($uriElement, StringUtils::FORMAT_UPPER_CAMEL_CASE) === $serviceName){

                $serviceNameFound = true;
            }
        }

        $this->_receivedURLGetParametersCount = count($this->_receivedURLGetParameters);

        // Check get parameters are valid
        if($this->_receivedURLGetParametersCount !== $this->enabledGetParams){

            throw new UnexpectedValueException('Invalid number of get parameters passed to service. Received '.
                $this->_receivedURLGetParametersCount.' but expected '.$this->enabledGetParams);
        }

        // Check post parameters are valid
        if(count(array_keys($_POST)) > 0 && !$this->enablePostData){

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

        if($index >= $this->_receivedURLGetParametersCount){

            throw new UnexpectedValueException('Disabled service parameter index '.$index.' requested');
        }

        return $removeHtmlTags ?
            strip_tags($this->_receivedURLGetParameters[$index]) :
            $this->_receivedURLGetParameters[$index];
    }


    /**
     * Get the POST parameters that have been passed to this service.
     * All the information that is required by the service must be passed or encoded here.
     *
     * @return string|null The value of the received POST "data" variable as a string (which should be passed through
     *                     json_decode if necessary) or null if no "data" variable has been passed to the service.
     */
    public function getPostData(){

        if(isset($_POST['data'])){

            return $_POST['data'];
        }

        return null;
    }


    public function getPostDataJSONEncoded(){

        // TODO
        
        return null;
    }


    /**
     * Override this method to setup the service
     */
    protected function setup(){

        // Override this method to modify the setup of your service
    }


    /**
     * Override this method with the actual service logic
     */
    public function run(){

        // Override this method to add the actual service execution code
    }
}

?>