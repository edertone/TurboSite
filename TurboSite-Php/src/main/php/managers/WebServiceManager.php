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

use org\turbocommons\src\main\php\model\BaseSingletonClass;


/**
 * Manages all the operations related to the API REST webservices
 */
class WebServiceManager extends BaseSingletonClass{


    /**
     * Compute the full file system path for the service that is defined by the current browser URL
     */
    public function getCurrentServiceFilePath(){

        $ws = WebSiteManager::getInstance();

        // Obtain an array with the folders of the current url after the .../api/ part
        $apiURI = explode('/', explode('/api/', $ws->getFullUrl(), 2)[1]);

        // Try to find the path for a service located on api/.../.../service.php
        $path = $ws->getPath('api/'.$apiURI[0].'/'.$apiURI[1].'/'.$apiURI[2].'.php');

        if(is_file($path)){

            return $path;
        }

        // Try to find the path for a service located on api/.../.../.../service.php
        $path = $ws->getPath('api/'.$apiURI[0].'/'.$apiURI[1].'/'.$apiURI[2].'/'.$apiURI[3].'.php');

        if(is_file($path)){

            return $path;
        }

        // The specified service url is not correct
        $ws->show404Error();
    }


    /**
     * TODO
     */
    public function initializeService(){

        // TODO
    }


    /**
     * TODO
     *
     * @param string $rootProjectPath The filesystem path to the root of the website project src/main folder
     * @param string $apiFolder The api subfolder to which this API manager is binded. All API calls must be performed as
     *        /api/apiFolder/api-operations. This API manager will only accept
     */
    public function generateContent(){

        // api.centroalum.com/api/site/...
        // api.centroalum.com/api/server/users/login/
    }
}

?>