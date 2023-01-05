<?php

namespace Codever\Scaffolder\Controller;

use Codever\Scaffolder\Helper\ScaffolderFileHelper;
use Codever\Scaffolder\Controller\ScaffolderAbstractController;

class ScaffolderAdminhtmlThemeController extends ScaffolderAbstractController
{

    public const DIRECTORY_ETC = 'etc';
    public const DIRECTORY_LAYOUT = 'layout';
    public const DIRECTORY_WEB = 'web';
    public const DIRECTORY_WEB_CSS = self::DIRECTORY_WEB . DIRECTORY_SEPARATOR . 'css';
    public const DIRECTORY_WEB_CSS_SOURCE = self::DIRECTORY_WEB_CSS . DIRECTORY_SEPARATOR . 'source';
    public const DIRECTORY_WEB_FONTS = self::DIRECTORY_WEB . DIRECTORY_SEPARATOR . 'fonts';
    public const DIRECTORY_WEB_IMAGES = self::DIRECTORY_WEB . DIRECTORY_SEPARATOR . 'images';
    public const DIRECTORY_WEB_JS = self::DIRECTORY_WEB . DIRECTORY_SEPARATOR . 'js';

    public const FILE_COMPOSER = 'composer.json';
    public const FILE_README = 'README.md';
    public const FILE_REGISTRATION = 'registration.php';
    public const FILE_THEME = 'theme.xml';

    public const SCAFFOLDER_TYPE = 'adminhtml';

    /**
     * Generates operations to write all needed folders
     *
     * @return void
     */
    public function prepareDestinationDirectories(): void
    {
        $this->generateDirectoryOperation();
        $this->generateDirectoryOperation(self::DIRECTORY_ETC);
        $this->generateDirectoryOperation(self::DIRECTORY_LAYOUT);
        $this->generateDirectoryOperation(self::DIRECTORY_WEB_CSS_SOURCE);
        $this->generateDirectoryOperation(self::DIRECTORY_WEB_FONTS);
        $this->generateDirectoryOperation(self::DIRECTORY_WEB_IMAGES);
        $this->generateDirectoryOperation(self::DIRECTORY_WEB_JS);
    }

    /**
     * Generates operations to write all needed files
     *
     * @return void
     */
    public function prepareDestinationFiles(): void
    {
        $this->generateFileOperation(self::FILE_COMPOSER);
        $this->generateFileOperation(self::FILE_README);
        $this->generateFileOperation(self::FILE_REGISTRATION);
        $this->generateFileOperation(self::FILE_THEME);
    }

    /**
     * The subpath from "app" Magento path where to store new extension files
     *
     * @return string
     */
    public function getDestinationAppPath(): string
    {
        // phpcs:ignore
        return 'design' . DIRECTORY_SEPARATOR . 'adminhtml' . DIRECTORY_SEPARATOR . $this->vendorName . DIRECTORY_SEPARATOR . $this->extensionName;
    }

    /**
     * Overrides parent method to specify the right template folder for adminhtml theme creation
     *
     * @return string
     */
    public function getTemplateBasepath(): string
    {
        $originPath = $this->fileHelper->getModulePath('Codever_Scaffolder');
        $subPaths = [
            $originPath,
            'templates',
            self::SCAFFOLDER_TYPE
        ];
        return implode(DIRECTORY_SEPARATOR, $subPaths);
    }

    /**
     * Displays a final success message to the user
     *
     * @return void
     */
    public function success(): void
    {
        $destinationFinalPath = $this->getDestinationBasepath();
        $this->shell->success('Your new adminhtml theme has been successfully created at ' . $destinationFinalPath);
    }
}
