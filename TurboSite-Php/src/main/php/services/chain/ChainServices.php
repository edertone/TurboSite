<?php

/**
 * TurboSite is a web site creation framework.
 *
 * Website : -> http://www.turboframework.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2018 Edertone Advanded Solutions. http://www.edertone.com
 */

namespace org\turbosite\src\main\php\services\chain;

use UnexpectedValueException;
use stdClass;
use org\turbosite\src\main\php\managers\WebServiceManager;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbosite\src\main\php\managers\WebSiteManager;


/**
 * ChainServices
 */
class ChainServices extends WebServiceManager{


    /**
     * If set to true, when a webservice fails then the next ones won't be executed and an empty result will be returned for each of them.
     * If set to false, all webservices will be executed even if errors happen on any of them.
     *
     * @default false
     * @var string
     */
    public $isAnyErrorStoppingExecution = false;


    /**
     * ChainServices is a WebService class that is used to sequentially execute multiple WebServices, one after the other. It receives a list
     * of services to be executed with their respective parameters and it will run each one of them in the same
     * order as received, returning a list with the results for each one of the executed services.
     *
     * @see WebServiceManager::__construct
     *
     * @param array $urlParameters This parameter is not used and will be ignored. It exists here to maintain compatibility with the WebService class on http requests
     * @param array $postParameters An associative array with only one key 'services' containing an array of stdClass instances. Each instance must have the following properties:<br>
     *        - class: When specified, it must contain the full classpath for the WebService to execute. For example: 'org\turbosite\src\test\resources\model\webservice\ServiceWithoutParams'.
     *        If class property is specified, the uri property is not allowed<br>
     *        - uri: When specified, it must contain the WebService URL that is relative to the WebServer application root. For example: 'api/site/example/example-service-without-params'.
     *        If uri property is specified, the class property is not allowed<br>
     *        - urlParameters: The list of URL parameters to pass to the Webservice to execute. @see WebServiceManager::__construct for details<br>
     *        - postParameters: The list of POST parameters to pass to the Webservice to execute. @see WebServiceManager::__construct for details
     */
    public function __construct(array $urlParameters = null, array $postParameters = null){

        parent::__construct($urlParameters, $postParameters);

        // TODO
        $this->isAnyErrorStoppingExecution = $this->getPostParam('isAnyErrorStoppingExecution');

        foreach ($this->getPostParam('services') as $service) {

            if(!($service instanceof stdClass)){

                throw new UnexpectedValueException('Each service must be defined as a php stdClass() but was '.print_r($service, true));
            }

            if((!isset($service->class) || StringUtils::isEmpty($service->class)) &&
                (!isset($service->uri) || StringUtils::isEmpty($service->uri))){

                throw new UnexpectedValueException('A namespace + class or an uri is mandatory to locate the service to execute');
            }

            if(isset($service->class) && isset($service->uri)){

                throw new UnexpectedValueException('Services can only be defined by class or uri, not both');
            }

            if(isset($service->class) && !class_exists($service->class)){

                throw new UnexpectedValueException('Provided class does not exist: '.$service->class);
            }

            // Uri execution will only work when called via http, so $_POST 'services' variable must exist
            if(isset($service->uri) && !isset($_POST['services'])){

                throw new UnexpectedValueException('ChainServices uri can only be defined when called via http request');
            }
        }
    }


    protected function setup(){

        $this->enabledPostParams[] = ['services', WebServiceManager::ARRAY];
        $this->enabledPostParams[] = ['isAnyErrorStoppingExecution', WebServiceManager::BOOL, WebServiceManager::NOT_REQUIRED, WebServiceManager::NOT_RESTRICTED, true];

        $this->authorizeMethod = function () { return true; };
    }


    public function run(){

        $resultsList = [];
        $ws = WebSiteManager::getInstance();

        foreach ($this->getPostParam('services') as $service) {

            $urlParameters = isset($service->urlParameters) ? $service->urlParameters : [];
            $postParameters = isset($service->postParameters) ? json_decode(json_encode($service->postParameters), true) : [];

            if(isset($service->class)){

                $resultsList [] = (new $service->class($urlParameters, $postParameters))->run();

            }else{

                foreach ($ws->getSetup('turbosite.json')->webServices->api as $apiDefinition) {

                    $apiUri = StringUtils::formatPath($apiDefinition->uri, '/').'/';

                    if (strpos($service->uri, $apiUri) !== false) {

                        $nameSpace = StringUtils::getPath($apiDefinition->namespace."\\".(explode($apiUri, $service->uri, 2)[1]), 1, "\\")."\\";
                        $serviceClass = $nameSpace.StringUtils::formatCase(StringUtils::getPathElement($service->uri), StringUtils::FORMAT_UPPER_CAMEL_CASE);

                        $resultsList [] = (new $serviceClass($urlParameters, $postParameters))->run();

                        break;
                    }
                }
            }
        }

        return $resultsList;
    }
}

?>