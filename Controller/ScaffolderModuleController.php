<?php

namespace Codever\Scaffolder\Controller;

use Codever\Scaffolder\Helper\ScaffolderFileHelper;
use Codever\Scaffolder\Controller\ScaffolderAbstractController;

class ScaffolderModuleController extends ScaffolderAbstractController
{

    const DIRECTORY_API = 'Api';
    const DIRECTORY_BLOCK = 'Block';
    const DIRECTORY_CONTROLLER = 'Controller';
    const DIRECTORY_CONTROLLER_ADMINHTML = self::DIRECTORY_CONTROLLER . DIRECTORY_SEPARATOR . 'Adminhtml';
    const DIRECTORY_CONTROLLER_ADMINHTML_SAMPLE = self::DIRECTORY_CONTROLLER_ADMINHTML . DIRECTORY_SEPARATOR . 'Sample';
    const DIRECTORY_CONTROLLER_SAMPLE = self::DIRECTORY_CONTROLLER . DIRECTORY_SEPARATOR . 'Sample';
    const DIRECTORY_CRON = 'Cron';
    const DIRECTORY_ETC = 'etc';
    const DIRECTORY_ETC_ADMINHTML = self::DIRECTORY_ETC . DIRECTORY_SEPARATOR . 'adminhtml';
    const DIRECTORY_ETC_FRONTEND = self::DIRECTORY_ETC . DIRECTORY_SEPARATOR . 'frontend';
    const DIRECTORY_HELPER = 'Helper';
    const DIRECTORY_I18N = 'i18n';
    const DIRECTORY_MAIL = 'Mail';
    const DIRECTORY_MODEL = 'Model';
    const DIRECTORY_MODEL_RESOURCEMODEL = self::DIRECTORY_MODEL . DIRECTORY_SEPARATOR . 'ResourceModel';
    const DIRECTORY_MODEL_RESOURCEMODEL_SAMPLE = self::DIRECTORY_MODEL_RESOURCEMODEL . DIRECTORY_SEPARATOR . 'Sample';
    const DIRECTORY_OBSERVER = 'Observer';
    const DIRECTORY_PLUGIN = 'Plugin';
    const DIRECTORY_SETUP = 'Setup';
    const DIRECTORY_TEST_UNIT = 'Test'.DIRECTORY_SEPARATOR.'Unit';
    const DIRECTORY_UI = 'UI';
    const DIRECTORY_VIEW = 'view';

    const FILE_COMPOSER = 'composer.json';
    const FILE_README = 'README.md';
    const FILE_REGISTRATION = 'registration.php';
    const FILE_INDEX = 'Index.php';
    const FILE_COLLECTION = 'Collection.php';
    const FILE_SAMPLE_RESOURCEMODEL = 'SampleResourceModel.php';
    const FILE_SAMPLE_MODEL = 'SampleModel.php';
    const FILE_SAMPLE_TEST = 'SampleTest.php';
    const FILE_MODULE = 'module.xml';
    const FILE_ACL = 'acl.xml';
    const FILE_ROUTES = 'routes.xml';
    const FILE_MENU = 'menu.xml';

    const SCAFFOLDER_TYPE = 'module';

    public function __construct(
        ScaffolderFileHelper $fileHelper
    ) {
        parent::__construct($fileHelper);
    }

    public function prepareModuleDirectories()
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

    public function prepareModuleFiles()
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
