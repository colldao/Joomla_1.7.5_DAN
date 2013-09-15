<?php
/**
 * @copyright   Copyright (C) 2009-2013 ThemePartner. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

abstract class ModuleStaticContentHelper
{
    /**
     * The database ID of the module
     * @var int
     */
    protected $moduleId;

    /**
     * The folder name of the module, for example: mod_easy_custom_code
     * @var
     */
    protected $moduleFolderName;

    /**
     * The name of the cache folder, for example: tp_mod_easy_custom_code
     * @var string
     */
    protected $cacheFolderName;

    /**
     * Contains the path to the cache directory
     * @var string
     */
    protected $cacheDir;

    /**
     * Contains true if the file to write has been successfully written
     * @var bool
     */
    protected $fileCreated = false;

    /**
     * The contents of the file to write
     * @var string
     */
    protected $fileContents = '';

    /**
     * The hash that defines that contents of the file to write
     * @var string|null
     */
    protected $uniquenessHash;


    public function __construct($moduleFolderName, $moduleId)
    {
        $this->moduleId = $moduleId;
        $this->moduleFolderName = $moduleFolderName;
        $this->cacheFolderName = 'tp_' . $moduleFolderName;
        $this->cacheDir = JPATH_CACHE . '/' . $this->cacheFolderName;

        $this->checkCacheDir();
    }

    /**
     * Writes the given contents to the file in the cache folder. Also makes sure cache is created.
     * @param $fileContents
     * @return bool
     */
    public function writeFile($fileContents)
    {
        $this->fileContents = $fileContents;

        if($this->isCacheValid())
        {
            $this->fileCreated = true;
            return true;
        }

        $result = @file_put_contents($this->getFullPath(), $this->fileContents);
        if(false !== $result)
        {
            // the file was successfully created in the cache folder
            $this->fileCreated = true;

            // write the hash to the cache file
            $this->writeNewCacheFile();

            return true;
        }

        return false;
    }

    /**
     * Returns the uri to the template folder
     * @param bool $prependBase
     * @return string
     */
    public function getUrl($prependBase = true)
    {
        if($prependBase)
        {
            // http://localhost/joomla25/
            return JURI::base(false) . 'modules/' . $this->moduleFolderName;
        }
        else
        {
            // /joomla25
            return JURI::base(true) . '/modules/' . $this->moduleFolderName;
        }
    }

    /**
     * @return bool
     */
    public function isFileCreated()
    {
        return $this->fileCreated;
    }

    /**
     * Returns the full path to the new css file
     * @return string
     */
    public function getFullPath()
    {
        return $this->cacheDir . '/' . $this->getFilename();
    }

    /**
     * Returns the URL to the written CSS file
     * @param bool $prependBase
     * @return string
     */
    public function getFullUrl($prependBase = false)
    {
        if($prependBase)
        {
            return JURI::base(false) . 'cache/' . $this->cacheFolderName . '/' . $this->getFilename();
        }
        else
        {
            return JURI::base(true) . '/cache/' . $this->cacheFolderName . '/' . $this->getFilename();
        }
    }

    /**
     * Should add the file to the given document
     * @param JDocument $document
     */
    abstract public function addToDocument(JDocument $document);

    /**
     * Should return the filename of the file to write the file contents to.
     * @return string
     */
    abstract protected function getFilename();

    /**
     * Should return the filename of the cache file.
     * @return string
     */
    abstract protected function getCacheFilename();

    /**
     * Checks if cache directory exists and is writable
     * @return boolean
     */
    protected function checkCacheDir()
    {
        if(!is_dir($this->cacheDir))
        {
            $this->createCacheDir();
        }

        return (is_dir($this->cacheDir) && is_writable($this->cacheDir));
    }

    /**
     * Creates the cache dir when it does not exist yet.
     * @return boolean
     */
    protected function createCacheDir()
    {
        if(is_dir(JPATH_CACHE) && is_writable(JPATH_CACHE))
        {
            return @mkdir($this->cacheDir, 0755);
        }

        return false;
    }

    /**
     * @return string
     */
    protected function getUniquenessHash()
    {
        // only create cache hash if it wasn't done already
        if($this->uniquenessHash === null)
        {
            $this->uniquenessHash = md5($this->fileContents);
        }

        return $this->uniquenessHash;
    }

    /**
     * Returns the contents of the cache file containing the hash
     * @return bool|string
     */
    protected function getCacheFileContents()
    {
        $fullPath = $this->getFullCacheFilePath();
        if(!file_exists($fullPath))
            return false;

        return file_get_contents($fullPath);
    }

    /**
     * @return string
     */
    protected function getFullCacheFilePath()
    {
        return $this->cacheDir . '/' . $this->getCacheFilename();
    }

    /**
     * Checks if the current cache is still valid
     * @return bool
     */
    protected function isCacheValid()
    {
        $cacheFileContents = $this->getCacheFileContents();
        if($cacheFileContents === false)
            return false;

        return $this->getUniquenessHash() == $cacheFileContents;
    }

    /**
     * This will write the template params hash to the cache file
     * @return int|bool
     */
    protected function writeNewCacheFile()
    {
        return file_put_contents($this->getFullCacheFilePath(), $this->getUniquenessHash());
    }
}