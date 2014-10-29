<?php

/**
 * Simple directory index that lists directory and 
 * sub-directories and allows you to navigate there 
 * within, and download button for each files.
 * 
 * https://github.com/manta42404/DirectoryIndex
 * 
 * Based on http://www.directorylister.com version 2.4.3
 */
class DirectoryIndex {

    // Reserve some variables
    protected $_themeName     = null;
    protected $_directory     = null;
    protected $_appDir        = null;
    protected $_appURL        = null;
    protected $_config        = null;
    protected $_fileTypes     = null;
    protected $_systemMessage = null;


    /**
     * DirectoryIndex construct function. Runs on object creation.
     */
    public function __construct() {

        // Set class directory constant
        if(!defined('__DIR__')) {
            define('__DIR__', dirname(__FILE__));
        }

        // Set application directory
        $this->_appDir = __DIR__;

        // Build the application URL
        $this->_appURL = $this->_getAppUrl();

        // Load the configuration file
        $configFile = $this->_appDir . '/config.php';

        // Set the config array to a global variable
        if (file_exists($configFile)) {
            $this->_config = require_once($configFile);
        } else {
            $this->setSystemMessage('danger', '<b>ERROR:</b> Unable to locate application config file');
        }

        // Set the file types array to a global variable
        $this->_fileTypes = require_once($this->_appDir . '/fileTypes.php');

        // Set the theme name
        $this->_themeName = $this->_config['theme_name'];

    }


    /**
     * Creates the directory listing and returns the formatted XHTML
     *
     * @param string $path Relative path of directory to list
     * @return array Array of directory being listed
     * @access public
     */
    public function listDirectory($directory) {

        // Set directory
        $directory = $this->setDirectoryPath($directory);

        // Set directory varriable if left blank
        if ($directory === null) {
            $directory = $this->_directory;
        }

        // Get the directory array
        $directoryArray = $this->_readDirectory($directory);

        // Return the array
        return $directoryArray;
    }


    /**
     * Parses and returns an array of breadcrumbs
     *
     * @param string $directory Path to be breadcrumbified
     * @return array Array of breadcrumbs
     * @access public
     */
    public function listBreadcrumbs($directory = null) {

        // Set directory varriable if left blank
        if ($directory === null) {
            $directory = $this->_directory;
        }

        // Explode the path into an array
        $dirArray = explode('/', $directory);

        // Statically set the Home breadcrumb
        $breadcrumbsArray[] = array(
            'link' => $this->_appURL,
            'text' => 'Home'
        );

        // Generate breadcrumbs
        foreach ($dirArray as $key => $dir) {

            if ($dir != '.') {

                $dirPath  = null;

                // Build the directory path
                for ($i = 0; $i <= $key; $i++) {
                    $dirPath = $dirPath . $dirArray[$i] . '/';
                }

                // Remove trailing slash
                if(substr($dirPath, -1) == '/') {
                    $dirPath = substr($dirPath, 0, -1);
                }

                // Combine the base path and dir path
                $link = $this->_appURL . '?dir=' . $dirPath;

                $breadcrumbsArray[] = array(
                    'link' => $link,
                    'text' => $dir
                );

            }

        }

        // Return the breadcrumb array
        return $breadcrumbsArray;
    }


    /**
     * Get path of the listed directory
     *
     * @return string Path of the listed directory
     * @access public
     */
    public function getListedPath() {

        // Build the path
        if ($this->_directory == '.') {
            $path = $this->_appURL;
        } else {
            $path = $this->_appURL . $this->_directory;
        }

        // Return the path
        return $path;
    }


    /**
     * Returns the theme name.
     *
     * @return string Theme name
     * @access public
     */
    public function getThemeName() {
        // Return the theme name
        return $this->_config['theme_name'];
    }


    /**
     * Returns the path to the chosen theme directory
     *
     * @param bool $absolute Wether or not the path returned is absolute (default = false).
     * @return string Path to theme
     * @access public
     */
    public function getThemePath($absolute = false) {
        if ($absolute) {
            // Set the theme path
            $themePath = $this->_appDir . '/themes/' . $this->_themeName;
        } else {
            // Get relative path to application dir
            $realtivePath = $this->_getRelativePath(getcwd(), $this->_appDir);

            // Set the theme path
            $themePath = $realtivePath . '/themes/' . $this->_themeName;
        }

        return $themePath;
    }


    /**
     * Get an array of error messages or false when empty
     *
     * @return array|bool Array of error messages or false
     * @access public
     */
    public function getSystemMessages() {
        if (isset($this->_systemMessage) && is_array($this->_systemMessage)) {
            return $this->_systemMessage;
        } else {
            return false;
        }
    }


    /**
     * Returns string of file size in human-readable format
     *
     * @param  string $filePath Path to file
     * @return string Human-readable file size
     * @access public
     */
    function getFileSize($filePath) {

        // Get file size
        $bytes = filesize($filePath);

        // Array of file size suffixes
        $sizes = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');

        // Calculate file size suffix factor
        $factor = floor((strlen($bytes) - 1) / 3);

        // Calculate the file size
        $fileSize = round($bytes / pow(1024, $factor), 0) .'<em class="space"> </em>'. $sizes[$factor];

        return $fileSize;

    }


    /**
     * Set directory path variable
     *
     * @param string $path Path to directory
     * @return string Sanitizd path to directory
     * @access public
     */
    public function setDirectoryPath($path = null) {

        // Set the directory global variable
        $this->_directory = $this->_setDirecoryPath($path);

        return $this->_directory;

    }


    /**
     * Add a message to the system message array
     *
     * @param string $type The type of message (ie - error, success, notice, etc.)
     * @param string $message The message to be displayed to the user
     * @return bool true on success
     * @access public
     */
    public function setSystemMessage($type, $text) {

        // Create empty message array if it doesn't already exist
        if (isset($this->_systemMessage) && !is_array($this->_systemMessage)) {
            $this->_systemMessage = array();
        }

        // Set the error message
        $this->_systemMessage[] = array(
            'type'  => $type,
            'text'  => $text
        );

        return true;
    }


    /**
     * Send headers to download file
     *
     * @param  string $filePath Path to file
     * @return bool true on success
     * @access public
     */
    public function downloadFile($filePath) {
        if(!$this->_isForbidden($filePath) && is_file($filePath)) {

            // Get file extension
            $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if (isset($this->_fileTypes[$fileExt][1])) {
                $mimeType = $this->_fileTypes[$fileExt][1];
            } else {
                $mimeType = $this->_fileTypes['blank'][1];
            }

            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: public');
            header('Content-Description: File Transfer');
            header('Content-Type: '.$mimeType);
            header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: '.filesize($filePath));
            readfile($filePath);

        } else {
            $this->setSystemMessage('danger', '<b>ERROR:</b> Access denied');
            return false;
        }

    }


    /**
     * Validates and returns the directory path
     *
     * @param string @dir Directory path
     * @return string Directory path to be listed
     * @access protected
     */
    protected function _setDirecoryPath($dir) {

        // Check for an empty variable
        if (empty($dir) || $dir == '.') {
            return '.';
        }

        // Eliminate double slashes
        while (strpos($dir, '//')) {
            $dir = str_replace('//', '/', $dir);
        }

        // Remove trailing slash if present
        if(substr($dir, -1, 1) == '/') {
            $dir = substr($dir, 0, -1);
        }

        // Verify file path exists and is a directory
        if (!file_exists($dir) || !is_dir($dir)) {
            // Set the error message
            $this->setSystemMessage('danger', '<b>ERROR:</b> File path does not exist');

            // Return the web root
            return '.';
        }

        // Prevent access to forbidden files
        if ($this->_isForbidden($dir)) {
            // Set the error message
            $this->setSystemMessage('danger', '<b>ERROR:</b> Access denied');

            // Set the directory to web root
            return '.';
        }

        // Prevent access to parent folders
        if (strpos($dir, '<') !== false || strpos($dir, '>') !== false
        || strpos($dir, '..') !== false || strpos($dir, '/') === 0) {
            // Set the error message
            $this->setSystemMessage('danger', '<b>ERROR:</b> An invalid path string was detected');

            // Set the directory to web root
            return '.';
        } else {
            // Should stop all URL wrappers (Thanks to Hexatex)
            $directoryPath = $dir;
        }

        // Return
        return $directoryPath;
    }


    /**
     * Loop through directory and return array with file info, including
     * file path, size, modification time, icon and sort order.
     *
     * @param string $directory Directory path
     * @param @sort Sort method (default = natcase)
     * @return array Array of the directory contents
     * @access protected
     */
    protected function _readDirectory($directory, $sort = 'natcase') {

        // Initialize array
        $directoryArray = array();

        // Get directory contents
        $files = scandir($directory);

        // Read files/folders from the directory
        foreach ($files as $file) {

            if ($file != '.') {

                // Get files relative path
                $relativePath = $directory . '/' . $file;

                if (substr($relativePath, 0, 2) == './') {
                    $relativePath = substr($relativePath, 2);
                }

                // Get files absolute path
                $realPath = realpath($relativePath);

                // Determine file type by extension
                if (is_dir($realPath)) {
                    $iconClass = 'fa-folder';
                    $sort = 1;
                } else {
                    // Get file extension
                    $fileExt = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));

                    if (isset($this->_fileTypes[$fileExt][0])) {
                        $iconClass = $this->_fileTypes[$fileExt][0];
                    } else {
                        $iconClass = $this->_fileTypes['blank'][0];
                    }

                    $sort = 2;
                }

                if ($file == '..') {

                    if ($this->_directory != '.') {
                        // Get parent directory path
                        $pathArray = explode('/', $relativePath);
                        unset($pathArray[count($pathArray)-1]);
                        unset($pathArray[count($pathArray)-1]);
                        $directoryPath = implode('/', $pathArray);

                        if (!empty($directoryPath)) {
                            $directoryPath = '?dir=' . $directoryPath;
                        }

                        // Add file info to the array
                        $directoryArray['..'] = array(
                            'file_path'  => $this->_appURL . $directoryPath,
                            'url_path' => $this->_appURL . $directoryPath,
                            'file_size'  => '-',
                            'mod_time'   => date($this->_config['time_format'], filemtime($realPath)),
                            'icon_class' => 'fa-level-up',
                            'sort'       => 0
                        );
                    }

                } elseif (!$this->_isForbidden($relativePath)) {

                    // Add all non-forbidden files to the array
                    if ($this->_directory != '.' || $file != 'index.php') {

                        // Build the file path
                        if (is_dir($relativePath)) {
                            $urlPath = '?dir=' . $relativePath;
                        } else {
                            $urlPath = $relativePath;
                        }

                        // Add the info to the main array
                        $directoryArray[pathinfo($relativePath, PATHINFO_BASENAME)] = array(
                            'file_path'  => $relativePath,
                            'url_path'   => $urlPath,
                            'file_size'  => is_dir($realPath) ? '-' : $this->getFileSize($realPath),
                            'mod_time'   => date($this->_config['time_format'], filemtime($realPath)),
                            'icon_class' => $iconClass,
                            'sort'       => $sort
                        );
                    }

                }
            }

        }

        // Sort the array
        $sortedArray = $this->_arraySort($directoryArray, $this->_config['list_sort_order']);

        // Return the array
        return $sortedArray;

    }


    /**
     * Sorts an array by the provided sort method.
     *
     * @param array $array Array to be sorted
     * @param string $sortMethod Sorting method (acceptable inputs: natsort, natcasesort, etc.)
     * @param boolen $reverse Reverse the sorted array order if true (default = false)
     * @return array
     * @access protected
     */
    protected function _arraySort($array, $sortMethod) {
        // Create empty arrays
        $sortedArray = array();
        $finalArray  = array();

        // Create new array of just the keys and sort it
        $keys = array_keys($array);

        switch ($sortMethod) {
            case 'asort':
                asort($keys);
                break;
            case 'arsort':
                arsort($keys);
                break;
            case 'ksort':
                ksort($keys);
                break;
            case 'krsort':
                krsort($keys);
                break;
            case 'natcasesort':
                natcasesort($keys);
                break;
            case 'natsort':
                natsort($keys);
                break;
            case 'shuffle':
                shuffle($keys);
                break;
        }

        // Loop through the sorted values and move over the data
        if ($this->_config['list_folders_first']) {

            foreach ($keys as $key) {
                if ($array[$key]['sort'] == 0) {
                    $sortedArray['0'][$key] = $array[$key];
                }
            }

            foreach ($keys as $key) {
                if ($array[$key]['sort'] == 1) {
                    $sortedArray[1][$key] = $array[$key];
                }
            }

            foreach ($keys as $key) {
                if ($array[$key]['sort'] == 2) {
                    $sortedArray[2][$key] = $array[$key];
                }
            }

        } else {

            foreach ($keys as $key) {
                if ($array[$key]['sort'] == 0) {
                    $sortedArray[0][$key] = $array[$key];
                }
            }

            foreach ($keys as $key) {
                if ($array[$key]['sort'] > 0) {
                    $sortedArray[1][$key] = $array[$key];
                }
            }

        }

        // Merge the arrays
        foreach ($sortedArray as $array) {
            if (empty($array)) continue;
            foreach ($array as $key => $value) {
                $finalArray[$key] = $value;
            }
        }

        // Return sorted array
        return $finalArray;

    }


    /**
     * Determines if a file is specified as forbidden
     *
     * @param string $filePath Path to file to be checked if forbidden
     * @return boolean Returns true if file is in forbidden array, false if not
     * @access protected
     */
    protected function _isForbidden($filePath) {

        // Add system files to forbidden files array
        $this->_config['forbidden'][] = 'index.php';
        $this->_config['forbidden'][] = 'resources*';

        // Add dot files to forbidden files array
        if ($this->_config['hide_dot_files']) {

            $this->_config['forbidden'][] = '.*';

        }

        // Compare path array to all forbidden file paths
        foreach ($this->_config['forbidden'] as $forbiddenPath) {

            if (fnmatch($forbiddenPath, $filePath)) {

                return true;

            }

        }

        return false;

    }


    /**
     * Builds the root application URL from server variables.
     *
     * @return string The application URL
     * @access protected
     */
    protected function _getAppUrl() {

        // Get the server protocol
        if (!empty($_SERVER['HTTPS'])) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }

        // Get the server hostname
        $host = $_SERVER['HTTP_HOST'];

        // Get the URL path
        $pathParts = pathinfo($_SERVER['PHP_SELF']);
        $path      = $pathParts['dirname'];

        // Remove backslash from path (Windows fix)
        if (substr($path, -1) == '\\') {
            $path = substr($path, 0, -1);
        }

        // Ensure the path ends with a forward slash
        if (substr($path, -1) != '/') {
            $path = $path . '/';
        }

        // Build the application URL
        $appUrl = $protocol . $host . $path;

        // Return the URL
        return $appUrl;
    }


    /**
      * Compares two paths and returns the relative path from one to the other
     *
     * @param string $fromPath Starting path
     * @param string $toPath Ending path
     * @return string $relativePath Relative path from $fromPath to $toPath
     * @access protected
     */
    protected function _getRelativePath($fromPath, $toPath) {

        // Define the OS specific directory separator
        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

        // Remove double slashes from path strings
        $fromPath = str_replace(DS . DS, DS, $fromPath);
        $toPath = str_replace(DS . DS, DS, $toPath);

        // Explode working dir and cache dir into arrays
        $fromPathArray = explode(DS, $fromPath);
        $toPathArray = explode(DS, $toPath);

        // Remove last fromPath array element if it's empty
        $x = count($fromPathArray) - 1;

        if(!trim($fromPathArray[$x])) {
            array_pop($fromPathArray);
        }

        // Remove last toPath array element if it's empty
        $x = count($toPathArray) - 1;

        if(!trim($toPathArray[$x])) {
            array_pop($toPathArray);
        }

        // Get largest array count
        $arrayMax = max(count($fromPathArray), count($toPathArray));

        // Set some default variables
        $diffArray = array();
        $samePath = true;
        $key = 1;

        // Generate array of the path differences
        while ($key <= $arrayMax) {

            // Get to path variable
            $toPath = isset($toPathArray[$key]) ? $toPathArray[$key] : null;

            // Get from path variable
            $fromPath = isset($fromPathArray[$key]) ? $fromPathArray[$key] : null;

            if ($toPath !== $fromPath || $samePath !== true) {

                // Prepend '..' for every level up that must be traversed
                if (isset($fromPathArray[$key])) {
                    array_unshift($diffArray, '..');
                }

                // Append directory name for every directory that must be traversed
                if (isset($toPathArray[$key])) {
                    $diffArray[] = $toPathArray[$key];
                }

                // Directory paths have diverged
                $samePath = false;
            }

            // Increment key
            $key++;
        }

        // Set the relative thumbnail directory path
        $relativePath = implode('/', $diffArray);

        // Return the relative path
        return $relativePath;

    }

}
