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

use Throwable;
use UnexpectedValueException;
use stdClass;
use org\turbocommons\src\main\php\managers\LocalizationManager;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbodepot\src\main\php\managers\DepotManager;
use org\turbosite\src\main\php\model\UrlParamsBase;
use org\turbosite\src\main\php\model\WebServiceError;
use org\turbosite\src\main\php\model\WebViewSetup;
use org\turbodepot\src\main\php\managers\MarkDownManager;
use org\turbodepot\src\main\php\managers\MarkDownBlogManager;


/**
 * The global application instance where all the methods to interact with the website are found
 */
class WebSiteManager extends UrlParamsBase{


    /**
     * Title to show on the html metadata (recommended lenght is 60 characters).
     * Very important for SEO purposes, so an exception will happen if empty.
     */
    public $metaTitle = '';


    /**
     * Description to show on the html metadata (recommended lenght is 150 characters).
     * Very important for SEO purposes, so an exception will happen if empty.
     */
    public $metaDescription = '';


    /**
     * Stores the generated hash string that is used to prevent browser from caching static resources.
     * This value is dynamically created when project is built and added as a property named "cacheHash" to turbosite setup.
     */
    private $_cacheHash = '';


    /**
     * Stores the filesystem location for the index of the project (the point where src/main folder points),
     * to be used when loading other files or resources
     */
    private $_mainPath = '';


    /**
     * Stores the filesystem location for the views cache
     */
    private $_viewsCachePath = '';


    /**
     * Contains the name for the view that is configured at turbosite.json to be used as the single parameter view.
     * This view is automatically loaded when a single root parameter is specified at the url
     */
    private $_singleParameterView = '';


    /**
     * Contains the name for the view that is used as home page
     */
    private $_homeView = '';


    /**
     * If the current document is a view, the name is stored here
     */
    private $_currentViewName = '';


    /**
     * Instance for storage interaction
     * @var DepotManager
     */
    private $_depotManager = null;


    /**
     * Instance that manages the text translations
     */
    private $_localizationManager = null;


    /**
     * @see WebSiteManager::getPrimaryLanguage
     */
    private $_primaryLanguage = '';


    /**
     * If the website is not located at the root of the host, this property contains
     * the url fragment that points to the website root.
     *
     * Note that this fragment must be formatted so it does not start nor end with /.
     * If the site is placed at the root of the domain, we will leave this property as an empty string
     */
    private $_baseURL = '';


    /**
     * Stores the list of required JS cdns as an array of stdclass instances (check turbosite.json docs for more info)
     */
    private $_globalCDNS = [];


    /**
     * Stores the list of defined global constants that can be shared between the site php scripts and javascript,
     * so values that are defined at the php layer can be read at the JS layer
     */
    private $_globalConstants = [];


    /**
     * Stores the webservices setup (check turbosite.json docs for more info)
     */
    private $_webServicesSetup = null;


    /**
     * Stores a copy of all the setup files data (turbosite.json, turbodepot.json, etc...) that has been loaded by this class.
     * This is used at initialization and all the relevant values are stored on class properties for an easier access, but we will
     * still have the full setup files data copy here if required.
     *
     * The data is stored as an associative array where each key is the setup file name (like turbosite.json) and each value is an
     * \stdClass instance with all its data parsed from the file json content.
     */
    private $_setupFilesData = [];


    /**
     * Contains all the singleton global instances. In php we must do it this way to avoid singletons from returning wrong class objects.
     */
    private static $_instances = [];


    /**
     * Returns the global WebSiteManager Singleton instance that is used across all the project
     *
     * @return WebSiteManager The Singleton instance.
     */
    public static function getInstance(){

        // We initialize the error manager as early as possible. This is why we do it here.
        GlobalErrorManager::getInstance()->initialize();

        $class = get_called_class();

        if(!isset(self::$_instances[$class])) {

            self::$_instances[$class] = new $class();
        }

        return self::$_instances[$class];
    }


    /**
     * Get the depot manager instance that has been created as part of this class
     *
     * @return DepotManager
     */
    public function getDepotManager(){

        return $this->_depotManager;
    }


    /**
     * Get the first language (two digits) of the list of translation priorities, which effectively is the
     * language that is currently using the website
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
     * Get the view that is defined as home on turbosite setup
     */
    public function getHomeView(){

        return $this->_homeView;
    }


    /**
     * If the current document is a view, this method will give it's view name
     *
     * The view is  equivalent to the name of the folder which contains all the view files
     */
    public function getCurrentViewName(){

        return $this->_currentViewName;
    }


    /**
     * Get the view that is defined to handle single parameter urls
     */
    public function getSingleParameterView(){

        return $this->_singleParameterView;
    }


    /**
     * Get all the data from the specified setup file
     *
     * @param string $setupFileName The name for a setup file that is accessible by this class. For example turbosite.json or any other
     *        setup file that is located at the root of our project.
     *
     * @return \stdClass An object that contains all the setup data parsed from the requested json file. Each setup property can be accessed
     *         via the -> accessor
     */
    public function getSetup($setupFileName){

        if(!isset($this->_setupFilesData[$setupFileName])){

            throw new UnexpectedValueException('Specified setup data was not found: '.$setupFileName);
        }

        return $this->_setupFilesData[$setupFileName];
    }


    /**
     * Get the value from a previosuly defined internal global constant
     *
     * @param string $name The name of the global constant we want to read
     *
     * @return mixed The value of the requested constant
     */
    public function getGlobalConst($name){

        if(!isset($this->_globalConstants[$name])){

            throw new UnexpectedValueException('global constant does not exist: '.$name);
        }

        if($this->_globalConstants[$name]['availability'] === 'js'){

            throw new UnexpectedValueException("global constant '".$name."' can only be accessed by JS cause availability is set to 'js'");
        }

        return $this->_globalConstants[$name]['value'];
    }


    /**
     * Define the name, value and availability for an internal global constant.
     *
     * This is an easy way to share data between different php files without having to define dirty global php variables directly on the
     * code.
     *
     * The constant value can also be sent to the JS layer, so it will be available for javascript code.
     *
     * @param string $name The name of the constant we want to create
     * @param mixed $value The value for the constant
     * @param string $availability If set to 'php', the constant will be available only on php. If set to 'js', it will be available only
     *        on javascript. If set to 'both', it will be available on both php and javascript layers.
     *
     * @return mixed The constant value
     */
    public function setGlobalConst($name, $value, $availability = 'php'){

        if($name === null || strlen($name) < 1 || !is_string($name)){

            throw new UnexpectedValueException('Constant name is not valid');
        }

        if(isset($this->_globalConstants[$name])){

            throw new UnexpectedValueException('Constant name "'.$name.'" is already defined');
        }

        if($availability !== 'php' && $availability !== 'js' && $availability !== 'both'){

            throw new UnexpectedValueException('availability invalid value: "'.$availability.'" ');
        }

        // Store the var value to the class
        $this->_globalConstants[$name] = [];
        $this->_globalConstants[$name]['value'] = $value;
        $this->_globalConstants[$name]['availability'] = $availability;

        return $value;
    }


    /**
     * Generates all the output that is expected for the current URL
     * This method is normally called by the index entry point file and doesn't need to be called again by us.
     *
     * @param string $indexFilePath The filesystem path to the project index.php file (src/main/index.php)
     * @param array $setupFilesData An associative array where each key is the name of a setup file (like turbosite.json) and each
     *        value contains the setup file as a decoded json object. This is normally injected on the index.php file by the turbobuilder
     *        application when the project is built
     */
    public function generateContent(string $indexFilePath, array $setupFilesData = []){

        $this->_mainPath = StringUtils::formatPath(StringUtils::getPath($indexFilePath));
        $this->_viewsCachePath = $this->_mainPath.DIRECTORY_SEPARATOR.'___views_cache___';

        // Validate the setup files data and load any missing ones
        foreach ($setupFilesData as $key => $value) {

            if(!property_exists($value, '$schema')){

                die('Invalid setup data specified for '.$key.' on setupFilesData');
            }
        }

        $this->_setupFilesData = $setupFilesData;

        $this->_initObjectsAndLoadData();

        $this->_sanitizeUrl();

        $this->_generateURIOutput();
    }


    /**
     * Initialize all the project global objects and load setup data
     */
    private function _initObjectsAndLoadData(){

        $this->_localizationManager = new LocalizationManager();
        $this->_depotManager = new DepotManager($this->_setupFilesData['turbodepot.json']);

        $turboSiteSetup = $this->_setupFilesData['turbosite.json'];

        GlobalErrorManager::getInstance()->depotManager = $this->_depotManager;
        GlobalErrorManager::getInstance()->exceptionsToBrowser = $turboSiteSetup->errorSetup->exceptionsToBrowser;
        GlobalErrorManager::getInstance()->exceptionsToLog = $turboSiteSetup->errorSetup->exceptionsToLog;
        GlobalErrorManager::getInstance()->exceptionsToMail = $turboSiteSetup->errorSetup->exceptionsToMail;
        GlobalErrorManager::getInstance()->warningsToBrowser = $turboSiteSetup->errorSetup->warningsToBrowser;
        GlobalErrorManager::getInstance()->warningsToLog = $turboSiteSetup->errorSetup->warningsToLog;
        GlobalErrorManager::getInstance()->warningsToMail = $turboSiteSetup->errorSetup->warningsToMail;
        GlobalErrorManager::getInstance()->tooMuchTimeWarning = $turboSiteSetup->errorSetup->tooMuchTimeWarning;
        GlobalErrorManager::getInstance()->tooMuchMemoryWarning = $turboSiteSetup->errorSetup->tooMuchMemoryWarning;

        if(GlobalErrorManager::getInstance()->exceptionsToLog !== ''){

            $this->_depotManager->getLogsManager()->blankLinesBetweenLogs = 2;
        }

        $this->_cacheHash = $turboSiteSetup->cacheHash;
        $this->_homeView = $turboSiteSetup->homeView;
        $this->_singleParameterView = $turboSiteSetup->singleParameterView;
        $this->_baseURL = StringUtils::formatPath($turboSiteSetup->baseURL, '/');
        $this->_globalCDNS = $turboSiteSetup->globalCDNS;
        $this->_webServicesSetup = $turboSiteSetup->webServices;

        // Load all the configured resourcebundle paths
        $locations = array_map(function ($location) {

            return ['label' => $location->label,
                    'path' => StringUtils::formatPath($this->_mainPath.'/'.$location->path),
                    'bundles' => $location->bundles];

        }, $turboSiteSetup->translationLocations);

        $this->_localizationManager->initialize($this->_depotManager->getFilesManager(), $turboSiteSetup->locales, $locations, function($errors){

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

        $redirectTo = $this->_fullURL;

        // 301 Redirect to remove any possible query string.
        // Standard says that the first question mark in an url is the query string sepparator, and all the rest
        // are treated as literal question mark characters. So we cut the url by the first ? index found.
        if(strpos($redirectTo, '?') !== false){

            $redirectTo = substr($redirectTo, 0, strpos($redirectTo, '?'));
        }

        // 301 Redirect to home view if current URI is empty or a 2 digits existing locale plus the home view name
        if(StringUtils::isEmpty($this->_URI) || $this->_URI === $this->_baseURL ||
           (count($this->_URIElements) >= 2 &&
            strlen($this->_URIElements[0]) === 2 &&
            in_array($this->_URIElements[0], $this->_localizationManager->languages()) &&
            strtolower($this->_URIElements[1]) === strtolower($this->_homeView))){

            $redirectTo = $this->getUrl($this->_primaryLanguage, true);
        }

        // Remove any trailing slash from the url
        if(substr($redirectTo, -1) === '/'){

            $redirectTo = substr_replace($redirectTo, '', strlen($redirectTo) - 1, 1);
        }

        // Move from http to https if necessary
        if(strpos(strtolower($redirectTo), 'http:') === 0){

            $redirectTo = substr_replace($redirectTo, 'https:', 0, 5);
        }

        // Redirect the www version to NO www
        if(strpos(strtolower($redirectTo), 'https://www.') === 0){

            $redirectTo = substr_replace($redirectTo, 'https://', 0, 12);
        }

        // Redirect to remove duplicate / characters
        if(strpos(substr($redirectTo, 8), '//') !== false){

            $redirectTo = 'https://'.preg_replace('/\/+/', '/', substr($redirectTo, 8));
        }

        // Check if a redirect must be performed
        if($redirectTo !== $this->_fullURL){

            $this->redirect301($redirectTo);
        }
    }


    /**
     * Chech which content must be output based on the current URI
     */
    private function _generateURIOutput(){

        // Php files execution is not allowed
        if(mb_strtolower(StringUtils::getPathExtension($this->_URI)) !== 'php'){

            // Check if the URI represents a service, output its result and end. (all webservice uris must start with api/)
            if($this->_URIElements[0] === 'api'){

                echo $this->runCurrentURLWebService();
                die();
            }

            // Check if the URI represents the home or single parameter view
            if(count($this->_URIElements) === 1){

                if($this->_primaryLanguage === $this->_URIElements[0]){

                    $this->_currentViewName = $this->_homeView;
                }

                if($this->_singleParameterView !== '' && strlen($this->_URIElements[0]) > 2){

                    $this->_currentViewName = $this->_singleParameterView;
                }
            }

            // Check if the URI represents a full view with N parameters
            if(count($this->_URIElements) > 1 &&
                $this->_primaryLanguage === $this->_URIElements[0] &&
                is_file('view/views/'.$this->_URIElements[1].'/'.$this->_URIElements[1].'.php')){

                    $this->_currentViewName = $this->_URIElements[1];
            }

            if($this->_currentViewName !== ''){

                $this->_browserManager->setCookie('turbosite_locale', $this->_localizationManager->primaryLocale(), 365);
                include('view/views/'.$this->_currentViewName.'/'.$this->_currentViewName.'.php');
                die();
            }
        }

        // Reaching here means no match was found for the current URI, so 404 and die
        $this->show404Error();
    }


    /**
     * Resolve the provided relative project path into a full file system path that can be correctly reached via file system.
     *
     * Note: This is not an URL path but a File system path, based on the current project main folder
     *
     * @param string $path A path relative to the project src/main folder
     *
     * @return string A full file system path that is generated from the provided relative one
     */
    public function getPath(string $path){

        return StringUtils::formatPath($this->_mainPath.DIRECTORY_SEPARATOR.$path);
    }


    /**
     * Gives the filesystem location to the src/main/resources folder
     *
     * @see WebSiteManager::getPath()
     *
     * @return string
     */
    public function getPathToResources(){

        return $this->_mainPath.DIRECTORY_SEPARATOR.'resources';
    }


    /**
     * Declares the current document as a view, initializes its structure and checks all possible restrictions.
     *
     * All view urls must obey the following format: https://host/2-digit-language/view-name/param0/param1/param2/...
     *
     * The only exceptions are:<br>
     *     - The home view which follows the format: https://host/2-digit-language<br>
     *     - The single parameter view which must be initialized via the initializeAsSingleParameterView() method and follows the
     *     format: https://host/parameter<br>
     *
     * Note that any extra parameters on the url which are not enabled will be discarted and removed with a 301 redirect. Also
     * GET url parameters like ?param1=v1&param2=v2.. will be ignored and removed from the url.
     *
     * @see WebViewSetup
     *
     * @param WebViewSetup $setup The setup parameters that must be aplied to the view. If not specified, all the defaults will be used
     *
     * @return void
     */
    public function initializeAsView(WebViewSetup $setup = null){

        if($setup === null){

            $setup = new WebViewSetup();
        }

        // Defines the index where the current url parameters start to be view parameters
        $firstViewParamOffset = $this->_currentViewName === $this->_homeView ? 1 : 2;

        $redirectRequired = false;
        $this->_setReceivedParamsFromUrl($firstViewParamOffset);
        $this->_setEnabledUrlParams($setup->enabledUrlParams);

        try {

            $redirectRequired = $this->_processUrlParams();

        } catch (UnexpectedValueException $e) {

            switch ($e->getCode()) {

                case 404:
                    $this->show404Error();
                    break;

                case 301:
                    $redirectRequired = true;
                    break;

                default:
                    throw $e;
            }
        }

        if($redirectRequired){

            for ($i = $firstViewParamOffset - 1; $i >= 0; $i--) {

                array_unshift($this->_receivedUrlParams, $this->_URIElements[$i]);
            }

            $this->redirect301($this->getUrl(implode('/', $this->_receivedUrlParams), true));
        }

        // Check if a method to obtain the forced parameters needs to be executed and redirect if necessary
        if($setup->forcedParametersCallback !== null){

            $forcedParameters = ($setup->forcedParametersCallback)();
            $forcedParametersCount = count($forcedParameters);

            if($forcedParametersCount !== count($setup->enabledUrlParams)){

                throw new UnexpectedValueException('forcedParametersCallback result must have the same length as enabledUrlParams ('.print_r($setup->enabledUrlParams, true).')');
            }

            for ($i = 0; $i < $forcedParametersCount; $i++) {

                if(!StringUtils::isEmpty($forcedParameters[$i]) &&
                    $this->_URIElements[$firstViewParamOffset + $i] !== $forcedParameters[$i]){

                    $redirectRequired = true;
                    $this->_URIElements[$firstViewParamOffset + $i] = $forcedParameters[$i];
                }
            }

            if($redirectRequired){

                $this->redirect301($this->getUrl(implode('/', $this->_URIElements), true));
            }
        }

        if($setup->cacheLifeTime >= 0){

            // TODO - implement cache life time non infinite values. Currently only the 0 value (infinite lifetime) is working. Any other positive values
            // are treated the same as 0, infinite lifetime.

            ob_start();

            register_shutdown_function(function() {

                $this->_depotManager->getFilesManager()->saveFile(
                    $this->_viewsCachePath.DIRECTORY_SEPARATOR.hash('md5', $_SERVER[REQUEST_URI]), ob_get_contents(), false, true);

                ob_end_flush();
            });
        }
    }


    /**
     * Declares the current document as a single parameter view, which is a special type of view that only accepts a single URL parameter, initializes its
     * structure and checks all possible restrictions.
     *
     * @see WebSiteManager::initializeAsView
     *
     * @param string $language As the single parameter view does not accept language parameter, we must define which language it is using by setting it here.
     * @param string|array $allowedURLParameterValues Array with a list of accepted values for the only one URL parameter that is accepted by this view. If the array is empty,
     *        any value will be accepted by the parameter view.
     * @param string $redirectToClosest if set to true, when the received single parameter is not found on the list of allowed parameters, a redirect to
     *        the most similar one will be performed
     *
     * @return void
     */
    public function initializeAsSingleParameterView($language, $allowedURLParameterValues = [], $redirectToClosest = true){

        if($this->_currentViewName !== $this->_singleParameterView){

            throw new UnexpectedValueException('Trying to initialize a view called <'.$this->_currentViewName.'> as single param view, but <'.$this->_singleParameterView.'> is the one configured at turbosite.json');
        }

        if(!in_array($language, $this->_localizationManager->languages())){

            throw new UnexpectedValueException('Invalid language specified <'.$language.'> for single parameter view');
        }

        $this->_setEnabledUrlParams(1);

        if($this->_setReceivedParamsFromUrl(0) !== 1){

            $this->show404Error();
        }

        $this->_processUrlParams();

        if($allowedURLParameterValues !== [] && !in_array($this->getUrlParam(), $allowedURLParameterValues)){

            if($redirectToClosest){

                $this->redirect301($this->getUrl(StringUtils::findMostSimilarString($this->getUrlParam(), $allowedURLParameterValues), true));

            }else{

                $this->show404Error();
            }
        }

        $this->_primaryLanguage = $language;
        $this->_localizationManager->setPrimaryLanguage($this->_primaryLanguage);
    }


    /**
     * Declares the current document as a view that shows the contents of a markdown blog post.
     *
     * Under the hood, this method simply performs all the necessary steps to initialize the current document as a standard view, but also load
     * the blog data, setup the url parameters and any other operation that may be necessary.
     *
     * The blogs language will be automatically retrieved from the primary site language
     *
     * @see WebSiteManager::initializeAsView
     * @see WebViewSetup::$cacheLifeTime
     *
     * @param array $options An associative array that will define how the blog post view is initialized. Following array key values are accepted:<br>
     *              - blogRootPath (mandatory): The filesystem path to the root folder where the markdown blog structure of files is stored.<br>
     *              - latestPosts (optional, default 0): The number of latest posts to load. If we need to show data or links regarding other recent blog posts, we can obtain it by requesting how many of them we want to load<br>
     *              - cacheLifeTime (optional, default -1): Exactly the same behaviour as WebViewSetup::$cacheLifeTime
     *
     * @return \stdClass An object with the following properties that we can use:<br>
     *         - currentPost: A fully initialized MarkDownBlogPostObject instance with the data for the blog post that is loaded by this view<br>
     *         - latestPosts: An array of fully initialized MarkDownBlogPostObject instances for the N newest blog posts. Only available if latestPosts is specified<br>
     *         - markDownBlogManager: A fully initialized MarkDownBlogManager instance that can be used to operate with the stored blog data
     */
    public function initializeAsViewMarkDownBlogPost(array $options){

        $blogManager = new MarkDownBlogManager($options['blogRootPath']);

        $latestPosts = $blogManager->getLatestPosts($this->getPrimaryLanguage(),
            (isset($options['latestPosts']) && $options['latestPosts'] > 0) ? $options['latestPosts'] : 1);

        if(empty($latestPosts)){

            throw new UnexpectedValueException('At least one blog post must exist to initialize a ViewMarkDownBlogPost');
        }

        $webViewSetup = new WebViewSetup();

        if(isset($options['cacheLifeTime']) && $options['cacheLifeTime'] >= 0){

            $webViewSetup->cacheLifeTime = $options['cacheLifeTime'];
        }

        $webViewSetup->enabledUrlParams = [
            [WebSiteManager::NOT_TYPED, WebSiteManager::NOT_RESTRICTED, $latestPosts[0]->date],
            [WebSiteManager::NOT_TYPED, WebSiteManager::NOT_RESTRICTED, $latestPosts[0]->keywords]
        ];

        $currentPost = null;

        $webViewSetup->forcedParametersCallback = function () use ($blogManager, &$currentPost) {

            try {

                $currentPost = $blogManager->getPost($this->getUrlParam(0), $this->getPrimaryLanguage(), $this->getUrlParam(1), 0);

                return [$currentPost->date, $currentPost->keywords];

            } catch (Throwable $e) {

                $this->show404Error();
            }
        };

        $this->initializeAsView($webViewSetup);

        $this->metaTitle = $currentPost->metaTitle;
        $this->metaDescription = $currentPost->metaDescription;

        $result = new stdClass();

        $result->currentPost = $currentPost;

        $result->latestPosts = [];

        if(isset($options['latestPosts']) && $options['latestPosts'] > 0){

            $result->latestPosts = $latestPosts;
        }

        $result->markDownBlogManager = $blogManager;

        return $result;
    }


    /**
     * TODO - Implement a partial cache feature
     */
    public function cacheSectionBegin($tag, $timeToLive = 0){

        // TODO - Won't be compatible with full page cache when is set with cacheLifeTime (an exception must happen)

    }


    /**
     * TODO - Implement a partial cache feature
     */
    public function cacheSectionEnd($tag){

        // TODO - Won't be compatible with full page cache when is set with cacheLifeTime (an exception must happen)

    }


    /**
     * Adds extra bundles to the currently loaded translation data.
     *
     * Note that no bundles are loaded by default other than the ones that may be specified at the translationLocations section
     * on the turbosite.json file
     *
     * @param array $bundles List of bundles to load from the specified location
     * @param string $location The label for an already defined location. The extra bundles translation data will be added to the already loaded ones. If not defined, the current active location will be used.
     *
     * @return void
     */
    public function loadBundles(array $bundles, string $location = ''){

        $this->_localizationManager->loadBundles($bundles, $location);
    }


    // TODO - this should include all component parts inline: css, php and js
    public function includeComponent(string $componentPath){

        // If the component is global, skip css and js cause they will be already loaded
        // JS and css of the components must be added inline instead of globally at the view js or css files!!

        require $this->_mainPath.DIRECTORY_SEPARATOR.$componentPath.'.php';
    }


    /**
     * Get the value for an url parameter, given its parameter index number.
     * If the parameter index is valid, but no value has been passed into the url, it will return an empty string.
     * URL parameters are the custom values that can be passed via url to the framework views.
     * They are encoded this way: http://.../locale/viewname/parameter0/parameter1/parameter2/parameter3/...
     *
     * @param int $index The numeric index for the requested parameter. Invalid index value will throw an exception
     *
     * @return string The requested parameter value
     */
    public function getUrlParam(int $index = 0){

        if($this->_currentViewName === $this->_singleParameterView){

            if($index > 0){

                throw new UnexpectedValueException('Single parameter view accepts only one parameter');
            }

            return $this->_URIElements[0];
        }

        return parent::getUrlParam($index);
    }


    /**
     * Get the translated text for the provided key and options with the currently defined locale
     *
     * @param string $key The key we want to read from the specified resource bundle and path
     * @param array|string $options If a string is provided, the value will be used as the bundle where key
     *        must be found. If an associative array is provided, the following keys can be defined:
     *        -bundle: To define which bundle to look for the provided key
     *        -location: To define which location to look for the provided key and bundle
     *        -toReplace: A string or array of strings with the replacements for the translated text wildcards (see LocalizationManager::get for more info).
     *        An example of complex options : ['bundle' => 'footer', 'location' => 'resources', 'toReplace' => ['replace1', 'replace2']]
     *
     * @see LocalizationManager::get
     *
     * @return string The translated text with all the options applied
     */
    public function getText(string $key, $options = ''){

        if(is_string($options)){

            return $this->_localizationManager->get($key, $options);

        }else{

            return $this->_localizationManager->get($key,
                isset($options['bundle']) ? $options['bundle'] : '',
                isset($options['location']) ? $options['location'] : '',
                isset($options['toReplace']) ? (string)$options['toReplace'] : []);
        }
    }


    /**
     * @see WebSiteManager::getText
     * @see LocalizationManager::get
     */
    public function echoText(string $key, $options = ''){

        echo $this->getText($key, $options);
    }


    /**
     * @see WebSiteManager::echoUrl
     *
     * @return string
     */
    public function getUrl($path = '', $fullUrl = false){

        // Sanitize the received path
        $formattedPath = StringUtils::formatPath($path, '/');

        // If we receive a full absolute url as the path, we will simply return it
        if(substr(strtolower($formattedPath), 0, 4) == 'http'){

            return $formattedPath;
        }

        $formattedPath = StringUtils::formatPath('/'.$this->_baseURL.'/'.$formattedPath, '/');

        $formattedPath = $formattedPath === '' ? '/' : $formattedPath;

        return ($fullUrl ? 'https://'.$_SERVER['HTTP_HOST'] : '').$formattedPath;
    }


    /**
     * Obtain a valid url based on the current website root.
     *
     * @param string $path A path relative to the root of the published site. Regarding the project folders, we should consider src/main as the root
     * @param boolean $fullUrl Set it to true to get the full url including https and the current domain.
     *
     * @return string the generated relative or full url
     */
    public function echoUrl($path = '', $fullUrl = false){

        echo $this->getUrl($path, $fullUrl);
    }


    /**
     * @see WebSiteManager::echoUrlToView
     *
     * @return string
     */
    public function getUrlToView(string $view, $parameters = '', bool $fullUrl = false){

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

        return htmlspecialchars($this->getUrl(implode('/', array_merge($result, $parameters)), $fullUrl));
    }


    /**
     * Gives the url that points to the specified view, using the current site locale and the specified parameters
     *
     * @param string $view The name of the view. For example: home
     * @param string|array $parameters The list of parameters to pass to the view url that will be generated. If a single parameter is sent, it can be a string.
     *        If more than one are sent, it must be an array.
     * @param boolean $fullUrl True to get the full absolute url (http://...) or false to get it relative to the current domain
     *
     * @return void
     */
    public function echoUrlToView($view, $parameters = '', $fullUrl = false){

        echo $this->getUrlToView($view, $parameters, $fullUrl);
    }


    /**
     * @see WebSiteManager::echoUrlToChangeLocale
     *
     * @return string
     */
    public function getUrlToChangeLocale($locale, $fullUrl = false){

        $newURI = ltrim($_SERVER['REQUEST_URI'], '/');

        // Remove the baseurl/ from the beginning if it exists
        if (substr($newURI, 0, strlen($this->_baseURL.'/')) == $this->_baseURL.'/') {

            $newURI = ltrim(substr($newURI, strlen($this->_baseURL.'/')), '/');
        }

        $language = substr($locale, 0, 2);

        if(substr($newURI, 0, 2) === $this->_primaryLanguage){

            $newURI = StringUtils::replace($newURI, $this->_primaryLanguage, $language, 1);
        }

        return $this->getUrl($newURI, $fullUrl);
    }


    /**
     * Gives the url that will allow us to change the locale for the current document URI
     *
     * @param string $locale The locale we want to set on the new url
     * @param boolean $fullUrl True to get the full absolute url (https://...) or false to get it relative to the current domain
     *
     * @return void
     */
    public function echoUrlToChangeLocale($locale, $fullUrl = false){

        echo $this->getUrlToChangeLocale($locale, $fullUrl);
    }


    /**
     * Output the html code that is generated by the provided markdown file
     *
     * @param string $markDownFilePath An Operating system absolute or relative path containing some markdown file
     *
     * @see MarkDownManager::toHtml
     *
     * @return void
     */
    public function echoHtmlFromMarkDownFile($markDownFilePath){

        echo $this->_depotManager->getMarkDownManager()->toHtml($this->_depotManager->getFilesManager()->readFile($markDownFilePath));
    }


    /**
     * Output the html code that must be placed inside the head tag for the current document
     * Charset, title, description, viewport and favicon tags are generated by this method.
     *
     * @throws UnexpectedValueException
     */
    public function echoHtmlHead(){

        if(StringUtils::isEmpty($this->metaTitle.$this->metaDescription)){

            throw new UnexpectedValueException('metaTitle or metaDescription are empty');
        }

        echo '<meta charset="utf-8">'."\n";
        echo '<title>'.$this->metaTitle.'</title>'."\n";
        echo '<meta name="description" content="'.$this->metaDescription.'">'."\n";
        echo '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">'."\n";

        // Favicons
        echo '<link rel="icon" type="image/png" sizes="16x16" href="'.$this->getUrl('/16x16-'.$this->_cacheHash.'.png').'">'."\n";
        echo '<link rel="icon" type="image/png" sizes="32x32" href="'.$this->getUrl('/32x32-'.$this->_cacheHash.'.png').'">'."\n";
        echo '<link rel="icon" type="image/png" sizes="96x96" href="'.$this->getUrl('/96x96-'.$this->_cacheHash.'.png').'">'."\n";
        echo '<link rel="icon" type="image/png" sizes="128x128" href="'.$this->getUrl('/128x128-'.$this->_cacheHash.'.png').'">'."\n";
        echo '<link rel="icon" type="image/png" sizes="196x196" href="'.$this->getUrl('/196x196-'.$this->_cacheHash.'.png').'">'."\n";

        // Global css file
        echo '<link rel="stylesheet" href="'.$this->getUrl('glob-'.$this->_cacheHash.'.css').'">'."\n";

        // Generate the view css if exists
        if(is_file($this->_mainPath.DIRECTORY_SEPARATOR.'view-view-views-'.$this->_currentViewName.'-'.$this->_cacheHash.'.css')){

            echo '<link rel="stylesheet" href="'.$this->getUrl('view-view-views-'.$this->_currentViewName.'-'.$this->_cacheHash.'.css').'">'."\n";
        }
    }


    /**
     * Write the html code to load the page js scripts and code.
     *
     * This call must always be performed at the end of the document, just before the closing body tag
     */
    public function echoHtmlJavaScriptTags(){

        // Generate the code to load CDN libs
        foreach ($this->_globalCDNS as $cdn) {

            echo '<script src="'.$cdn->url.'" crossorigin="anonymous" defer></script>'."\n";

            if(!StringUtils::isEmpty($cdn->fallbackResource)){

                $url = $this->getUrl($cdn->fallbackResource);

                echo "<script>".$cdn->fallbackVerify." || document.write('<script src=\"".$url."\"><\/script>')</script>\n";
            }
        }

        // Generate all the defined global JS constants
        if(count($this->_globalConstants) > 0){

            echo "<script>\n";

            foreach($this->_globalConstants as $name => $var){

                if($var['availability'] === 'js' || $var['availability'] === 'both'){

                    // Correctly format the var so it can be generated in js
                    $varFormatted = is_string($var['value']) ? '"'.str_replace(['"', "\r\n", "\n", "\r"], ['\"', '\r\n', '\n', '\r'], $var['value']).'"' : $var['value'];

                    echo 'const '.$name.' = '.$varFormatted.";\n";
                }
            }

            echo "</script>\n";
        }

        // Generate the global js script
        echo '<script src="'.$this->getUrl('glob-'.$this->_cacheHash.'.js').'" defer></script>';

        // Generate the view js if exists
        if(is_file($this->_mainPath.DIRECTORY_SEPARATOR.'view-view-views-'.$this->_currentViewName.'-'.$this->_cacheHash.'.js')){

            echo "\n<script src=\"".$this->getUrl('view-view-views-'.$this->_currentViewName.'-'.$this->_cacheHash.'.js').'" defer></script>';
        }
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
    public function redirect301($url){

        // TODO - should this be moved to turbocommons?
        header('location:'.$url, true, 301);
        die();
    }


    /**
     * Show a 404 error, which means that the webpage you were trying to reach could not be found on the server.
     * It is a Client-side Error which means that either the page has been removed or moved and the URL was not changed accordingly,
     * or that you typed in the URL incorrectly.
     *
     * Note that this method uses headers so no output must have been generated when calling it or it won't work.
     */
    public function show404Error(){

        // TODO - should this be moved to turbocommons?
        http_response_code(404);
        include('error-404.php');
        die();
    }


    /**
     * This method executes the API web service that is defined by the current URL
     */
    private function runCurrentURLWebService(){

        // Loop all the api definitions to find the one that matches the current url
        foreach ($this->_webServicesSetup->api as $apiDefinition) {

            $apiUri = StringUtils::formatPath($apiDefinition->uri, '/').'/';

            if (strpos($this->_fullURL, $apiUri) !== false) {

                $nameSpace = $apiDefinition->namespace."\\";
                $explodedUrlParts = explode('/', explode($apiUri, $this->_fullURL, 2)[1]);

                foreach ($explodedUrlParts as $explodedUrlPart) {

                    $serviceClass = $nameSpace.StringUtils::formatCase($explodedUrlPart, StringUtils::FORMAT_UPPER_CAMEL_CASE).'Service';

                    if(class_exists($serviceClass)){

                        try {

                            if($this->_webServicesSetup->crossOriginCORS === 'allow'){

                                header("Access-Control-Allow-Origin: *");
                                header("Access-Control-Allow-Credentials: true");
                                header('Access-Control-Allow-Methods: GET, POST');
                            }

                            $serviceClassInstance = new $serviceClass;

                            $serviceResult = $serviceClassInstance->run();

                            header('Content-Type: '.($serviceResult instanceof WebServiceError ?
                                'application/json' : $serviceClassInstance->contentType));

                            return $this->webServiceResultToString($serviceResult);

                        } catch (Throwable $e) {

                            // Log the error so it does not get lost for application logs
                            error_log($e);

                            header('Content-Type: application/json');

                            // We set 500 error code cause the exception is not handled by the webservice, and therefore we don't know what happened
                            return $this->webServiceResultToString(WebServiceError::createInstance(
                                500, 'Unhandled exception', $e->getMessage(), $e->getTraceAsString()), false);
                        }
                    }

                    $nameSpace .= $explodedUrlPart."\\";
                }
            }
        }

        $this->show404Error();
    }


    /**
     * Convert the result of a web service into a string that can be output to the browser
     *
     * @param mixed $result The result of a web service
     * @param boolean $handled Set it to false if the result is an exception that was not controlled by the service itself
     *
     * return string The textual value for the passed result
     */
    private function webServiceResultToString($result, $handled = true){

        if(is_string($result)){

            return $result;
        }

        if($result instanceof WebServiceError){

            http_response_code($result->code);

            // If exceptions to log are enabled, we will send the error information to the defined log
            if(GlobalErrorManager::getInstance()->exceptionsToLog !== ''){

                $this->_depotManager->getLogsManager()->write(
                    $result->title.'(code '.$result->code.")\n".$this->_browserManager->getCurrentUrl()."\n".$result->message."\n".$result->trace,
                    GlobalErrorManager::getInstance()->exceptionsToLog);
            }

            // Error trace and message will only be output if exceptions to browser are enabled
            if(!GlobalErrorManager::getInstance()->exceptionsToBrowser){

                unset($result->message);
                unset($result->trace);
            }

            // Error information will only be output if exceptionsToBrowser are enabled or is a handled service error
            return (GlobalErrorManager::getInstance()->exceptionsToBrowser || $handled) ? json_encode($result) : '';
        }

        if(is_bool($result) || is_array($result) || $result instanceof stdClass){

            return json_encode($result);
        }

        if(is_numeric($result)){

            return strval($result);
        }

        throw new UnexpectedValueException('The overriden WebService run() method must return a valid PHP basic type');
    }
}

?>