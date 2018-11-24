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
	 * Defines how many parameters are accepted by this view. Anyones beyond this limit will be removed from the current url.
     * If a view has a missing value for any of the enabled parameters and there's no default value defined, a 404 error will happen
     */
    public $enabledParams = 0;


    /**
     * A list of default values for the view parameters. If the current url does not have a value or has an empty value for
     * a default parameter, the url will be modified via a 301 redirect to set the defined default.
     */
    public $defaultParameters = [];


    /**
     * Forces several view parameters to a fixed value. A callback function will be passed here, which will be executed
     * after the view and default params have been initialized. This method must return an array with the same length as the enabled
     * parameters. Each array element will be a value that will be forced on the same index view parameter and the current url
     * redirected if any of the forced parameters values differ from the actual ones.
     */
    public $forcedParametersCallback = null;


    /**
     * A list of arrays that will define the only possible values that are allowed for each one of the view parameters.
     * If an array of this list is empty, the view parameter on the same index will have no restrictions.
     * If the array is not empty, the respective view parameter will accept only the provided values. If the received parameter
     * value does not match any of them, the url will be redirected to the most similar of the possible ones.
     */
    public $allowedParameterValues = [];


    /**
     * Defines the amount of seconds that the view will remain on cache.
     * If set to -1 the view will remain on cache for an infinite amount of time
     *
     * @example 1 minute = 60 seconds
     * @example 1 hour = 3600 seconds
     */
    public $cacheLifeTime = -1;
}

?>