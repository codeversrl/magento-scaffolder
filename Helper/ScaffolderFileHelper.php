<?php

namespace Codever\Scaffolder\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Module\Dir;
use Magento\Framework\Filesystem\Driver\File;

class ScaffolderFileHelper extends AbstractHelper
{
    /**
     * template data content
     *
     * @var array
     */
    private array $data;

    /**
     * Magento directory manager
     *
     * @var DirectoryList
     */
    private DirectoryList $dir;

    /**
     * Magento directory manager
     *
     * @var File
     */
    private File $file;

    /**
     * Magento Module path helper
     *
     * @var Dir
     */
    private Dir $moduleDir;

    /**
     * Class constructor
     *
     * @param Context $context
     * @param DirectoryList $dir
     * @param Dir $moduleDir
     * @param File $file
     *
     * @return void
     */
    public function __construct(
        Context $context,
        DirectoryList $dir,
        Dir $moduleDir,
        File $file
    ) {
        $this->dir = $dir;
        $this->file = $file;
        $this->moduleDir = $moduleDir;
        parent::__construct($context);
    }

    /**
     * Renders a template file and return output content
     *
     * @param  string $templateFilepath
     * @param  array $data
     * @return string
     */
    public function render(string $templateFilepath, array $data): string
    {
        $this->data = $data;
        // phpcs:ignore
        ob_start();
        $block = $this; // needs to stay here to comply template's coding standard
        try {
            // phpcs:ignore
            include $templateFilepath;
        } catch (\Exception $exception) {
            ob_end_clean();
            throw $exception;
        }
        return ob_get_clean();
    }

    /**
     * Writes destination file
     *
     * @param  string $filepath
     * @param  string $content
     * @return void
     */
    public function write(string $filepath, string $content)
    {
        if (!$this->fileExists($filepath)) {
            $this->fileWrite($filepath, $content);
        }
    }

    /**
     * Is file exists
     *
     * @param string $filepath
     * @return bool
     * @throws FileSystemException
     */
    public function fileExists(string $filepath): bool
    {
        return $this->file->isExists($filepath);
    }

    /**
     * Creates a directory recursively
     *
     * @param string $path
     * @param int $mode
     * @return bool
     * @throws FileSystemException
     */
    public function dirMkdir(string $path, int $mode = 0777): bool
    {
        return $this->file->createDirectory($path, $mode);
    }

    /**
     * Writes a file from string, file or stream
     *
     * @param string $filepath
     * @param string $content
     * @return int
     * @throws FileSystemException
     */
    public function fileWrite(string $filepath, $content): int|bool
    {
        $resource = $this->file->fileOpen($filepath, 'w');
        return $this->file->fileWrite($resource, $content);
    }

     /**
      * Check if a directory is empty
      *
      * If the directory doesn't exists, it will return as empty
      *
      * @param string $dirpath
      * @return bool
      */
    public function dirIsEmpty(string $dirpath): bool
    {
        if (!$this->fileExists($dirpath)) {
            return true;
        }
        $di = new \RecursiveDirectoryIterator($dirpath, \FilesystemIterator::SKIP_DOTS);
        if (iterator_count($di) !== 0) {
            return false;
        }
        return true;
    }

    /**
     * Check permissions for reading file or directory
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function fileIsReadable(string $path): bool
    {
        return $this->file->isReadable($path);
    }

    /**
     * Check if given path is writable
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function fileIsWritable($path): bool
    {
        return $this->file->isWritable($path);
    }

    /**
     * Function to be used in templates to get values
     *
     * @param  string $key
     * @return string
     */
    private function getData(string $key): string
    {
        if (empty($key) || $key === null || !isset($this->data[$key])) {
            return '';
        }
        return $this->data[$key];
    }

    /**
     * Gets specific Magento folder path
     *
     * @param  string $folderName
     * @return string
     */
    public function getMagentoPath(string $folderName = null): string
    {
        return $this->dir->getPath($folderName);
    }

    /**
     * Gets path of folders inside a specific Magento module
     *
     * @param  string $moduleName
     * @return string
     */
    public function getModulePath(string $moduleName): string
    {
        return $this->moduleDir->getDir($moduleName);
    }

    /**
     * Creates new directory if it does not exists
     *
     * @param  string $path
     * @param  int $mode
     * @param  bool $recursive
     * @return void
     */
    public function createDirIfNotExists(string $path, int $mode = 0755, bool $recursive = true): void
    {
        if (!$this->fileExists($path)) {
            $this->dirMkdir($path, $mode, $recursive);
        }
    }

    /**
     * Creates a destination directory for the new Magento extension
     *
     * @param  string $basepath
     * @param  string $dirname
     * @return void
     */
    public function generateDestinationDirectory(string $basepath, string $dirname = null): void
    {
        $path = $this->getDestinationBasepath($basepath);
        if ($dirname) {
            $path .= DIRECTORY_SEPARATOR . $dirname;
        }
        $this->createDirIfNotExists($path);
    }

    /**
     * Generates a file for the new extension
     *
     * The file is generated from a template, that shares the same name with the destination file
     * except for the suffix $templateExtension, that will be stripped down before creating the new file.
     * Template file and new file will share also the subfolders, starting from each own extension root
     *
     * @param  string $originBasepath the root path of module where template is
     * @param  string $destinationBasepath the root path of the extension where the new file will be created
     * @param  string $filename the name of the destination file. The template name is the same, but with a suffix
     * @param  string $data the data to be used inside template
     * @param  string $dirname the subfolders path where template is, starting from extension root.
     *                Is the same subfolder path where new file will be created.
     *                If null, extension root will be used
     * @param  string $templateExtension the suffix that is added to template file, including initial dot.
     *                The default value is '.phtml'
     * @return void
     */
    public function generateDestinationFile(
        string $originBasepath,
        string $destinationBasepath,
        string $filename,
        array $data,
        string $dirname = null,
        string $templateExtension = '.phtml'
    ): void {
        $templateFile = $filename.$templateExtension;
        $template = $this->getOriginTemplatePath($originBasepath, $templateFile, $dirname);
        $output = $this->render($template, $data);
        $this->write($this->getDestinationFilePath($destinationBasepath, $filename, $dirname), $output);
    }

    /**
     * Calculates path where template file is
     *
     * @param  string $originBasepath
     * @param  string $filename
     * @param  string $dirname
     * @return string
     */
    public function getOriginTemplatePath(string $originBasepath, string $filename, string $dirname = null): string
    {
        $templateFile = $originBasepath;
        if ($dirname) {
            $templateFile .=  DIRECTORY_SEPARATOR . $dirname;
        }
        $templateFile .= DIRECTORY_SEPARATOR . $filename;
        return $templateFile;
    }

    /**
     * Calculates path where new extension file should be created
     *
     * @param  string $basepath
     * @param  string $filename
     * @param  string $dirname
     * @return string
     */
    public function getDestinationFilePath(string $basepath, string $filename, string $dirname = null): string
    {
        $path = $this->getDestinationBasepath($basepath);
        $destinationFile = $path;
        if ($dirname) {
            $destinationFile .=  DIRECTORY_SEPARATOR . $dirname;
        }
        $destinationFile .= DIRECTORY_SEPARATOR . $filename;
        return $destinationFile;
    }

    /**
     * Gets a full path of a folder inside Magento "app" folder
     *
     * @param  string $basepath
     * @return string
     */
    public function getDestinationBasepath(string $basepath): string
    {
        $subPaths = [
            $this->getMagentoPath('app'),
            $basepath, // 'code' or 'design'.DIRECTORY_SEPARATOR.'frontend' + vendor + name
        ];
        $finalBasepath = implode(DIRECTORY_SEPARATOR, $subPaths);
        return $finalBasepath;
    }
}
