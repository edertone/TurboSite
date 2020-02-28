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
use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbocommons\src\main\php\managers\BrowserManager;


/**
 * Base class for all the classes that use or work with URL parameters
 */
abstract class UrlParamsBase extends BaseStrictClass{


    /**
     * Specifies that a parameter has no specific type restriction
     * It will be available as a raw string containing the unaltered value as received by the service.
     */
    public const NOT_TYPED = 'NOT_TYPED';


    /**
     * Specifies that a parameter is of boolean type
     * It must be received by the service as a json encoded string containing a boolean value or an exception will be thrown
     */
    public const BOOL = 'BOOL';


    /**
     *Specifies that a parameter is of integer type
     *It must be received by the service as a json encoded string containing an int number value or an exception will be thrown
     */
    public const INT = 'INT';


    /**
     *Specifies that a parameter is of number type
     *It must be received by the service as a json encoded string containing a number value or an exception will be thrown
     */
    public const NUMBER = 'NUMBER';


    /**
     * Specifies that a parameter is of string type
     * It must be received by the service as a json encoded string containing a string value or an exception will be thrown
     * NOTE: Json encoded strings are expected to be surrounded by double quotes like "hello", "goodbye my friend", etc..
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
     * Instance that manages the browser operations
     */
    protected $_browserManager = null;


    /**
     * Contains the current url URI fragment starting at the root of the website application.
     *
     * If the site is published at the root of the domain, this value will be the same as the regular URI for the full url, but
     * if the site is located inside some path at the domain, this value will start at that point.
     *
     * For example:<br>
     * https://somedomain/en/someview/param1 will have a "en/someview/param1" _URI value.
     * https://somedomain/_dev/en/someview/param1 will have the same "en/someview/param1" _URI value.
     */
    protected $_URI = '';


    /**
     * Contains the same value as $this->_URI but splitted as an array where each element is a
     * URI fragment (fragments are divided by /).
     *
     * @see UrlParamsBase::$_URI
     */
    protected $_URIElements = [];


    /**
     * Contains the value for the current url including the initial https://
     */
    protected $_fullURL = '';


    /**
     * The setup for the enabled URL parameters
     *
     * @var int|array
     */
    private $_enabledUrlParams = [];


    /**
     * Number of URL parameters that have been enabled to this class
     */
    private $_enabledUrlParamsCount = 0;


    /**
     * List of actual URL parameter values that have been passed to this class via URL or constructor,
     * sorted in the same order as specified in the url or constructor array
     */
    protected $_receivedUrlParams = [];


    /**
     * Number of URL parameters that have been passed to this class
     */
    private $_receivedUrlParamsCount = 0;


    /**
     * Class constructor
     */
    public function __construct(){

        $this->_browserManager = new BrowserManager();

        $this->_URI = isset($_GET['q']) ? $_GET['q'] : '';
        $this->_URIElements = explode('/', $this->_URI);

        if(isset($_SERVER['HTTP_HOST'])){

            $this->_fullURL = $this->_browserManager->getCurrentUrl();
        }
    }


    /**
     * Initialize the enabled URL parameters property that defines which url parameters are accepted and what type or value restrictions
     * they have.
     *
     * @param array|int $enabledUrlParams an integer or an array with the enabled url params structure
     *
     * @throws UnexpectedValueException
     *
     * @return number The total number of enabled url parameters that have been defined
     */
    protected function _setEnabledUrlParams($enabledUrlParams){

        $this->_enabledUrlParams = $enabledUrlParams;

        // Validate enabled url params are correct
        if(!is_int($this->_enabledUrlParams) && !is_array($this->_enabledUrlParams)){

            throw new UnexpectedValueException('enabledUrlParams must be an int or an array of arrays');
        }

        // If enabled params are defined as an integer value, convert it to an array filled with empty arrays
        if(is_int($this->_enabledUrlParams)){

            $this->_enabledUrlParams = array_fill(0, $this->_enabledUrlParams, []);
        }

        $this->_enabledUrlParamsCount = count($this->_enabledUrlParams);

        return $this->_enabledUrlParamsCount;
    }


    /**
     * Initialize the value for the received URL parameters property by obtaining all of them from a given array.
     * Each of the array elements will be processed before being stored: If it is a string, it will be left untouched, but if it is another type of
     * value like an int or array, it will be json encoded before being stored.
     *
     * @param array $urlParameters Array containing values to be stored at the received url parameters property.
     *
     * @return void
     */
    protected function _setReceivedParamsFromArray(array $urlParameters){

        $this->_receivedUrlParams = [];

        foreach ($urlParameters as $value) {

            $this->_receivedUrlParams[] = is_string($value) ? $value : json_encode($value);
        }
    }


    /**
     * Initialize the value for the received URL parameters property by obtaining all of them from the current URI path.
     *
     * @param int $firstParamUriIndex The URI index where the first of the parameters we want to obtain is located. For example if our URI is: en/viewname/a/b/c,
     *        the parameter at the index 0 is en, the parameter at the index 3 is b, and so.
     *
     * @throws UnexpectedValueException
     *
     * @return number The number of url parameters that have been obtained from the current URL
     */
    protected function _setReceivedParamsFromUrl(int $firstParamUriIndex){

        $this->_receivedUrlParams = [];

        for ($i = $firstParamUriIndex, $l = count($this->_URIElements); $i < $l; $i++) {

            $this->_receivedUrlParams[] = $this->_URIElements[$i];
        }

        return $this->_receivedUrlParamsCount = count($this->_receivedUrlParams);
    }


    /**
     * Given an array of enabled URL params and an array of received URL params, this method will validate and process both
     * so they are ready to be used
     *
     * @throws UnexpectedValueException
     */
    protected function _processUrlParams(){

        $receivedUrlParamsModified = false;

        // Check that optional URL parameters are correctly defined
        // If a non mandatory parameter exists at position /N/, all following parameters must have at least a default value
        $optionalUrlParamFound = -1;

        for ($i = 0; $i < $this->_enabledUrlParamsCount; $i++) {

            if(isset($this->_enabledUrlParams[$i][2])){

                $optionalUrlParamFound = $i;

            }else if ($optionalUrlParamFound > 0){

                throw new UnexpectedValueException('All URL parameters must have a default value after the non mandatory defined at '.$optionalUrlParamFound);
            }
        }

        // Check that enabled params structure is ok, and fill the undefined indices with the default values if necessary
        for ($i = 0; $i < $this->_enabledUrlParamsCount; $i++) {

            if(!is_array($this->_enabledUrlParams[$i]) || count($this->_enabledUrlParams[$i]) > 3){

                throw new UnexpectedValueException('Each enabled URL parameter must be an array with min 0 and max 3 elements');
            }

            if(!isset($this->_enabledUrlParams[$i][0])){

                $this->_enabledUrlParams[$i][] = self::NOT_TYPED;
            }

            if(!isset($this->_enabledUrlParams[$i][1])){

                $this->_enabledUrlParams[$i][] = self::NOT_RESTRICTED;
            }

            $this->_validateParameterExpectedType($this->_enabledUrlParams[$i][0], 'URL param <'.$i.'> element[0] <'.$this->_enabledUrlParams[$i][0].'>');

            if(!isset($this->_receivedUrlParams[$i]) && isset($this->_enabledUrlParams[$i][2])){

                $receivedUrlParamsModified = true;
                $this->_receivedUrlParams[$i] = is_string($this->_enabledUrlParams[$i][2]) ? $this->_enabledUrlParams[$i][2] : json_encode($this->_enabledUrlParams[$i][2]);
            }

            if($this->_enabledUrlParams[$i][1] !== self::NOT_RESTRICTED && !is_array($this->_enabledUrlParams[$i][1])){

                throw new UnexpectedValueException('URL param <'.$i.'> element[1] <'.$this->_enabledUrlParams[$i][1].'> must be WebServiceManager::NOT_RESTRICTED or an array of values');
            }

            if(!isset($this->_receivedUrlParams[$i])){

                throw new UnexpectedValueException('Missing mandatory URL parameter at '.$i, 404);
            }
        }

        // Validate all the received URL parameteres against the enabled URL parameters
        $this->_receivedUrlParamsCount = count($this->_receivedUrlParams);

        for ($i = 0; $i < $this->_receivedUrlParamsCount; $i++) {

            if(!isset($this->_enabledUrlParams[$i])){

                array_splice($this->_receivedUrlParams, $i);
                $this->_receivedUrlParamsCount = count($this->_receivedUrlParams);

                throw new UnexpectedValueException('Unexpected URL parameter received at '.$i, 301);
            }

            for ($j = 0; $j < $this->_enabledUrlParamsCount; $j++) {

                if($i === $j){

                    $this->_validateParameterType($this->_receivedUrlParams[$i], json_decode($this->_receivedUrlParams[$i]), $this->_enabledUrlParams[$j][0], 'Expected URL param '.$i);

                    break;
                }
            }
        }

        return $receivedUrlParamsModified;
    }


    /**
     * Aux method to validate the type definition for a parameter
     *
     * @param string $typeDef The type definition to test
     * @param string $errorMessage Custom error message
     */
    protected function _validateParameterExpectedType($typeDef, $errorMessage){

        if($typeDef !== self::NOT_TYPED && $typeDef !== self::BOOL &&
            $typeDef !== self::INT && $typeDef !== self::NUMBER &&
            $typeDef !== self::STRING && $typeDef !== self::ARRAY &&
            $typeDef !== self::OBJECT){

                throw new UnexpectedValueException(
                    $errorMessage.' must be WebServiceManager::NOT_TYPED, WebServiceManager::BOOL, WebServiceManager::INT, WebServiceManager::NUMBER, WebServiceManager::STRING, WebServiceManager::ARRAY or WebServiceManager::OBJECT');
        }
    }


    /**
     * Aux method to validate that the type for a parameter matches the expected type definition
     *
     * @param mixed $originalValue The raw value for the parameter
     * @param mixed $decodedValue The json decoded value for the parameter
     * @param string $typeDef The type definition to test
     * @param string $errorMessage Custom error message
     */
    protected function _validateParameterType($originalValue, $decodedValue, $typeDef, $errorMessage){

        if($typeDef === self::NOT_TYPED){

            return;
        }

        if($typeDef === self::BOOL && !is_bool($decodedValue)){

            throw new UnexpectedValueException($errorMessage.' to be a json encoded boolean but was '.$originalValue, 404);
        }

        if($typeDef === self::INT && !is_int($decodedValue)){

            throw new UnexpectedValueException($errorMessage.' to be a json encoded integer but was '.$originalValue, 404);
        }

        if($typeDef === self::NUMBER && !is_numeric($decodedValue)){

            throw new UnexpectedValueException($errorMessage.' to be a json encoded number but was '.$originalValue, 404);
        }

        if($typeDef === self::STRING && !is_string($decodedValue)){

            throw new UnexpectedValueException($errorMessage.' to be a json encoded string but was '.$originalValue, 404);
        }

        if($typeDef === self::ARRAY && !is_array($decodedValue)){

            throw new UnexpectedValueException($errorMessage.' to be a json encoded array but was '.$originalValue, 404);
        }

        if($typeDef === self::OBJECT && (!is_object($decodedValue) || get_class($decodedValue) !== 'stdClass')){

            throw new UnexpectedValueException($errorMessage.' to be a json encoded object but was '.$originalValue, 404);
        }
    }


    /**
     * Get the value for a service url parameter, given its parameter index number (starting at 0).
     * If the parameter index is valid, but no value has been passed into the url and there's no default value, an exception will be thrown.
     *
     * URL parameters are the custom values that can be passed via url to the framework services.
     * They are encoded this way: http://.../api/site/service-category/service-name/parameter0/parameter1/parameter2/...
     *
     * @see UrlParamsBase::$_enabledUrlParams
     *
     * @param int $index The numeric index for the requested parameter (starting at 0). Invalid index value will throw an exception
     *
     * @return string The requested parameter value
     */
    public function getUrlParam(int $index = 0){

        if($index < 0){

            throw new UnexpectedValueException('Invalid URL parameter index: '.$index);
        }

        if(!isset($this->_enabledUrlParams[$index])){

            throw new UnexpectedValueException('Disabled parameter URL index '.$index.' requested');
        }

        if($this->_enabledUrlParams[$index][0] !== self::NOT_TYPED){

            return json_decode($this->_receivedUrlParams[$index]);
        }

        return $this->_receivedUrlParams[$index];
    }
}

?>