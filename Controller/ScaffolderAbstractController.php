<?php

namespace Codever\Scaffolder\Controller;

use Symfony\Component\Console\Style\SymfonyStyle;
use Magento\Framework\App\Helper\Context;
use Codever\Scaffolder\Helper\ScaffolderFileHelper;
use Codever\Scaffolder\Model\ScaffolderOperationModel;

abstract class ScaffolderAbstractController
{

    const OPERATION_DIRECTORY_NEW = 'scaffolder:directory:new';
    const OPERATION_FILE_NEW = 'scaffolder:file:new';
    const SLEEP_TIME = 0.05;


    protected $fileHelper;
    protected $operations;
    protected $shell;
    protected $extensionName;
    protected $vendorName;

    public function __construct(
        ScaffolderFileHelper $fileHelper
    ) {
        $this->extensionName = '';
        $this->vendorName = '';
        $this->shell = null;
        $this->fileHelper = $fileHelper;
        $this->operations = [];
    }

    public function execute(SymfonyStyle $shell)
    {
        $this->shell = $shell;
        if ($this->validate() && $this->check()) {
            $this->prepare();
            $res = $this->do();
            if($res){
              $this->success();
              return;
            }
        }
        $this->failure();
    }

    public function validate()
    {
        $vendorName = $this->askName(static::SCAFFOLDER_TYPE, 'vendor');
        if($vendorName){
            $extensionName = $this->askName(static::SCAFFOLDER_TYPE, 'name');
            if($extensionName){
                $this->vendorName = ucfirst(strtolower($vendorName));
                $this->extensionName = ucfirst(strtolower($extensionName));
                $res = $this->showRecap();
                $this->shell->table($res['header'], $res['body']);
                if (!$this->shell->confirm('Do you wish to continue?', 'Y')) {
                    $this->shell->warning('Exiting without creating ' . static::SCAFFOLDER_TYPE . '...');
                    return false;
                }
                $this->shell->text('Creating ' . static::SCAFFOLDER_TYPE . '...');
                return true;
            }
        }
        return false;
    }


    public function check()
    {
        $destinationBasepath = $this->getDestinationBasepath();
        if (file_exists($destinationBasepath)) {
            if (!is_readable($destinationBasepath)) {
                $this->shell->warning("the directory $destinationBasepath already exists and is not readable.");
                return false;
            }
            $di = new \RecursiveDirectoryIterator($destinationBasepath, \FilesystemIterator::SKIP_DOTS);
            if (iterator_count($di) !== 0) {
                $this->shell->warning("the directory $destinationBasepath already exists and is not empty.");
                return false;
            }
            if (!is_writable($destinationBasepath)) {
                $this->shell->warning("the directory $destinationBasepath already exists and is not writable.");
                return false;
            }
        }
        return true;
    }

    public function prepare()
    {
        $this->prepareDestinationDirectories();
        $this->prepareDestinationFiles();
    }


    protected function createDestinationFileContent()
    {
        $data = [];
        $data['vendor'] = $this->vendorName;
        $data['extension'] = $this->extensionName;
        return $data;
    }


    public function do() :bool
    {
        try {
            $totalOperations = count($this->operations);
            $this->shell->progressStart($totalOperations);
            foreach($this->operations as $op){
                $this->doOperation($op);
            }
            $this->shell->progressFinish();
            return true;
        } catch(\Exception $e) {
            $this->shell->error($e->getMessage());
            return false;
        }
    }

    public function doOperation($op)
    {
        switch($op->getAction()) {
            case self::OPERATION_DIRECTORY_NEW:
                if ($op->getArg('name')){
                    $this->generateDestinationDirectory($op->getArg('name'));
                }
            break;
            case self::OPERATION_FILE_NEW:
                if ($op->getArg('name')) {
                    $data = $this->createDestinationFileContent();
                    if ($op->getArg('directory')) {
                        $this->generateDestinationFile($op->getArg('name'), $data, $op->getArg('directory'));
                    } else {
                        $this->generateDestinationFile($op->getArg('name'), $data);
                    }
                    $this->advanceProgress();
                }
            break;
        }
    }

    protected function showRecap()
    {
        $destinationFinalPath = $this->getDestinationBasepath();
        return [
            "header" => ['Your data', ''],
            "body" => [
                ['vendor', $this->vendorName],
                ['extension', $this->extensionName],
                ['path', $destinationFinalPath],
            ]
        ];
    }
    public function success()
    {
        $this->shell->success('The command has been executed successfully');
    }

    public function failure()
    {
        $this->shell->error('An error occurred during the command run');
    }

    public function askName($type, $name, $default='')
    {
        $vendorName = $this->shell->ask('Your ' . $type . ' ' . $name . ' name:', $default);
        $vendorName = $this->sanitizeExtensionName($vendorName);
        if (empty($vendorName)) {
            $this->shell->warning('Cannot create a ' . $type . ' ' . $name . ' with empty name');
            return false;
        }
        return $vendorName;
    }

    public function sanitizeExtensionName(string $name) :string
    {
        return preg_replace("/[^A-Za-z]+/", "", $name);
    }

    public function advanceProgress()
    {
        if ($this->shell) {
            $this->shell->progressAdvance();
            $this->msleep(self::SLEEP_TIME);
        }
    }

    protected function msleep($time)
    {
        usleep((int)($time * 1000000));
    }

    public function createOperation($action, $args)
    {
        return new ScaffolderOperationModel($action, $args);
    }

    public function addOperation($op)
    {
        $this->operations[] = $op;
    }

    public function generateDestinationDirectory($name)
    {
        $basepath = $this->getDestinationAppPath();
        $this->fileHelper->generateDestinationDirectory($basepath, $name);
    }

    public function generateDestinationFile($fileName, $data, $directory = null)
    {
        $originBasepath = $this->getTemplateBasepath();
        $destinationBasepath = $this->getDestinationAppPath();
        $this->fileHelper->generateDestinationFile($originBasepath, $destinationBasepath, $fileName, $data, $directory);
    }

    public function getDestinationAppPath(){
        return 'code' . DIRECTORY_SEPARATOR . $this->vendorName . DIRECTORY_SEPARATOR . $this->extensionName;
    }

    public function getDestinationBasepath(){
        $basepath = $this->getDestinationAppPath();
        return $this->fileHelper->getDestinationBasepath($basepath);
    }

    public function generateDirectoryOperation($name = null)
    {
        $args = ['name'=>$name];
        $op = $this->createOperation(self::OPERATION_DIRECTORY_NEW, $args);
        $this->addOperation($op);
    }

    public function generateFileOperation($name, $directory = null)
    {
        $args = ['name'=>$name];
        if (!is_null($directory)) {
            $args['directory'] = $directory;
        }
        $op = $this->createOperation(self::OPERATION_FILE_NEW, $args);
        $this->addOperation($op);
    }

    public function getTemplateBasepath()
    {
        $originPath = $this->fileHelper->getModulePath('Codever_Scaffolder');
        $subPaths = [
            $originPath,
            'templates'
        ];
        return implode(DIRECTORY_SEPARATOR, $subPaths);
    }
}
