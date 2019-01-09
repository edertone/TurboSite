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
use org\turbocommons\src\main\php\managers\BrowserManager;
use org\turbosite\src\main\php\model\ProblemData;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbocommons\src\main\php\managers\MailManager;


/**
 * Used to encapsulate all the global error management.
 * It will give total control over the code exceptions, the way they are handled and notified.
 */
class GlobalErrorManager extends BaseSingletonClass{


    /**
     * Flag that tells the class to show or hide the all browser exceptions output.
     * This is normally set to false cause html errors will give lots of information to malicious users,
     * so setting it to true will generate an email warning notification if email error notifications are enabled
     */
    public $exceptionsToBrowser = false;


    /**
     * Enable or disable the email error notifications. If set to empty '' string, no mail notifications will happen.
     * If an email address is specified, any error or warning that happens on the application will be sent to the specified address with all the detailed information.
     */
    public $exceptionsToMail = '';


    /**
     * Flag that tells the class to show or hide all the browser warnings output.
     */
    public $warningsToBrowser = false;


    /**
     * Enable or disable the email warning notifications. Works the same as $exceptionsToMail
     */
    public $warningsToMail = '';


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
     * If the php errors are setup to be output to the browser html code, this method will generate an alert that
     * will be displayed as any other error message that is handled by this class.
     * This verification is very important, cause showing the errors on browser is very dangerous as gives a lot of information to malicious users.
     *
     * @return void
     */
    private function _checkBrowserOutputIsDisabled(){

//         $message = '';

//         if($this->exceptionsToBrowser){

//             $message = 'ErrorManager::getInstance()->exceptionsToBrowser Are enabled.';
//         }

//         $displayErrors = false;

//         switch (ini_get('display_errors')) {

//             case '1':
//             case 'On':
//             case 'true':
//                 $displayErrors = true;
//                 break;

//             default:
//         }

//         if($displayErrors){

//             $message = 'PHP Errors are globally enabled (display_errors).';
//         }

//         if($message != ''){

//             $problemData = new ProblemData();
//             $problemData->type = ProblemData::PHP_WARNING;
//             $problemData->fileName = __FILE__;
//             $problemData->line = '';
//             $problemData->message = $message.' Malicious users will get lots of information, please disable all php error browser output.';

//             // Check if error needs to be sent by email
//             if($this->exceptionsToMail != ''){

//                 $this->_sendProblemToMail($problemData);
//             }

//             // Check if error needs to be sent to browser output
//             if($this->exceptionsToBrowser){

//                 $this->_sendProblemToBrowser($problemData);
//             }
//         }
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
                    $type = 'E_WARNING ';
                    break;

                case E_NOTICE:
                    $type = 'E_NOTICE ';
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
                    $type = 'E_ALL ';
                    break;

                default:
            }

            $problemData = new ProblemData();
            $problemData->type = $type;
            $problemData->fileName = $errorFile;
            $problemData->line = $errorLine;
            $problemData->message = $errorMessage;
            $problemData->context = print_r($errorContext, true);

            $this->_problemsFound[] = $problemData;
        });
    }


    /**
     * Set a handler to manage fatal php errors
     *
     * @return void
     */
    private function _setExceptionHandler(){

        set_exception_handler(function (Throwable $error) {

            $problemData = new ProblemData();
            $problemData->type = 'FATAL EXCEPTION';
            $problemData->fileName = $error->getFile();
            $problemData->line = $error->getLine();
            $problemData->message = $error->getMessage();
            $problemData->backTrace = $error->getTraceAsString();

            $this->_problemsFound[] = $problemData;
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

            // Check if problems need to be sent to browser output
            $errorsHtmlCode = '';

            foreach ($this->_problemsFound as $problem) {

                if(($this->exceptionsToBrowser && $problem->type === 'FATAL EXCEPTION') ||
                    ($this->warningsToBrowser && $problem->type !== 'FATAL EXCEPTION')){

                        $errorsHtmlCode .= '<p style="all: initial; color: #fff; margin-bottom: 15px; float: left"><b>PHP Problem: ';

                        $errorsHtmlCode .= $problem->type.'<br>'.htmlspecialchars($problem->message).'</b><br>';

                        $errorsHtmlCode .= $problem->fileName;

                        if(isset($problem->line) && $problem->line !== ''){

                            $errorsHtmlCode .= ' line '.$problem->line;
                        }

                        $errorsHtmlCode .= '<br>'.str_replace("\n", '<br>', $problem->trace);

                        $errorsHtmlCode .= '</p>';
                }
            }

            if($errorsHtmlCode !== ''){

                echo '<div id="turbosite-global-error-manager-problem" style="left:0px; right:0px; top:0px; background-color: #000; opacity: .8; position: fixed; padding: 15px">';
                echo $errorsHtmlCode;
                echo '</div>';
            }

            // Check if problems need to be sent to mail
//             foreach ($this->_problemsFound as $problem) {



//                 // Check if problem needs to be sent by email
//                 if(($this->exceptionsToMail && $problem->type === 'FATAL EXCEPTION') ||
//                     ($this->warningsToMail && $problem->type !== 'FATAL EXCEPTION')){

//                         $this->_sendProblemToMail($problem);
//                 }
//             }
        });
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
        if($problemData->type == '' || $problemData->fileName == ''){

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
        }
    }
}

?>