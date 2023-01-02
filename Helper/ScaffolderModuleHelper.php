<?php

namespace Codever\Scaffolder\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Style\SymfonyStyle;
use Magento\Framework\App\Helper\Context;

class ScaffolderModuleHelper extends AbstractHelper
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

    const SLEEP_TIME = 0.05;

    private $dir;
    private $moduleName;
    private $vendorName;
    private $shell;
    private $fileHelper;

    public function __construct(
        Context $context,
        ScaffolderFileHelper $fileHelper
    ) {
        $this->moduleName = '';
        $this->vendorName = '';
        $this->shell = null;
        $this->fileHelper = $fileHelper;
        parent::__construct($context);
    }

    public function startCommand(SymfonyStyle $shell)
    {
        $this->shell = $shell;
        if ($this->validate()) {
            $this->doJob();
            $this->shell->success('Your new module is ready at ' . $this->getModuleBasepath());
        }
    }

    public function validate()
    {
        $vendorName = $this->shell->ask('Your module Vendor name:', '');
        $vendorName = $this->sanitizeModuleName($vendorName);
        if (empty($vendorName)) {
            $this->shell->warning('Cannot create a vendor with empty name');
            return false;
        }
        $this->vendorName = ucfirst(strtolower($vendorName));
        $question = new Question('Your new Module name:', '');
        $moduleName = $this->shell->ask('Your new Module name:', '');
        $moduleName = $this->sanitizeModuleName($moduleName);
        if (empty($moduleName)) {
            $this->shell->warning('Cannot create a new module with empty name');
            return false;
        }
        $this->moduleName = ucfirst(strtolower($moduleName));
        $res = $this->getStatus();
        $this->shell->table($res['header'], $res['body']);

        if (!$this->shell->confirm('Do you wish to continue?', 'Y')) {
            $this->shell->warning('Exiting without creating module...');
            return false;
        }
        $this->shell->text('Creating module...');
        return true;
    }

    public function sanitizeModuleName(string $name): string
    {
        return preg_replace("/[^A-Za-z]+/", "", $name);
    }

    protected function getStatus()
    {
        $moduleFinalPath = $this->getModuleBasepath() . DIRECTORY_SEPARATOR . $this->vendorName.'_'.$this->moduleName;
        return [
            "header" => ['Your data', ''],
            "body" => [
                ['vendor', $this->vendorName],
                ['module', $this->moduleName],
                ['path', $moduleFinalPath],
            ]
        ];
    }

    protected function doJob()
    {
        if ($this->checkBeforeGeneratingModule()) {
            $this->generateModuleDirectoriesAndFiles();
        }
    }

    protected function checkBeforeGeneratingModule()
    {
        $moduleBasepath = $this->getModuleBasepath();
        if (file_exists($moduleBasepath)) {
            if (!is_readable($moduleBasepath)) {
                $this->shell->warning("the directory $moduleBasepath already exists and is not readable.");
                return false;
            }
            $di = new \RecursiveDirectoryIterator($moduleBasepath, \FilesystemIterator::SKIP_DOTS);
            if (iterator_count($di) !== 0) {
                $this->shell->warning("the directory $moduleBasepath already exists and is not empty.");
                return false;
            }
            if (!is_writable($moduleBasepath)) {
                $this->shell->warning("the directory $moduleBasepath already exists and is not writable.");
                return false;
            }
        }
        return true;
    }

    protected function generateModuleDirectoriesAndFiles()
    {
        $data = $this->prepareModuleDirectoriesAndFiles();
        $countDirs = count($data['directories']);
        $countFiles = count($data['files']);
        $this->shell->progressStart($countDirs + 1 + $countFiles);
        $this->generateModuleDirectory(); // first dir is the module's dir
        foreach ($data['directories'] as $obj) {
            if (property_exists($obj, 'name') && $obj->name) {
                $this->generateModuleDirectory($obj->name);
            }
        }
        foreach ($data['files'] as $obj) {
            if (property_exists($obj, 'name') && $obj->name) {
                if (property_exists($obj, 'directory') && $obj->directory) {
                    $this->generateModuleFile($obj->name, $obj->directory);
                } else {
                    $this->generateModuleFile($obj->name);
                }
            }
        }
        $this->shell->progressFinish();
    }

    protected function prepareModuleDirectoriesAndFiles()
    {
        $data = [];
        $data["directories"] = [];
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_API);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_BLOCK);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_CONTROLLER_ADMINHTML_SAMPLE);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_CONTROLLER_SAMPLE);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_CRON);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_ETC_ADMINHTML);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_ETC_FRONTEND);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_HELPER);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_I18N);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_MAIL);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_MODEL_RESOURCEMODEL_SAMPLE);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_OBSERVER);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_PLUGIN);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_SETUP);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_TEST_UNIT);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_UI);
        $data["directories"][] = $this->generateDirectoryObject(self::DIRECTORY_VIEW);

        $data["files"] = [];
        $data["files"][] = $this->generateFileObject(self::FILE_COMPOSER);
        $data["files"][] = $this->generateFileObject(self::FILE_README);
        $data["files"][] = $this->generateFileObject(self::FILE_REGISTRATION);
        $data["files"][] = $this->generateFileObject(self::FILE_INDEX, self::DIRECTORY_CONTROLLER_ADMINHTML_SAMPLE);
        $data["files"][] = $this->generateFileObject(self::FILE_INDEX, self::DIRECTORY_CONTROLLER_SAMPLE);
        $data["files"][] = $this->generateFileObject(self::FILE_MODULE, self::DIRECTORY_ETC);
        $data["files"][] = $this->generateFileObject(self::FILE_ACL, self::DIRECTORY_ETC);
        $data["files"][] = $this->generateFileObject(self::FILE_ROUTES, self::DIRECTORY_ETC_ADMINHTML);
        $data["files"][] = $this->generateFileObject(self::FILE_MENU, self::DIRECTORY_ETC_ADMINHTML);
        $data["files"][] = $this->generateFileObject(self::FILE_ROUTES, self::DIRECTORY_ETC_FRONTEND);
        $data["files"][] = $this->generateFileObject(self::FILE_COLLECTION, self::DIRECTORY_MODEL_RESOURCEMODEL_SAMPLE);
        $data["files"][] = $this->generateFileObject(self::FILE_SAMPLE_RESOURCEMODEL, self::DIRECTORY_MODEL_RESOURCEMODEL);
        $data["files"][] = $this->generateFileObject(self::FILE_SAMPLE_MODEL, self::DIRECTORY_MODEL);
        $data["files"][] = $this->generateFileObject(self::FILE_SAMPLE_TEST, self::DIRECTORY_TEST_UNIT);

        return $data;
    }

    public function generateDirectoryObject($name)
    {
        $obj = new \stdClass();
        $obj->name = $name;
        return $obj;
    }

    public function generateFileObject($name, $directory = null)
    {
        $obj = new \stdClass();
        $obj->name = $name;
        if (!is_null($directory)) {
            $obj->directory = $directory;
        }
        return $obj;
    }

    public function generateModuleDirectory(string $dirname = null)
    {
        $path = $this->getModuleBasepath();
        if ($dirname) {
            $path .= DIRECTORY_SEPARATOR . $dirname;
        }
        $this->fileHelper->createDirIfNotExists($path);
        if ($this->shell) {
            $this->shell->progressAdvance();
            $this->msleep(self::SLEEP_TIME);
        }
    }

    protected function generateModuleFile(string $filename, string $dirname = null, string $templateExtension = '.phtml')
    {
        $templateFile = $filename.$templateExtension;
        $template = $this->getTemplatePath($templateFile, $dirname);
        $data = $this->prepareContent();
        $output = $this->fileHelper->render($template, $data);
        $this->fileHelper->write($this->getDestinationFilePath($filename, $dirname), $output);
        if ($this->shell) {
            $this->shell->progressAdvance();
            $this->msleep(self::SLEEP_TIME);
        }
    }

    protected function getModuleBasepath() :string
    {
        $subPaths = [
            $this->fileHelper->getMagentoPath('app'),
            'code',
            $this->vendorName,
            $this->moduleName
        ];
        $moduleBasepath = implode(DIRECTORY_SEPARATOR, $subPaths);
        return $moduleBasepath;
    }

    protected function getTemplatePath(string $filename, string $dirname = null) :string
    {
        $modulePath = $this->fileHelper->getModulePath('Codever_Scaffolder');
        $subPaths = [
            $modulePath,
            'templates'
        ];
        $moduleBasepath = implode(DIRECTORY_SEPARATOR, $subPaths);
        $templateFile = $moduleBasepath;
        if ($dirname) {
            $templateFile .=  DIRECTORY_SEPARATOR . $dirname;
        }
        $templateFile .= DIRECTORY_SEPARATOR . $filename;
        return $templateFile;
    }

    protected function getDestinationFilePath(string $filename, string $dirname = null) :string
    {
        $path = $this->getModuleBasepath();
        $destinationFile = $path;
        if ($dirname) {
            $destinationFile .=  DIRECTORY_SEPARATOR . $dirname;
        }
        $destinationFile .= DIRECTORY_SEPARATOR . $filename;
        return $destinationFile;
    }

    protected function msleep($time)
    {
        usleep((int)($time * 1000000));
    }

    protected function prepareContent(string $template = null)
    {
        $data = [];
        $data['vendor'] = $this->vendorName;
        $data['module'] = $this->moduleName;
        return $data;
    }
}
