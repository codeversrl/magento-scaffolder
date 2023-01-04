<?php

namespace Codever\Scaffolder\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Module\Dir;

class ScaffolderFileHelper extends AbstractHelper
{

    private $data;
    private $dir;
    private $moduleDir;

    public function __construct(
        Context $context,
        DirectoryList $dir,
        Dir $moduleDir
    ) {
        $this->dir = $dir;
        $this->moduleDir = $moduleDir;
        parent::__construct($context);
    }

    public function render($templateFilepath, $data)
    {
        $this->data = $data;
        ob_start();
        $block = $this; // needs to stay here to comply template's coding standard
        try {
            include $templateFilepath;
        } catch (\Exception $exception) {
            ob_end_clean();
            throw $exception;
        }
        return ob_get_clean();
    }

    public function write($filepath, $content)
    {
        if (!file_exists($filepath)) {
            $fp = fopen($filepath, "w");
            fwrite($fp, $content);
            fclose($fp);
        }
    }

    private function getData(string $key)
    {
        if (empty($key) || is_null($key) || !isset($this->data[$key])) {
            return '';
        }
        return $this->data[$key];
    }

    public function getMagentoPath(string $folderName = null)
    {
        return $this->dir->getPath($folderName);
    }

    public function getModulePath(string $moduleName)
    {
        return $this->moduleDir->getDir($moduleName);
    }

    public function createDirIfNotExists($path, $mode = 0755, $recursive = true)
    {
        if (!file_exists($path)) {
            mkdir($path, $mode, $recursive);
        }
    }

    public function generateDestinationDirectory(string $basepath, string $dirname = null)
    {
        $path = $this->getDestinationBasepath($basepath);
        if ($dirname) {
            $path .= DIRECTORY_SEPARATOR . $dirname;
        }
        $this->createDirIfNotExists($path);
    }

    public function generateDestinationFile(
        string $originBasepath,
        string $destinationBasepath,
        string $filename,
        array $data,
        string $dirname = null,
        string $templateExtension = '.phtml')
    {
        $templateFile = $filename.$templateExtension;
        $template = $this->getOriginTemplatePath($originBasepath, $templateFile, $dirname);
        $output = $this->render($template, $data);
        $this->write($this->getDestinationFilePath($destinationBasepath, $filename, $dirname), $output);
    }


    public function getOriginTemplatePath(string $originBasepath, string $filename, string $dirname = null) :string
    {
        $templateFile = $originBasepath;
        if ($dirname) {
            $templateFile .=  DIRECTORY_SEPARATOR . $dirname;
        }
        $templateFile .= DIRECTORY_SEPARATOR . $filename;
        return $templateFile;
    }

    public function getDestinationFilePath(string $basepath, string $filename, string $dirname = null) :string
    {
        $path = $this->getDestinationBasepath($basepath);
        $destinationFile = $path;
        if ($dirname) {
            $destinationFile .=  DIRECTORY_SEPARATOR . $dirname;
        }
        $destinationFile .= DIRECTORY_SEPARATOR . $filename;
        return $destinationFile;
    }

    public function getDestinationBasepath(string $basepath) :string
    {
        $subPaths = [
            $this->getMagentoPath('app'),
            $basepath, // 'code' or 'design'.DIRECTORY_SEPARATOR.'frontend' + vendor + name
        ];
        $finalBasepath = implode(DIRECTORY_SEPARATOR, $subPaths);
        return $finalBasepath;
    }



}
