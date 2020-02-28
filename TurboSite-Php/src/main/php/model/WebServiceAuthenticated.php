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

use org\turbosite\src\main\php\managers\WebServiceManager;


/**
 * WebServiceAuthenticated
 */
abstract class WebServiceAuthenticated extends WebServiceManager{


    // This class will extend the base webservice one to provide user login authentication on each call by default.

    // We do it this way to make sure that the base webservice does not depend on the turbodepot library users to provide the base functionality

    // What happens if we declare a reusable webservice that can be called either as authenticated and not?

    // May it be interesting to require the athentication only when runnyng from url and not when called via code? or will it be configurable?

    // Are we sure that the services will be always authenticated by default?

    // Are we sure that the "token" post parameter will be reserved for autenticated webservices (an error is thrown when trying to redeclare it)?

    // Each loged user must be verified against its possible operations. In the case of a web service authenticated,
    // the default operation name will be the full namespace + class (It may be changed at any time later).
}

?>