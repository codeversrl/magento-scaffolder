<?php

namespace Codever\Scaffolder\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
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
    const DIRECTORY_CRON = 'Cron';
    const DIRECTORY_ETC = 'etc';
    const DIRECTORY_HELPER = 'Helper';
    const DIRECTORY_I18N = 'i18n';
    const DIRECTORY_MAIL = 'Mail';
    const DIRECTORY_MODEL = 'Model';
    const DIRECTORY_OBSERVER = 'Observer';
    const DIRECTORY_PLUGIN = 'Plugin';
    const DIRECTORY_SETUP = 'Setup';
    const DIRECTORY_TEST = 'Test'.DIRECTORY_SEPARATOR.'Unit';
    const DIRECTORY_UI = 'UI';
    const DIRECTORY_VIEW = 'view';

    const FILE_COMPOSER = 'composer.json';
    const FILE_README = 'README.md';
    const FILE_REGISTRATION = 'registration.php';
    const FILE_MODULE = 'module.xml';

    private $dir;
    private $moduleName;
    private $vendorName;
    private $input;
    private $output;
    private $shellStyle;
    private $fileHelper;

    public function __construct(
        Context $context,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        ScaffolderFileHelper $fileHelper
    ) {
        $this->moduleName = '';
        $this->vendorName = '';
        $this->shellStyle = null;
        $this->dir = $dir;
        $this->fileHelper = $fileHelper;
        parent::__construct($context);
    }

    public function startCommand(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        if ($this->validate()) {
            $this->doJob();
        }
    }

    protected function validate()
    {
        $helper = new QuestionHelper();
        $question = new Question('Your module Vendor name:', '');
        $vendorName = $helper->ask($this->input, $this->output, $question);
        $vendorName = preg_replace("/[^A-Za-z]+/", "", $vendorName);
        if (empty($vendorName)) {
            $this->output->writeln('Cannot create a vendor with empty name');
            return false;
        }
        $this->vendorName = ucfirst(strtolower($vendorName));
        $question = new Question('Your new Module name:', '');
        $moduleName = $helper->ask($this->input, $this->output, $question);
        $moduleName = preg_replace("/[^A-Za-z]+/", "", $moduleName);
        if (empty($moduleName)) {
            $this->output->writeln('Cannot create a new module with empty name');
            return false;
        }
        $this->moduleName = ucfirst(strtolower($moduleName));
        $res = $this->getStatus();
        $this->output->writeln($res);
        $question = new ConfirmationQuestion('Do you wish to continue?', true);
        if (!$helper->ask($this->input, $this->output, $question)) {
            $this->output->writeln('Exiting without creating module...');
            return false;
        }
        $this->output->writeln('Creating module...');
        return true;
    }

    protected function getStatus()
    {
        $moduleFinalPath = $this->getModuleBasepath() . DIRECTORY_SEPARATOR . $this->vendorName.'_'.$this->moduleName;
        $str = <<<EOD


======================================
Review your data:
======================================
vendor: $this->vendorName
module: $this->moduleName
path: $moduleFinalPath
--------------------------------------

EOD;
        return $str;
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
                $this->output->writeln("the directory $moduleBasepath already exists and is not readable.");
                return false;
            }
            $di = new \RecursiveDirectoryIterator($moduleBasepath, \FilesystemIterator::SKIP_DOTS);
            if (iterator_count($di) !== 0) {
                $this->output->writeln("the directory $moduleBasepath already exists and is not empty.");
                return false;
            }
            if (!is_writable($moduleBasepath)) {
                $this->output->writeln("the directory $moduleBasepath already exists and is not writable.");
                return false;
            }
        }
        return true;
    }

    protected function generateModuleDirectoriesAndFiles()
    {


        $this->shellStyle = new SymfonyStyle($this->input, $this->output);
        $this->shellStyle->progressStart(100);
        $this->generateModuleDirectory(null);
        $this->generateModuleDirectory(self::DIRECTORY_API);
        $this->generateModuleDirectory(self::DIRECTORY_BLOCK);
        $this->generateModuleDirectory(self::DIRECTORY_CONTROLLER);
        $this->generateModuleDirectory(self::DIRECTORY_CRON);
        $this->generateModuleDirectory(self::DIRECTORY_ETC);
        $this->generateModuleDirectory(self::DIRECTORY_HELPER);
        $this->generateModuleDirectory(self::DIRECTORY_I18N);
        $this->generateModuleDirectory(self::DIRECTORY_MAIL);
        $this->generateModuleDirectory(self::DIRECTORY_MODEL);
        $this->generateModuleDirectory(self::DIRECTORY_OBSERVER);
        $this->generateModuleDirectory(self::DIRECTORY_PLUGIN);
        $this->generateModuleDirectory(self::DIRECTORY_SETUP);
        $this->generateModuleDirectory(self::DIRECTORY_TEST);
        $this->generateModuleDirectory(self::DIRECTORY_UI);
        $this->generateModuleDirectory(self::DIRECTORY_VIEW);
        $this->generateModuleFile(self::FILE_COMPOSER);
        $this->generateModuleFile(self::FILE_README);
        $this->generateModuleFile(self::FILE_REGISTRATION);
        $this->generateModuleFile(self::FILE_MODULE, self::DIRECTORY_ETC);
        $this->shellStyle->progressFinish();
    }

    protected function generateModuleDirectory(string $dirname = null)
    {
        $path = $this->getModuleBasepath();
        if ($dirname) {
            $path .= DIRECTORY_SEPARATOR . $dirname;
        }
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        if ($this->shellStyle) {
            $this->shellStyle->progressAdvance();
            $this->msleep(0.5);
        }
    }

    protected function generateModuleFile(string $filename, string $dirname = null, string $templateExtension = '.phtml')
    {
        $templateFile = $filename.$templateExtension;
        $template = $this->getTemplatePath($templateFile, $dirname);
        $data = $this->prepareContent();
        $output = $this->fileHelper->render($template, $data);
        $this->fileHelper->write($this->getDestinationFilePath($filename, $dirname), $output);
        if ($this->shellStyle) {
            $this->shellStyle->progressAdvance();
            $this->msleep(0.5);
        }
    }

    protected function getModuleBasepath() :string
    {
        $subPaths = [
            $this->dir->getPath('app'),
            'code',
            $this->vendorName,
            $this->moduleName
        ];
        $moduleBasepath = implode(DIRECTORY_SEPARATOR, $subPaths);
        return $moduleBasepath;
    }

    protected function getTemplatePath(string $filename, string $dirname = null) :string
    {
        $subPaths = [
            $this->dir->getPath('app'),
            'code',
            'Codever',
            'Scaffolder',
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
