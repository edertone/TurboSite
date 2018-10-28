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
 * This entity defines a single blog post.
 * To be used with BlogMarkDownManager
 */
class BlogMarkDownPost{


	/**
	 * The post date in a yyyy-mm-dd format
	 */
	public $date = '';


	/**
	 * The post language in a 2 digit format
	 */
	public $language = '';


	/**
	 * The post title. It is detected from the first H1 element found on the text string
	 */
	public $title = '';


	/**
	 * The full post text as a markdown formatted string (including the post title)
	 */
	public $text = '';

}

?>