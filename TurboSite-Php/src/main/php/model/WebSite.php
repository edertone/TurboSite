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
 * The global application instance where all the framework methods are found
 */
class WebSite extends BaseSingletonClass{


    /**
     * Contains the title to show on the html metadata. This is very important for SEO purposes, so an exception will happen if
     * this value is empty
     */
    public $metaTitle = '';


    /**
     * Contains the description to show on the html metadata. This is very important for SEO purposes, so an exception will happen if
     * this value is empty. Note that meta description recommended lenght is 150 characters.
     */
    public $metaDescription = '';


    /**
     * Stores the generated hash string that is used to prevent browser from caching
     * static resources
     */
    private $_cacheHash = '';


    /**
     * Stores the filesystem location for the index of the site (the point where src/main folder points),
     * to be used when loading other files or resources
     */
    private $_mainPath = '';


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
	 * If the current document is a view, the name is stored here
	 */
	private $_currentView = '';


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
	 * Stores all the data for the components that are loaded on the current instance
	 */
	private $_loadedComponents = [];


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
	 * Get the view that is defined as home
	 */
	public function getHomeView(){

	    return $this->_homeView;
	}


	/**
	 * Get the view that is defined to handle single parameter urls
	 */
	public function getSingleParameterView(){

	    return $this->_singleParameterView;
	}


	/**
	 * Initialize the website structure and generate the html code for the current url
	 */
	public function initialize($rootPath){

	    $this->_mainPath = StringUtils::formatPath(StringUtils::replace($rootPath, StringUtils::getPathElement($rootPath), ''));
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

	    $this->_cacheHash = $setup->cacheHash;
	    $this->_homeView = $setup->homeView;
	    $this->_singleParameterView = $setup->singleParameterView;

	    // Load all the configured resourcebundle paths
	    $bundles = [];

	    foreach ($setup->resourceBundles as $bundle) {

	        $bundles[] = [
	            'path' => StringUtils::formatPath($this->_mainPath.'/'.$bundle->path),
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

	                $this->_301Redirect($this->_primaryLanguage);
	    }

	    // 301 Redirect to remove any possible query string.
	    // Standard says that the first question mark in an url is the query string sepparator, and all the rest
	    // are treated as literal question mark characters. So we cut the url by the first ? index found.
	    if(strpos($this->_fullURL, '?') !== false){

	        $this->_301Redirect($this->_URI);
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

	        // Check if the URI represents the home or single parameter view
	        if(count($this->_URIElements) === 1){

	            if($this->_primaryLanguage === $this->_URIElements[0]){

	                $this->_currentView = $this->_homeView;
	            }

	            if($this->_singleParameterView !== '' && strlen($this->_URIElements[0]) > 2){

	                $this->_currentView = $this->_singleParameterView;
	            }
	        }

	        // Check if the URI represents a full view with N parameters
	        if(count($this->_URIElements) > 1 &&
	           $this->_primaryLanguage === $this->_URIElements[0] &&
	           is_file('view/views/'.$this->_URIElements[1].'/'.$this->_URIElements[1].'.php')){

	           $this->_currentView = $this->_URIElements[1];
	        }

	        if($this->_currentView !== ''){

	            $this->_browserManager->setCookie('turbosite_locale', $this->_localizationManager->primaryLocale(), 365);
	            include('view/views/'.$this->_currentView.'/'.$this->_currentView.'.php');
	            die();
	        }
	    }

	    // Reaching here means no match was found for the current URI, so 404 and die
	    $this->_404Error();
	}


	/**
	 * TODO docs
	 */
	public function initializeView($enabledParams = 0, array $paramsDefault = [], array $paramsForce = []){

	    // If URI parameters exceed the enabled ones, a redirect to remove unaccepted params will be performed
	    if((count($this->_URIElements) - 2) > $enabledParams){

	        $redirectUrl = $this->_URIElements[0].'/'.$this->_URIElements[1];

	        for ($i = 2; $i < $enabledParams - 1; $i++) {

	            $redirectUrl += '/'.$this->_URIElements[$i];
	        }

	        $this->_301Redirect($redirectUrl);
	    }
	}


	/**
	* TODO docs
	*/
	public function initializeSingleParameterView($language, $acceptedParameters = []){

	    $this->_URLEnabledParameters = 1;

	    if($acceptedParameters !== '*' && !in_array($this->getParam(), $acceptedParameters)){

	        $this->_404Error();
	    }

	    if(!in_array($language, $this->_localizationManager->languages())){

	        throw new UnexpectedValueException('Invalid language specified <'.$language.'> for single parameter view');
	    }

	    $this->_primaryLanguage = $language;
	    $this->_localizationManager->setPrimaryLanguage($this->_primaryLanguage);
	}


	/**
	 * Adds extra bundles to the currently loaded translation data
	 */
	public function loadBundles(array $bundles){

	    $this->_localizationManager->loadBundles('resources/locales/$bundle/$bundle_$locale.properties', $bundles);
	}


	/**
	 * TODO
	 */
	public function loadComponents(array $componentsPaths){

	    foreach ($componentsPaths as $componentPath) {

	        $loadedComponent = ['id' => StringUtils::replace($componentPath, ['/', '\\'], '-')];

	        $this->_loadedComponents[] = $loadedComponent;
	    }
	}


	// TODO
	public function includeComponent(string $componentPath){

	    // TODO verificar component loaded

	    require $this->_mainPath.DIRECTORY_SEPARATOR.$componentPath.'.php';
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
	public function getParam(int $index = 0, bool $removeHtmlTags = true){

	    if($index < 0){

	        throw new UnexpectedValueException('Invalid parameter index: '.$index);
	    }

	    if($index >= $this->_URLEnabledParameters){

	        throw new UnexpectedValueException('Disabled parameter index '.$index.' requested');
	    }

	    return $removeHtmlTags ? strip_tags($this->_URIElements[$index]) : $this->_URIElements[$index];
	}


	/**
	 * TODO
	 */
    public function echoHeadHtml(){

	    if(StringUtils::isEmpty($this->metaTitle.$this->metaDescription)){

	        throw new UnexpectedValueException('metaTitle or metaDescription are empty');
	    }

	    echo '<meta charset="utf-8">'."\n";
	    echo '<meta http-equiv="x-ua-compatible" content="ie=edge">'."\n";
	    echo '<title>'.$this->metaTitle.'</title>'."\n";
	    echo '<meta name="description" content="'.$this->metaDescription.'">'."\n";
	    echo '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">'."\n";
	    echo '<link rel="manifest" href="/manifest.json">'."\n";
	    echo '<link rel="apple-touch-icon" href="/icon.png">'."\n";
	    // <!-- Place favicon.ico in the root directory -->
	    echo '<link rel="stylesheet" href="/glob-'.$this->_cacheHash.'.css">'."\n";

        // Generate the components css
        foreach ($this->_loadedComponents as $loadedComponent) {

            if(is_file($this->_mainPath.DIRECTORY_SEPARATOR.'comp-'.$loadedComponent['id'].'-'.$this->_cacheHash.'.css')){

                echo '<link rel="stylesheet" href="/comp-'.$loadedComponent['id'].'-'.$this->_cacheHash.'.css">'."\n";
            }
        }

        // Generate the view css if we are on a view
        if(is_file($this->_mainPath.DIRECTORY_SEPARATOR.'view-view-views-'.$this->_currentView.'-'.$this->_cacheHash.'.css')){

            echo '<link rel="stylesheet" href="/view-view-views-'.$this->_currentView.'-'.$this->_cacheHash.'.css">'."\n";
        }
	}


	/**
	 * TODO
	 */
	public function echoJavaScriptTags(){

	    // Generate the global js script
	    echo '<script src="/glob-'.$this->_cacheHash.'.js" defer></script>';
	}


	/**
	 * Get the translated text for the provided key and options
	 *
	 * @param string $key The key we want to read from the specified resource bundle and path
	 * @param array|string $options If a string is provided, the value will be used as the bundle where key
	 *        must be found. If an associative array is provided, the following keys can be defined:
	 *        -bundle: To define which bundle to look for the provided key
	 *        -wildcards: A string or an array of strings that will be replaced on the translated Text
	 *        -replace: A string or array of strings with the replacements for each of the provided wildcards
	 *        An example of complex options : ['bundle' => 'footer', 'wildcards' => '$N', 'replace' => $ws->getRunningTime()]
	 *
	 * @see LocalizationManager::get
	 *
	 * @return string The translated text with all the options applied
	 */
	public function getLoc(string $key, $options = ''){

	    if(is_string($options)){

	        $bundle = $options;

	    }else{

	        $bundle = isset($options['bundle']) ? $options['bundle'] : '';
	    }

	    $text = $this->_localizationManager->get($key, $bundle);

	    if(!is_string($options) && isset($options['wildcards'])){

	        $text = StringUtils::replace($text, $options['wildcards'], $options['replace']);
	    }

	    return $text;
	}


	/**
	 * @see WebSite::getLoc
	 * @see LocalizationManager::get
	 */
	public function echoLoc(string $key, $options = ''){

	   echo $this->getLoc($key, $options);
	}


	/**
	 * @see WebSite::echoUrl
	 *
	 * @return string
	 */
	public function getUrl($path = '', $absolute = false){

	    // Sanitize the received path
	    $formattedPath = StringUtils::formatPath($path, '/');

	    // If we receive a full absolute url as the path, we will simply return it
	    if(substr(strtolower($formattedPath), 0, 4) == 'http'){

	        return $formattedPath;
	    }

	    // Check if absolute url is required
	    if($absolute){

	        return $path;

	    }else{

	        return '/'.$path;
	    }
	}


	/**
	 * TODO
	 *
	 * @return string The generated url
	 */
	public function echoUrl($path = '', $absolute = false){

	    echo $this->getUrl($path, $absolute);
	}


	/**
	 * @see WebSite::echoUrlToView
	 *
	 * @return string
	 */
	public function getUrlToView(string $view, $parameters = '', bool $absolute = false){

	    if(is_string($parameters)){

	        $parameters = StringUtils::isEmpty($parameters) ? [] : [$parameters];
	    }

	    // The array that will store the URI parts
	    $result = [];
	    $view = str_replace('.php', '', $view);

	    // Check if we are getting the single parameter view
	    if($view === $this->_singleParameterView){

	        if(count($parameters) !== 1 || strlen($parameters[0]) < 3){

                throw new UnexpectedValueException('Single parameter view only allows one parameter with more than 2 digits');
	        }

        // Check if we are getting the home view url
	    }elseif ($view === $this->_homeView || StringUtils::isEmpty($view)){

	        if(count($parameters) !== 0){

	            throw new UnexpectedValueException('Home view does not allow parameters');
	        }

	        $result = [$this->_primaryLanguage];

	    }else{

	        $result = [$this->_primaryLanguage, $view];
	    }

	    // Add all the parameters to the url
	    $parameters = array_map(function ($p) {return rawurlencode($p);}, $parameters);

	    return htmlspecialchars($this->getUrl('', $absolute).implode('/', array_merge($result, $parameters)));
	}


	/**
	 * Gives the url that points to the specified view, using the current site locale and the specified parameters
	 *
	 * @param string $view The name of the view. For example: Home
	 * @param mixed $parameters The list of parameters to pass to the PHP call. If a single parameter is sent, it can be a string. If more than one will be passed, it must be an array.
	 * @param boolean $absolute True to get the full absolute url (http://...) or false to get it relative to the current domain
	 *
	 * @return void
	 */
	public function echoUrlToView($view, $parameters = '', $absolute = false){

	    echo $this->getUrlToView($view, $parameters, $absolute);
	}


	/**
	 * @see WebSite::echoUrlToChangeLocale
	 *
	 * @return string
	 */
	public function getUrlToChangeLocale($locale, $absolute = false){

	    $newURI = ltrim($_SERVER['REQUEST_URI'], '/');

	    $language = substr($locale, 0, 2);

	    if(substr($newURI, 0, 2) === $this->_primaryLanguage){

	        $newURI = StringUtils::replace($newURI, $this->_primaryLanguage, $language, 1);
	    }

	    return $this->getUrl($newURI, $absolute);
	}


	/**
	 * Gives the url that will allow us to change the locale for the current document URI
	 *
	 * @param string $locale The locale we want to set on the new url
	 * @param boolean $absolute True to get the full absolute url (https://...) or false to get it relative to the current domain
	 *
	 * @return void
	 */
	public function echoUrlToChangeLocale($locale, $absolute = false){

	    echo $this->getUrlToChangeLocale($locale, $absolute);
	}


	/**
	 * Get the time that's taken for the document to be generated since the initial page request.
	 *
	 * @return float the number of seconds (with 3 digit ms precision) since the Website object was instantiated.
	 *         For example 1.357 which means 1 second an 357 miliseconds
	 */
	public function getRunningTime(){

	    return round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 4);
	}


	/**
	 * Perform a 301 redirect (permanently moved) to the specified url.
	 */
	private function _301Redirect($url){

	    // TODO - should this be moved to turbocommons?
	    header('location:/'.$url, true, 301);
	    die();
	}


	/**
	 * Show a 404 error
	 */
	private function _404Error(){

	    http_response_code(404);
	    include('error-404.php');
	    die();
	}
}

?>