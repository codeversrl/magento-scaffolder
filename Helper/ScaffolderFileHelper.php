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
        )
    {
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

    public function getMagentoPath(string $folderName = null){
        return $this->dir->getPath($folderName);
    }

    public function getModulePath(string $moduleName){
        return $this->moduleDir->getDir($moduleName);
    }
}
