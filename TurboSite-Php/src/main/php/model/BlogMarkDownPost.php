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
	 * The keywords part of the filesystem path that defines the post.
	 *
	 * @example This property will contain the string "some-key-words-are-placed-here" for a blog post that is saved on
	 * file system with the following path: BLOGROOT/2018/10/25/en-some-key-words-are-placed-here
	 *
	 */
	public $keywords = '';


	/**
	 * The keywords part of the filesystem path that defines the post, but formatted as an array where each keyword is an
	 * array element
	 *
	 * @see BlogMarkDownPost::keywords
	 */
	public $keywordsAsArray = '';


	/**
	 * The post title. It is detected from the first H1 element found on the text string
	 */
	public $title = '';


	/**
	 * The full post text as a markdown formatted string (including the post title)
	 */
	public $text = '';


	/**
	 * The full post text but formatted with HTML tags instead of the original markdown
	 */
	public $textAsHtml = '';

}

?>