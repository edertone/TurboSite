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
	 * The number of uri parameters that are allowed on the current url
	 */
	private $_URLEnabledParameters = 0;


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
	    $this->_fullURL = $this->_browserManager->getCurrentUrl();

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

	    // Detect the primary locale from the url, cookies, browser or the project list of locales
	    $this->_primaryLanguage = $this->_URIElements[0];

	    if(!in_array($this->_primaryLanguage, $this->_localizationManager->languages())){

	        $this->_primaryLanguage = substr($this->_browserManager->getCookie('turbosite_locale'), 0, 2);

	        if(!in_array($this->_primaryLanguage, $this->_localizationManager->languages())){

	            $this->_primaryLanguage = $this->_browserManager->getPreferredLanguage();

	            if(!in_array($this->_primaryLanguage, $this->_localizationManager->languages())){

	                $this->_primaryLanguage = $this->_localizationManager->languages()[0];
	            }
	        }
	    }

	    $this->_localizationManager->setPrimaryLanguage($this->_primaryLanguage);
	}


	/**
	 * Check that the url does not contain invalid characters or values and redirect it if necessary
	 */
	private function _sanitizeUrl(){

	    // 301 Redirect to home view if current URI is empty or a 2 digits existing locale
	    if(StringUtils::isEmpty($this->_URI) ||
	        (count($this->_URIElements) === 2 &&
	            strlen($this->_URIElements[0]) === 2 &&
	            in_array($this->_URIElements[0], $this->_localizationManager->languages()) &&
	            strtolower($this->_URIElements[1]) === strtolower($this->_homeView))){

	                $this->_redirect301($this->_primaryLanguage);
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

	        // Check if the URI represents the home view
	        if($this->_primaryLanguage === $this->_URIElements[0] && count($this->_URIElements) === 1){

	            $this->_browserManager->setCookie('turbosite_locale', $this->_localizationManager->primaryLocale(), 365);
	            include('view/views/'.$this->_homeView.'/'.$this->_homeView.'.php');
	            die();
	        }

	        // Check if the URI represents a view
	        if($this->_primaryLanguage === $this->_URIElements[0] &&
	            is_file('view/views/'.$this->_URIElements[1].'/'.$this->_URIElements[1].'.php')){

	                $this->_browserManager->setCookie('turbosite_locale', $this->_localizationManager->primaryLocale(), 365);
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
	 * Get the translated text for the provided key and bundle
	 *
	 * @param unknown $key TODO
	 * @param unknown $bundle TODO
	 * @return string TODO
	 */
	public function l($key, $bundle){

	    return $this->_localizationManager->get($key, $bundle);
	}


	/**
	 * Get the value for an url parameter, given its parameter index number. If the parameter does not exist, it will return an empty string
	 * URL parameters are the custom values that can be passed via url to the framework views.
	 * They are encoded this way: http://.../locale/viewname/parameter1/parameter2/parameter3/parameter4/...
	 *
	 * @param int $index The numeric index for the requested parameter
	 * @param bool $removeHtmlTags To prevent HTML injection attacks, all html and php tags are removed from the parameter values.
	 *        If we specifically need this tags to be preserved, we can set this flag to false. Normally not necessary
	 *
	 * @return string The requested parameter value
	 */
	public function getParam(int $index, bool $removeHtmlTags = true){

	    if($index < 1){

	        throw new UnexpectedValueException('Invalid parameter index: '.$index);
	    }

	    if($index >= $this->_URLEnabledParameters){

	        throw new UnexpectedValueException('Disabled parameter index '.$index.' requested');
	    }

	    return $removeHtmlTags ? strip_tags($this->_URIElements[$index]) : $this->_URIElements[$index];
	}


	/**
	 * Perform a 301 redirect (permanently moved) to the specified url.
	 */
	private function _redirect301($url){

	    // TODO - should this be moved to turbocommons?
	    header('location:/'.$url, true, 301);
	    die();
	}
}

?>