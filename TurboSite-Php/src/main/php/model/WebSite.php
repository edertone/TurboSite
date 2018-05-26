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
use org\turbocommons\src\main\php\managers\LocalizationManager;
use org\turbocommons\src\main\php\managers\FilesManager;
use org\turbocommons\src\main\php\model\BaseSingletonClass;
use org\turbocommons\src\main\php\managers\BrowserManager;


/**
 * TODO
 */
class WebSite extends BaseSingletonClass{


    /**
     * Stores the filesystem location for the index of the site, to be used when
     * loading other files or resources
     */
    private $_rootPath = '';


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
	 * Files manager instance for file system interaction
	 */
	private $_filesManager = null;


	/**
	 * Class that manages the text translations
	 */
	private $_localizationManager = null;


	/**
	 * Class that manages the browser operations
	 */
	private $_browserManager = null;


	/**
	 * String with the locale that is defined on the current URI.
	 * If empty, no locale's been detected on the current URI.
	 */
	private $_primaryLanguage = '';


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

	    return $this->_primaryLanguage;
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
	public function initialize($rootPath){

	    $this->_rootPath = StringUtils::formatPath(StringUtils::replace($rootPath, StringUtils::getPathElement($rootPath), ''));
	    $this->_filesManager = new FilesManager();
	    $this->_localizationManager = new LocalizationManager();
	    $this->_browserManager = new BrowserManager();

	    $this->_initializeSetup();

	    $this->_sanitizeUrl();

	    $this->_includeContentBasedOnURI();
	}


	/**
	 * get the website current full url as it is shown on the user browser
	 */
	private function _initializeSetup(){

	    $this->_URI = isset($_GET['q']) ? $_GET['q'] : '';
	    $this->_URIElements = explode('/', $this->_URI);
	    $this->_fullURL = (isset($_SERVER['HTTPS']) ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

	    $setup = json_decode($this->_filesManager->readFile('turbosite.json'));

	    $this->_homeView = $setup->homeView;
	    $this->_singleParameterView = $setup->singleParameterView;

	    // Load all the configured resourcebundle paths
	    $bundles = [];

	    foreach ($setup->resourceBundles as $bundle) {

	        $bundles[] = [
	            'path' => StringUtils::formatPath($this->_rootPath.'/'.$bundle->path),
	            'bundles' => $bundle->bundles
	        ];
	    }

	    $this->_localizationManager->initialize($this->_filesManager, $setup->locales, $bundles, function($errors){

	        if(count($errors) > 0){

	            throw new UnexpectedValueException(print_r($errors, true));
	        }
	    });

	    // Detect the primary locale from the url, browser or the project list of locales
	    $this->_primaryLanguage = $this->_URIElements[0];

	    if(!in_array($this->_primaryLanguage, $this->_localizationManager->languages())){

	        $this->_primaryLanguage = $this->_browserManager->getPreferredLanguage();

	        if(!in_array($this->_primaryLanguage, $this->_localizationManager->languages())){

	            $this->_primaryLanguage = $this->_localizationManager->languages()[0];
	        }
	    }

	    $this->_localizationManager->setPrimaryLanguage($this->_primaryLanguage);
	}

	/**
	 * Initialize a view
	 */
	public function initializeView($enabledParams = 0, $enableDummy = false, array $paramsDefault = null, $dummyDefault = ''){

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
	 * TODO
	 *
	 * @param unknown $title
	 * @param unknown $description
	 */
	public function echoViewHeadHtml($title, $description){
	    ?>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title><?php echo $title ?></title>
        <meta name="description" content="<?php echo $description ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="manifest" href="site.webmanifest">
        <link rel="apple-touch-icon" href="icon.png">
        <!-- Place favicon.ico in the root directory -->

        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/main.css">
        <?php
	}


	/**
	 * TODO
	 * @param unknown $key
	 * @param unknown $bundle
	 * @return string
	 */
	public function l($key, $bundle){

	    return $this->_localizationManager->get($key, $bundle);
	}


	/**
	 * Check that the url does not contain invalid characters or values and redirect it if necessary
	 */
	private function _sanitizeUrl(){

	    // 301 Redirect to home view if current URI is empty or a 2 digits existing locale
	    if(StringUtils::isEmpty($this->_URI) ||
	       (count($this->_URIElements) === 1 &&
	        strlen($this->_URIElements[0]) === 2 &&
	        in_array($this->_URIElements[0], $this->_localizationManager->languages()))){

	        $this->_redirect301($this->_primaryLanguage.'/'.$this->_homeView);
	    }

	    // 301 Redirect to remove any possible query string.
	    // Standard says that the first question mark in an url is the query string sepparator, and all the rest
	    // are treated as literal question mark characters. So we cut the url by the first ? index found.
	    if(strpos($this->_fullURL, '?') !== false){

	        $this->_redirect301($this->_URI);
	    }
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
    	    if($this->_primaryLanguage === $this->_URIElements[0] &&
    	       is_file('view/views/'.$this->_URIElements[1].'/'.$this->_URIElements[1].'.php')){

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