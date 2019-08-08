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
use org\turbosite\src\main\php\model\WebService;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbosite\src\main\php\managers\WebSiteManager;


/**
 * This method is used to execute multiple api calls to different services with a single http request.
 * It receives a list of services that need to be executed, and their respective parameters, and this service
 * will run each one of them in the same order as received, and return a list with all the results of each execution.
 */
class ChainServices extends WebService{


    protected function setup(){

        $this->enabledPostParams = ['services'];
    }


    public function run(){

        $resultsList = [];

        foreach ($this->getPostAsArray('services') as $service) {


            if((!isset($service->class) || StringUtils::isEmpty($service->class)) &&
               (!isset($service->uri) || StringUtils::isEmpty($service->uri))){

                throw new UnexpectedValueException('A namespace + class or an uri is mandatory to locate the service to execute');
            }

            if(isset($service->class) && isset($service->uri)){

                throw new UnexpectedValueException('Services can only be defined by class or uri, not both');
            }

            $getParameters = isset($service->getParameters) ? $service->getParameters : [];
            $postParameters = isset($service->postParameters) ? json_decode(json_encode($service->postParameters), true) : [];

            if(isset($service->class)){

                $resultsList [] = (new $service->class($getParameters, $postParameters))->run();

            } else if(isset($service->uri)){

                // Uri execution will only work when called via http, so $_POST variable must exist
                if(!isset($_POST['services'])){

                    throw new UnexpectedValueException('ChainServices can only be executed when called via http request');
                }

                $ws = WebSiteManager::getInstance();

                foreach ($ws->getSetup('turbosite.json')->webServices->api as $apiDefinition) {

                    $apiUri = StringUtils::formatPath($apiDefinition->uri, '/').'/';

                    if (strpos($service->uri, $apiUri) !== false) {

                        $nameSpace = StringUtils::getPath($apiDefinition->namespace."\\".(explode($apiUri, $service->uri, 2)[1]), 1, "\\")."\\";
                        $serviceClass = $nameSpace.StringUtils::formatCase(StringUtils::getPathElement($service->uri), StringUtils::FORMAT_UPPER_CAMEL_CASE);

                        $resultsList [] = (new $serviceClass($getParameters, $postParameters))->run();

                        break;
                    }
                }
            }
        }

        return $resultsList;
    }
}

?>