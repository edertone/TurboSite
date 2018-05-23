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

use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbocommons\src\main\php\managers\LocalizationManager;
use org\turbocommons\src\main\php\managers\FilesManager;
use org\turbocommons\src\main\php\model\BaseSingletonClass;


/**
 * TODO
 */
class WebSite extends BaseSingletonClass{


    /**
	 * Contains the name for the view that is loaded when a single root parameter is
	 * specified on the urls
	 */
    private $_singleParameterView = '';


	/**
	 * Contains the name for the view that is used as home page
	 */
	private $_homeView = '';


	/**
     * The list with project locales sorted by preference
     */
	private $_locales = [];


	/**
	 * The list with project languages sorted by preference (same as locales but
	 * only 2 first characters without the country part)
	 */
    private $_languages = [];


	/**
	 * Files manager instance for file system interaction
	 */
	private $_filesManager = null;


	/**
	 * Class that manages the text translations
	 */
	private $_localizationManager = null;


	/**
	 * Contains the value for the current url URI fragment
	 */
	private $_URI = '';


	/**
	 * Contains the value for the current url URI fragment but splitted as an array
	 * where each element is a URI fragment (fragments are divided by /)
	 */
	private $_URIElements = [];


	/**
	 * Contains the value for the current url URI fragment
	 */
	private $_fullURL = '';


	/**
	 * Get the first language of the list of translation priorities
	 */
	public function getPrimaryLanguage(){

	    return $this->_languages[0];
	}


	/**
	 * Get the website current full url as it is shown on the user browser
	 */
	public function getFullUrl(){

	    return $this->_fullURL;
	}


	/**
	 * Initialize the website structure and generate the html code for the current url
	 */
	public function construct(){

	    $this->_filesManager = new FilesManager();
	    $this->_localizationManager = new LocalizationManager();

	    $this->_initializeSetup();

	    // TODO - initialize localization manager
	    //$this->_localizationManager->initialize();

	    // 301 Redirect to home view if current URI is empty
	    if(StringUtils::isEmpty($this->_URI)){

	        $this->_redirect301($this->_languages[0].'/'.$this->_homeView);
	    }

	    $this->_includeContentBasedOnURI();
	}


	/**
	 * Initialize a view
	 */
	public function constructView($enabledParams = 0, $enableDummy = false, array $paramsDefault = null, $dummyDefault = ''){

	    // If URI parameters exceed the enabled ones, a redirect to remove unaccepted params will be performed
	    if((count($this->_URIElements) - 2) > $enabledParams){

	        $redirectUrl = $this->_URIElements[0].'/'.$this->_URIElements[1];

	        for ($i = 2; $i < $enabledParams - 1; $i++) {

	            $redirectUrl += '/'.$this->_URIElements[$i];
	        }

	        $this->_redirect301($redirectUrl);
	    }
	}


	/**
	 * get the website current full url as it is shown on the user browser
	 */
	private function _initializeSetup(){

	    $setup = json_decode($this->_filesManager->readFile('turbosite.json'));

	    $this->_locales = $setup->locales;
	    $this->_homeView = $setup->homeView;
	    $this->_singleParameterView = $setup->singleParameterView;

	    foreach ($setup->locales as $locale) {

	        $this->_languages[] = substr($locale, 0, 2);
	    }

	    $this->_URI = isset($_GET['q']) ? $_GET['q'] : '';
	    $this->_URIElements = explode('/', $this->_URI);
	    $this->_fullURL = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}


	/**
	 * Chech which content must be required based on the current URI
	 */
	private function _includeContentBasedOnURI(){

	    // Php files execution is not allowed
	    if(mb_strtolower(StringUtils::getPathExtension($this->_URI)) !== 'php'){

    	    // Check if the URI represents a service
	        if($this->_URIElements[0] === 'http'){

	            include('http/'.$this->_URIElements[1].'.php');
    	        die();
    	    }

    	    // Check if the URI represents a view
    	    if(in_array($this->_URIElements[0], $this->_languages)){

    	        include('view/views/'.$this->_URIElements[1].'/'.$this->_URIElements[1].'.php');
                die();
    	    }
	    }

	    // Reaching here means no match was found for the current URI, so 404 and die
        http_response_code(404);
        include('error-404.php');
        die();
	}


	/**
	 * Perform a 301 redirect (permanently moved) to the specified url.
	 */
	private function _redirect301($url){

	    header('location:/'.$url, true, 301);
	    die();
	}
}

?>