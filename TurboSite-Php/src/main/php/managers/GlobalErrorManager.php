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

use Exception;
use Throwable;
use org\turbocommons\src\main\php\model\BaseSingletonClass;
use org\turbosite\src\main\php\model\ProblemData;
use org\turbodepot\src\main\php\managers\DepotManager;
use org\turbocommons\src\main\php\utils\StringUtils;


/**
 * Used to encapsulate all the global error management.
 * It will give total control over the code exceptions, the way they are handled and notified.
 */
class GlobalErrorManager extends BaseSingletonClass{


    /**
     * If we want to save exceptions and warnings to a persistent log, we must pass here an initialized instance
     * of DepotManager, which will be used to store the information
     *
     * @var DepotManager
     */
    public $depotManager = null;


    /**
     * Flag that tells the class to show or hide the browser exceptions output.
     * This is normally set to false cause html errors will give lots of information to malicious users,
     * so setting it to true will generate an email warning notification if email error notifications are enabled
     */
    public $exceptionsToBrowser = true;


    /**
     * Enable or disable the error output to the specified log file. If set to empty '' string, errors won't be stored to log files.
     * If a file name or relative path is specified, any error that happens on the application will be sent to that file.
     */
    public $exceptionsToLog = '';


    /**
     * Enable or disable the email error notifications. If set to empty '' string, no mail notifications will happen.
     * If an email address is specified, any error that happens on the application will be sent to the specified address with all the detailed information.
     */
    public $exceptionsToMail = '';


    /**
     * Flag that tells the class to show or hide all the browser warnings output.
     */
    public $warningsToBrowser = true;


    /**
     * Enable or disable the log warning output. Works the same as $exceptionsToLog
     *
     * @see GlobalErrorManager::$exceptionsToLog
     */
    public $warningsToLog = '';


    /**
     * Enable or disable the email warning notifications. Works the same as $exceptionsToMail
     *
     * @see GlobalErrorManager::$exceptionsToMail
     */
    public $warningsToMail = '';


    /**
     * When running time on the current script exceeds the number of miliseconds defined here, a warning will be launched.
     * If set to zero, this feature will be disabled
     */
    public $tooMuchTimeWarning = 1000;


    /**
     * When allocated memory on the current script exceeds the number of bytes defined here, a warning will be launched.
     * If set to zero, this feature will be disabled
     */
    public $tooMuchMemoryWarning = 5000000;


    /**
     * Tells if the initialize() method has been called and therefore the php error management is being handled by this class
     */
    private $_initialized = false;


    /**
     * Array containing all the problems that have been found, to be processed at the end of the current script execution
     */
    private $_problemsFound = [];


    /**
     * Use this method to initialize the error management class.
     * The ErrorManager will not be doing anything till this method is called. Once intialized, the custom error handlers will take care of
     * all the exceptions and errors that happen.
     * This method should be called only once. Subsequent calls will do nothing.
     *
     * @return void
     */
    public function initialize(){

        if(!$this->_initialized){

            // Disable the native php browser errors output.
            // If the exceptionsToBrowser property is true, this class will take care of showing them via browser output.
            ini_set('display_errors', '0');

            // Initialize the handlers that will take care of the errors
            $this->_setWarningHandler();

            $this->_setExceptionHandler();

            $this->_setShutdownFunction();

            // Make sure display errors have been really disabled at php level
            $displayErrors = ini_get('display_errors');

            if($displayErrors === '1' || $displayErrors === 'On' || $displayErrors === 'true'){

                die('Could not deactivate php display_errors');
            }

            $this->_initialized = true;
        }
    }


    /**
     * Get the detailed backtrace to the current execution point.
     *
     * @return string The detailed execution trace to the point this method is called.
     */
    public function getBackTrace(){

        return (new Exception)->getTraceAsString();
    }


    /**
     * Set the error handler to manage non fatal php errors
     *
     * @return void
     */
    private function _setWarningHandler(){

        set_error_handler(function ($errorType, $errorMessage, $errorFile, $errorLine, $errorContext){

            $type = 'WARNING';

            switch($errorType){

                case E_WARNING:
                    $type = 'E_WARNING';
                    break;

                case E_NOTICE:
                    $type = 'E_NOTICE';
                    break;

                case E_USER_ERROR:
                    $type = 'E_USER_ERROR';
                    break;

                case E_USER_WARNING:
                    $type = 'E_USER_WARNING';
                    break;

                case E_USER_NOTICE:
                    $type = 'E_USER_NOTICE';
                    break;

                case E_RECOVERABLE_ERROR:
                    $type = 'E_RECOVERABLE_ERROR';
                    break;

                case E_DEPRECATED:
                    $type = 'E_DEPRECATED';
                    break;

                case E_USER_DEPRECATED:
                    $type = 'E_USER_DEPRECATED';
                    break;

                case E_ALL:
                    $type = 'E_ALL';
                    break;

                default:
            }

            $this->_problemsFound[] = $this->_createProblemData($type,
                $errorMessage,
                $errorFile,
                $errorLine);
        });
    }


    /**
     * Set a handler to manage fatal php errors
     *
     * @return void
     */
    private function _setExceptionHandler(){

        set_exception_handler(function (Throwable $error) {

            $this->_problemsFound[] = $this->_createProblemData('FATAL EXCEPTION',
                $error->getMessage(),
                $error->getFile(),
                $error->getLine());
        });
    }


    /**
     * Defer the processing of all the errors that have been captured on the current script
     * to the end of its execution
     *
     * @return void
     */
    private function _setShutdownFunction(){

        register_shutdown_function(function() {

            // If errors or warnings are configured to be sent to logs but no depot manager is available,
            // We will generate an error to warn about this problem
            if(($this->exceptionsToLog || $this->warningsToLog) &&
                $this->depotManager === null){

                    $this->_problemsFound[] = $this->_createProblemData('FATAL EXCEPTION', 'Errors or warnings are configured to be sent to logs but no depotmanager is provided');
            }

            // If too much memory value has been exceeded, we will generate a warning
            if($this->tooMuchMemoryWarning > 0 && memory_get_peak_usage() > $this->tooMuchMemoryWarning){

                $message  = "Too much memory used by script:\n";
                $message .= "The tooMuchMemoryWarning setup memory threshold is ".$this->tooMuchMemoryWarning.' bytes\n';
                $message .= "Script finished using ".memory_get_usage().' bytes\n';
                $message .= "Script memory peaked at ".memory_get_peak_usage().' bytes';

                $this->_problemsFound[] = $this->_createProblemData('E_WARNING', $message);
            }

            // If too much time value has been exceeded, we will generate a warning
            $runningTime = round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 4) * 1000;

            if($this->tooMuchTimeWarning > 0 && $runningTime > $this->tooMuchTimeWarning){

                $message  = "Too much time used by script:\n";
                $message .= "The tooMuchTimeWarning setup memory threshold is ".$this->tooMuchTimeWarning.' ms\n';
                $message .= "Script finished in ".$runningTime.' ms';

                $this->_problemsFound[] = $this->_createProblemData('E_WARNING', $message);
            }

            $problemsHtmlCode = '';
            $exceptionsLog = '';
            $warningsLog = '';

            foreach ($this->_problemsFound as $problem) {

                if(($this->exceptionsToBrowser && $problem->type === 'FATAL EXCEPTION') ||
                    ($this->warningsToBrowser && $problem->type !== 'FATAL EXCEPTION')){

                        $problemsHtmlCode .= '<p style="all: initial; color: #fff; margin-bottom: 15px; float: left"><b>PHP Problem: ';

                        $problemsHtmlCode .= $problem->type.'<br>'.htmlspecialchars($problem->message).'</b><br>';

                        $problemsHtmlCode .= $problem->fileName;

                        if(isset($problem->line) && $problem->line !== ''){

                            $problemsHtmlCode .= ' line '.$problem->line;
                        }

                        $problemsHtmlCode .= '<br>'.str_replace("\n", '<br>', $problem->trace);

                        $problemsHtmlCode .= '</p>';
                }

                // Generate the log text for this problem so it can be output to log file later (if enabled)
                $logText  = $problem->type.' '.$problem->message."\n";
                $logText .= 'IP: '.$_SERVER['REMOTE_ADDR']."\n";
                $logText .= $problem->fileName.' line '.$problem->line."\n";
                $logText .= $problem->trace."\n";
                $logText .= 'URL: '.$problem->fullUrl."\n";
                $logText .= 'URL referer: '.$problem->referer."\n";
                $logText .= 'Browser: '.$_SERVER['HTTP_USER_AGENT']."\n";
                $logText .= 'Used memory: '.$problem->usedMemory.' of '.ini_get('memory_limit')."\n";
                $logText .= 'GET params: '.$problem->getParams;
                $logText .= 'POST params: '.$problem->postParams;
                $logText .= 'Cookies: '.print_r($_COOKIE, true)."\n";

                if($problem->type === 'FATAL EXCEPTION'){

                    $exceptionsLog .= $logText;

                }else{

                    $warningsLog .= $logText;
                }
            }

            // Output the html problems info if any was generated
            if($problemsHtmlCode !== ''){

                echo '<div id="turbosite-global-error-manager-problem" style="left:0px; right:0px; top:0px; background-color: #000; opacity: .8; position: fixed; padding: 15px; z-index: 5000000">';
                echo $problemsHtmlCode;
                echo '</div>';
            }

            // Check if errors need to be sent to a log file
            if($this->exceptionsToLog !== '' && !StringUtils::isEmpty($exceptionsLog)){

                $this->depotManager->getLogsManager()->write($exceptionsLog, $this->exceptionsToLog);
            }

            // Check if warnings need to be sent to a log file
            if($this->warningsToLog !== '' && !StringUtils::isEmpty($warningsLog)){

                $this->depotManager->getLogsManager()->write($warningsLog, $this->warningsToLog);
            }
        });
    }


    /**
     * Aux method to create an instance of a problem data
     *
     * @param string $type See ProblemData
     * @param string $message See ProblemData
     * @param string $fileName See ProblemData
     * @param string $line See ProblemData
     *
     * @see ProblemData
     *
     * @return ProblemData A fully loaded problem data instance
     */
    private function _createProblemData(string $type, string $message, string $fileName = '', string $line = '-'){

        $problemData = new ProblemData();
        $problemData->type = $type;
        $problemData->fileName = $fileName === '' ? __FILE__ : $fileName;
        $problemData->line = $line;
        $problemData->message = $message;

        return $problemData;
    }


    /**
     * Send a notification email with the specified error data. It also sends the following data:<br>
     * <i>- Browser:</i> The browser info.<br>
     * <i>- Cookies:</i> The current cookies state when the error occurred.<br><br>
     *
     * @param ProblemData $problemData see ErrorManager::_sendProblemToBrowser
     *
     * @see GlobalErrorManager::_sendProblemToBrowser
     *
     * @return void
     */
    private function _sendProblemsToMail(ProblemData $problemData){

        // No error type means nothing to do
        /*if($problemData->type == '' || $problemData->fileName == ''){

            return;
        }

        $fileName = StringUtils::getPathElement($problemData->fileName);
        $filePath = StringUtils::getPath($problemData->fileName);
        $fullUrl = StringUtils::isEmpty($problemData->fullUrl) ? 'Unknown' : $problemData->fullUrl;
        $refererUrl = StringUtils::isEmpty($problemData->referer) ? '' : $problemData->referer;
        $subject = $problemData->type.' for '.str_replace('http://www.', '', $fullUrl).' (Script: '.$problemData->fileName.') IP:'.$_SERVER['REMOTE_ADDR'];

        // Define the email message
        $errorMessage  = 'Error type: '.(isset($problemData->type) ? $problemData->type : 'Unknown')."\n\n";
        $errorMessage .= 'IP: '.$_SERVER['REMOTE_ADDR']."\n\n";
        $errorMessage .= 'Line: '.(isset($problemData->line) ? $problemData->line : 'Unknown')."\n";
        $errorMessage .= 'File name: '.($fileName !== '' ? $fileName : 'Unknown')."\n";
        $errorMessage .= 'File path: '.($filePath !== '' ? $filePath : 'Unknown')."\n";
        $errorMessage .= 'Full URL: '.$fullUrl."\n";
        $errorMessage .= 'Referer URL: '.$refererUrl."\n\n";
        $errorMessage .= 'Message: '.(isset($problemData->message) ? $problemData->message : 'Unknown')."\n\n";
        $errorMessage .= 'Browser: '.$_SERVER['HTTP_USER_AGENT']."\n\n";
        $errorMessage .= 'Cookies: '.print_r($_COOKIE, true)."\n\n";

        if(isset($problemData->getParams)){
            $errorMessage .= 'GET params: '.$problemData->getParams."\n\n";
        }

        if(isset($problemData->postParams)){
            $errorMessage .= 'POST params: '.$problemData->postParams."\n\n";
        }

        // Add more information related to memory and app context
        if(isset($problemData->usedMemory)){
            $errorMessage .= 'Used memory: '.$problemData->usedMemory.' of '.ini_get('memory_limit')."\n\n";
        }

        // Add the error trace if available
        if(isset($problemData->trace) && $problemData->trace != ''){

            $errorMessage .= 'Trace: '.substr($problemData->trace, 0, 20000).'...'."\n\n";
        }

        if(isset($problemData->context)){
            $errorMessage .= 'Context: '.substr($problemData->context, 0, 20000).'...'."\n\n";
        }

        $mailManager = new MailManager();

        // If mail can't be queued, or we are in a localhost enviroment without email cappabilities,
        // we will launch a warning with the error information, so it does not get lost and goes to the php error logs.
        // @codingStandardsIgnoreStart
        if(!$mailManager->sendMail('TODO', $this->exceptionsToMail, $subject, $errorMessage)){

            trigger_error($problemData->message.(isset($problemData->trace) ? $problemData->trace : ''), E_USER_WARNING);
        }*/
    }
}

?>