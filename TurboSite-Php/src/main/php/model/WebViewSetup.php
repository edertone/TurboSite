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
 * Contains the configuration parameters for the project views
 */
class WebViewSetup{


    /**
     * Specifies how many URL parameters are accepted by this view and allows to setup type and value restrictions.
     * If a view has a missing value for any of the enabled parameters and there's no default value defined, a 404
     * error will happen.
     *
     * <b>Two possible formats are accepted by this property:</b><br>
     *
     * 1 - An integer representing the exact number of URL parameters that are acepted by the view which will be non typed, mandatory
     * and with no default value<br>
     *
     * 2 - An array of arrays with the list of URL parameters that are accepted by this view and their respective type and value restrictions:
     *
     *     Each element on the enabledUrlParams array must be an array with between 0 and 3 elements (in this same order):<br>
     *         0 - TYPE: (optional) Specifies the URL parameter data type restriction: WebSiteManager::NOT_TYPED (default), WebSiteManager::BOOL,
     *         WebSiteManager::NUMBER, WebSiteManager::STRING, WebSiteManager::ARRAY, WebSiteManager::OBJECT<br>
     *
     *         1 - POSSIBLE VALUES: (optional) Specifies the URL parameter allowed values: WebSiteManager::NOT_RESTRICTED (default) or an
     *         array with all the possible values (same as the defined type) that the parameter is allowed to have.<br>
     *
     *         2 - DEFAULT VALUE: (optional) Specifies the URL parameter default value. This value will be used if the parameter is not
     *         received by the view. If the url does not have a value or has an empty value for this default parameter, the url will
     *         be modified via a 301 redirect to set the defined default.
     *
     *     The index for the parameter at the enabledUrlParams array is the same as the parameter at the URL.
     *
     * @var int|array
     */
    public $enabledUrlParams = [];


    /**
     * If we want to fix some of the URL parameters to a fixed value, we can use this method.
     * When the url is loaded, if any of the URL parameters that have been fixed has a different value, a redirect will be
     * performed to replace them with the fixed value.
     *
     * A callback function will be passed here, which will be executed after the view and default params have been
     * initialized. This method must return an array with the same length as the enabled URL parameters. Each array element
     * will be a value that will be forced on the same index view parameter and the current url redirected if any of the
     * forced parameters values differ from the actual ones.
     */
    public $forcedParametersCallback = null;


    /**
     * Defines the amount of seconds that the whole view will remain on cache.
     * If set to -1 the view will remain on cache for an infinite amount of time or until the cache is cleared
     *
     * @example 1 minute = 60 seconds
     * @example 1 hour = 3600 seconds
     */
    public $cacheLifeTime = -1;
}

?>