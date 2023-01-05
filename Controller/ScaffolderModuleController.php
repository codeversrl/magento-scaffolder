<?php

namespace Codever\Scaffolder\Controller;

use Codever\Scaffolder\Helper\ScaffolderFileHelper;
use Codever\Scaffolder\Controller\ScaffolderAbstractController;

class ScaffolderModuleController extends ScaffolderAbstractController
{

    public const DIRECTORY_API = 'Api';
    public const DIRECTORY_BLOCK = 'Block';
    public const DIRECTORY_CONTROLLER = 'Controller';
    public const DIRECTORY_CONTROLLER_ADMINHTML = self::DIRECTORY_CONTROLLER . DIRECTORY_SEPARATOR . 'Adminhtml';
    // phpcs:ignore
    public const DIRECTORY_CONTROLLER_ADMINHTML_SAMPLE = self::DIRECTORY_CONTROLLER_ADMINHTML . DIRECTORY_SEPARATOR . 'Sample';
    public const DIRECTORY_CONTROLLER_SAMPLE = self::DIRECTORY_CONTROLLER . DIRECTORY_SEPARATOR . 'Sample';
    public const DIRECTORY_CRON = 'Cron';
    public const DIRECTORY_ETC = 'etc';
    public const DIRECTORY_ETC_ADMINHTML = self::DIRECTORY_ETC . DIRECTORY_SEPARATOR . 'adminhtml';
    public const DIRECTORY_ETC_FRONTEND = self::DIRECTORY_ETC . DIRECTORY_SEPARATOR . 'frontend';
    public const DIRECTORY_HELPER = 'Helper';
    public const DIRECTORY_I18N = 'i18n';
    public const DIRECTORY_MAIL = 'Mail';
    public const DIRECTORY_MODEL = 'Model';
    public const DIRECTORY_MODEL_RESOURCEMODEL = self::DIRECTORY_MODEL . DIRECTORY_SEPARATOR . 'ResourceModel';
    // phpcs:ignore
    public const DIRECTORY_MODEL_RESOURCEMODEL_SAMPLE = self::DIRECTORY_MODEL_RESOURCEMODEL . DIRECTORY_SEPARATOR . 'Sample';
    public const DIRECTORY_OBSERVER = 'Observer';
    public const DIRECTORY_PLUGIN = 'Plugin';
    public const DIRECTORY_SETUP = 'Setup';
    public const DIRECTORY_TEST_UNIT = 'Test'.DIRECTORY_SEPARATOR.'Unit';
    public const DIRECTORY_UI = 'UI';
    public const DIRECTORY_VIEW = 'view';

    public const FILE_COMPOSER = 'composer.json';
    public const FILE_README = 'README.md';
    public const FILE_REGISTRATION = 'registration.php';
    public const FILE_INDEX = 'Index.php';
    public const FILE_COLLECTION = 'Collection.php';
    public const FILE_SAMPLE_RESOURCEMODEL = 'SampleResourceModel.php';
    public const FILE_SAMPLE_MODEL = 'SampleModel.php';
    public const FILE_SAMPLE_TEST = 'SampleTest.php';
    public const FILE_MODULE = 'module.xml';
    public const FILE_ACL = 'acl.xml';
    public const FILE_ROUTES = 'routes.xml';
    public const FILE_MENU = 'menu.xml';

    public const SCAFFOLDER_TYPE = 'module';

    /**
     * Generates operations to write all needed folders
     *
     * @return void
     */
    public function prepareDestinationDirectories(): void
    {
        $this->generateDirectoryOperation();
        $this->generateDirectoryOperation(self::DIRECTORY_API);
        $this->generateDirectoryOperation(self::DIRECTORY_BLOCK);
        $this->generateDirectoryOperation(self::DIRECTORY_CONTROLLER_ADMINHTML_SAMPLE);
        $this->generateDirectoryOperation(self::DIRECTORY_CONTROLLER_SAMPLE);
        $this->generateDirectoryOperation(self::DIRECTORY_CRON);
        $this->generateDirectoryOperation(self::DIRECTORY_ETC_ADMINHTML);
        $this->generateDirectoryOperation(self::DIRECTORY_ETC_FRONTEND);
        $this->generateDirectoryOperation(self::DIRECTORY_HELPER);
        $this->generateDirectoryOperation(self::DIRECTORY_I18N);
        $this->generateDirectoryOperation(self::DIRECTORY_MAIL);
        $this->generateDirectoryOperation(self::DIRECTORY_MODEL_RESOURCEMODEL_SAMPLE);
        $this->generateDirectoryOperation(self::DIRECTORY_OBSERVER);
        $this->generateDirectoryOperation(self::DIRECTORY_PLUGIN);
        $this->generateDirectoryOperation(self::DIRECTORY_SETUP);
        $this->generateDirectoryOperation(self::DIRECTORY_TEST_UNIT);
        $this->generateDirectoryOperation(self::DIRECTORY_UI);
        $this->generateDirectoryOperation(self::DIRECTORY_VIEW);
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
        $this->generateFileOperation(self::FILE_INDEX, self::DIRECTORY_CONTROLLER_ADMINHTML_SAMPLE);
        $this->generateFileOperation(self::FILE_INDEX, self::DIRECTORY_CONTROLLER_SAMPLE);
        $this->generateFileOperation(self::FILE_MODULE, self::DIRECTORY_ETC);
        $this->generateFileOperation(self::FILE_ACL, self::DIRECTORY_ETC);
        $this->generateFileOperation(self::FILE_ROUTES, self::DIRECTORY_ETC_ADMINHTML);
        $this->generateFileOperation(self::FILE_MENU, self::DIRECTORY_ETC_ADMINHTML);
        $this->generateFileOperation(self::FILE_ROUTES, self::DIRECTORY_ETC_FRONTEND);
        $this->generateFileOperation(self::FILE_COLLECTION, self::DIRECTORY_MODEL_RESOURCEMODEL_SAMPLE);
        $this->generateFileOperation(self::FILE_SAMPLE_RESOURCEMODEL, self::DIRECTORY_MODEL_RESOURCEMODEL);
        $this->generateFileOperation(self::FILE_SAMPLE_MODEL, self::DIRECTORY_MODEL);
        $this->generateFileOperation(self::FILE_SAMPLE_TEST, self::DIRECTORY_TEST_UNIT);
    }

    /**
     * The subpath from "app" Magento path where to store new extension files
     *
     * @return string
     */
    public function getDestinationAppPath(): string
    {
        return 'code' . DIRECTORY_SEPARATOR . $this->vendorName . DIRECTORY_SEPARATOR . $this->extensionName;
    }

    /**
     * Overrides parent method to specify the right template folder for module creation
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
        $this->shell->success('Your new module has been successfully created at ' . $destinationFinalPath);
    }
}
