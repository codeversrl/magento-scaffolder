<?php

namespace Codever\Scaffolder\Controller;

use Codever\Scaffolder\Helper\ScaffolderFileHelper;
use Codever\Scaffolder\Controller\ScaffolderAbstractController;

class ScaffolderThemeController extends ScaffolderAbstractController
{

    const DIRECTORY_ETC = 'etc';
    const DIRECTORY_LAYOUT = 'layout';
    const DIRECTORY_WEB = 'web';
    const DIRECTORY_WEB_CSS = self::DIRECTORY_WEB . DIRECTORY_SEPARATOR . 'css';
    const DIRECTORY_WEB_CSS_SOURCE = self::DIRECTORY_WEB_CSS . DIRECTORY_SEPARATOR . 'source';
    const DIRECTORY_WEB_FONTS = self::DIRECTORY_WEB . DIRECTORY_SEPARATOR . 'fonts';
    const DIRECTORY_WEB_IMAGES = self::DIRECTORY_WEB . DIRECTORY_SEPARATOR . 'images';
    const DIRECTORY_WEB_JS = self::DIRECTORY_WEB . DIRECTORY_SEPARATOR . 'js';

    const FILE_COMPOSER = 'composer.json';
    const FILE_README = 'README.md';
    const FILE_REGISTRATION = 'registration.php';
    const FILE_THEME = 'theme.xml';

    const SCAFFOLDER_TYPE = 'frontend';

    public function __construct(
        ScaffolderFileHelper $fileHelper
    ) {
        parent::__construct($fileHelper);
    }

    public function prepareModuleDirectories()
    {
        $this->generateDirectoryOperation();
        $this->generateDirectoryOperation(self::DIRECTORY_ETC);
        $this->generateDirectoryOperation(self::DIRECTORY_LAYOUT);
        $this->generateDirectoryOperation(self::DIRECTORY_WEB_CSS_SOURCE);
        $this->generateDirectoryOperation(self::DIRECTORY_WEB_FONTS);
        $this->generateDirectoryOperation(self::DIRECTORY_WEB_IMAGES);
        $this->generateDirectoryOperation(self::DIRECTORY_WEB_JS);
    }

    public function prepareModuleFiles()
    {
        $this->generateFileOperation(self::FILE_COMPOSER);
        $this->generateFileOperation(self::FILE_README);
        $this->generateFileOperation(self::FILE_REGISTRATION);
        $this->generateFileOperation(self::FILE_THEME);
    }

    public function generateDirectoryOperation($name = null)
    {
        $args = ['name'=>$name];
        $op = $this->createOperation('scaffolder:directory:new', $args);
        $this->addOperation($op);
    }

    public function generateFileOperation($name, $directory = null)
    {
        $args = ['name'=>$name];
        if (!is_null($directory)) {
            $args['directory'] = $directory;
        }
        $op = $this->createOperation('scaffolder:file:new', $args);
        $this->addOperation($op);
    }

}
