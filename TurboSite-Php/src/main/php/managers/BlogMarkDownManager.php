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
     * Contains functionalities to manage a lightweight blog based on markdown .md files.
     * To publish posts to this blog, the following folder structure must be used:
     * ROOT/year/month/day/language-posturl/text.md (Where language is a two digit locale)
     *
     * @param string $rootPath A full filesystem path to the root of the folder where the blog structure is located.
     *
     */
    public function __construct(string $rootPath){

	   $this->_rootPath = StringUtils::formatPath($rootPath);

	   $this->_fm = new FilesManager();
	}



	/**
	 * Get a BlogMarkDownPost instance containing the newesty available blog post.
	 *
	 * @param string $language A two digit string containing the post locale we want to obtain
	 *
	 * @return BlogMarkDownPost An instance with all the post data
	 */
	public function getLatestPost(string $language){

	    $post = new BlogMarkDownPost();

	    $latestYear = $this->_fm->getDirectoryList($this->_rootPath, 'nameDesc')[0];

	    $latestMonth = $this->_fm->getDirectoryList(
	        $this->_rootPath.DIRECTORY_SEPARATOR.$latestYear, 'nameDesc')[0];

	    $latestDay = $this->_fm->getDirectoryList(
	        $this->_rootPath.DIRECTORY_SEPARATOR.$latestYear.DIRECTORY_SEPARATOR.$latestMonth, 'nameDesc')[0];

        $files = $this->_fm->getDirectoryList(
            $this->_rootPath.DIRECTORY_SEPARATOR.$latestYear.DIRECTORY_SEPARATOR.$latestMonth.DIRECTORY_SEPARATOR.$latestDay, 'mDateDesc');

        foreach ($files as $file) {

            if(substr($file, 0, 2) === $language){

                $post->date = $latestYear.'-'.$latestMonth.'-'.$latestDay;

                $post->language = $language;

                $post->text = $this->_fm->readFile(
                    $this->_rootPath.DIRECTORY_SEPARATOR.$latestYear.DIRECTORY_SEPARATOR.$latestMonth.DIRECTORY_SEPARATOR
                    .$latestDay.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR.'text.md');

                $post->title = $this->getPostTitleFromMdText($post->text);

                return $post;
            }
        }
	}


	/**
	 * Try to detect the post title from a markdown text
	 *
	 * @param string $mdText A full markdown text containing a title (H1, H2, H3,...)
	 *
	 * @return string The detected post title
	 */
	private function getPostTitleFromMdText($mdText){

	    $lines = StringUtils::getLines($mdText);

	    foreach ($lines as $line) {

	        if(StringUtils::countStringOccurences($line, '#') > 0){

	            // TODO - clean unwanted # chars and spaces from the beginning

	            return $line;
	        }
	    }
	}
}

?>