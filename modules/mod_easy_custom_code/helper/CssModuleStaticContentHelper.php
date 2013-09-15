<?php
/**
 * @copyright   Copyright (C) 2009-2013 ThemePartner. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once dirname(__FILE__) . '/ModuleStaticContentHelper.php';

class CssModuleStaticContentHelper extends ModuleStaticContentHelper
{
    /**
     * The filename of the file to write the contents to
     * @var string
     */
    private $filename;

    /**
     * The filename of the cache file
     * @var string
     */
    private $cacheFilename;



    /**
     * Adds the written CSS file to the Joomla page
     * @param JDocument $document
     */
    public function addToDocument(JDocument $document)
    {
        $document->addStyleSheet($this->getFullUrl() . '?' . @filemtime($this->getFullPath()));
    }

    /**
     * Returns the filename of the file to write
     * @return string
     */
    protected function getFilename()
    {
        if($this->filename === null)
        {
            $this->filename = $this->moduleFolderName . '_' . $this->moduleId . '.css';
        }
        return $this->filename;
    }

    /**
     * Returns the filename of the cache file
     * @return string
     */
    protected function getCacheFilename()
    {
        if($this->cacheFilename === null)
        {
            $this->cacheFilename = 'easy_custom_code_css_cache_' . $this->moduleId;
        }

        return $this->cacheFilename;
    }
}