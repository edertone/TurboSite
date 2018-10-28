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

use org\turbocommons\src\main\php\model\BaseStrictClass;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbocommons\src\main\php\managers\FilesManager;
use org\turbosite\src\main\php\model\BlogMarkDownPost;


/**
 * Contains functionalities to manage a lightweight blog based on markdown .md files
 */
class BlogMarkDownManager extends BaseStrictClass{


    /**
     * Defines the path to the root of the blog folders
     */
    private $_rootPath = '';


    /**
     * A filesManager instance used to operate with the blog files
     *
     * @var FilesManager
     */
    private $_fm;


    /**
     * A ParseDown class instance. It is a library to convert Markdown data to html
     *
     * @var \Parsedown
     */
    private $_parseDown;


    /**
     * Contains functionalities to manage a lightweight blog based on markdown .md files.
     * To publish posts to this blog, the following folder structure must be used:
     * ROOT/year/month/day/language-postkeywords/text.md (Where language is a two digit locale)
     *
     * @param string $rootPath A full filesystem path to the root of the folder where the blog structure is located.
     *
     */
    public function __construct(string $rootPath){

	   $this->_rootPath = StringUtils::formatPath($rootPath);

	   $this->_fm = new FilesManager();

	   require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'libs'
	       .DIRECTORY_SEPARATOR.'parsedown'.DIRECTORY_SEPARATOR.'Parsedown.php';

       $this->_parseDown = new \Parsedown();
	}


	/**
	 * Get a list of BlogMarkDownPost instances containing the $count newest available blog posts.
	 *
	 * @param string $language A two digit string containing the locale we want for the obtained posts
	 * @param string $count The number of latest posts we want to obtain
	 *
	 * @return array A list with the $count number of latest blog posts instances, sorted by newest first
	 */
	public function getLatestPosts(string $language, int $count){

	    $result = [];

	    $path  = DIRECTORY_SEPARATOR.$this->_fm->getDirectoryList($this->_rootPath, 'nameDesc')[0];

	    $path .= DIRECTORY_SEPARATOR.$this->_fm->getDirectoryList($this->_rootPath.$path, 'nameDesc')[0];

	    $path .= DIRECTORY_SEPARATOR.$this->_fm->getDirectoryList($this->_rootPath.$path, 'nameDesc')[0];

	    $files = $this->_fm->getDirectoryList($this->_rootPath.$path, 'mDateDesc');

        foreach ($files as $file) {

            if(substr($file, 0, 2) === $language){

                $result[] = $this->createPostFromFilePath($path.DIRECTORY_SEPARATOR.$file);

                if(count($result) >= $count){

                    break;
                }
            }
        }

        return $result;
	}


	/**
	 * Obtain a BlogMarkDownPost instace from a given blog post filesystem path.
	 *
	 * @param string $postPath Full path to the root of the folder that contains the blog post, based on the blog root folder.
	 *
	 * @example Given a post path like the following: "2018/05/10/en-some-keywords-text-here" based on the main blog root folder, this
	 *          method will return a blog post instance with all the blog data loaded and ready to use
	 *
	 * @return BlogMarkDownPost An instance containing all the blog post data
	 */
	private function createPostFromFilePath($postPath){

	    $pathParts = explode('/', ltrim(StringUtils::formatPath($postPath, '/'), '/'));

	    $post = new BlogMarkDownPost();

	    $post->date = $pathParts[0].'-'.$pathParts[1].'-'.$pathParts[2];

	    $post->language = substr($pathParts[3], 0, 2);

	    $post->keywords = substr($pathParts[3], 3);

	    $post->text = $this->_fm->readFile($this->_rootPath.DIRECTORY_SEPARATOR.$postPath.DIRECTORY_SEPARATOR.'text.md');

	    $post->textAsHtml = $this->_parseDown->text($post->text);

	    // Get the post title from the markdown text
	    $lines = StringUtils::getLines($post->text);

	    foreach ($lines as $line) {

	        if(StringUtils::countStringOccurences($line, '#') > 0){

	            $post->title = ltrim($line, ' #');

	            break;
	        }
	    }

	    return $post;
	}
}

?>