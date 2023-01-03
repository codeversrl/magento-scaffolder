<?php

namespace Codever\Scaffolder\Controller;

use Symfony\Component\Console\Style\SymfonyStyle;
use Magento\Framework\App\Helper\Context;

abstract class ScaffolderAbstractController
{

    const SLEEP_TIME = 0.05;

    protected $fileHelper;
    protected $operations;
    private $shell;
    private $extensionName;
    private $vendorName;

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
        $vendorName = $this->askName(self::SCAFFOLDER_TYPE, 'vendor');
        if($vendorName){
            $extensionName = $this->askName(self::SCAFFOLDER_TYPE, 'name');
            if($extensionName){
                $this->vendorName = ucfirst(strtolower($vendorName));
                $this->extensionName = ucfirst(strtolower($extensionName));
                $res = $this->getStatus();
                $this->shell->table($res['header'], $res['body']);
                if (!$this->shell->confirm('Do you wish to continue?', 'Y')) {
                    $this->shell->warning('Exiting without creating ' . self::SCAFFOLDER_TYPE . '...');
                    return false;
                }
                $this->shell->text('Creating ' . self::SCAFFOLDER_TYPE . '...');
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


    protected function createDestinationFileContent(string $template = null)
    {
        $data = [];
        $data['vendor'] = $this->vendorName;
        $data['extension'] = $this->extensionName;
        return $data;
    }


    public function do(): boolval
    {
        try {
            $totalOperations = count($this->operations);
            $this->shell->progressStart($totalOperations);
            foreach($this->operations as $op){
                $this->doOperation($op);
            }
            $this->shell->progressFinish();
            return true;
        } catch(\Exception e){
            $this->shell->error($e->getMessage());
            return false;
        }
    }

    public function doOperation($op)
    {
        switch($op->action) {
            case 'scaffolder:directory:new':
                if (property_exists($op, 'name')){
                    $this->generateDestinationDirectory($op->name);
                }
            break;
            case 'scaffolder:file:new':
                if (property_exists($op, 'name') && $op->name) {
                    if (property_exists($op, 'directory') && $op->directory) {
                        $this->generateDestinationFile($op->name, $op->directory);
                    } else {
                        $this->generateDestinationFile($op->name);
                    }
                    $this->advanceProgress();
                }
            break;
        }
    }

    protected function showRecap()
    {
        $destinationFinalPath = $this->getDestinationBasepath() . DIRECTORY_SEPARATOR . $this->vendorName.'_'.$this->extensionName;
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
        return true;
    }

    public function sanitizeExtensionName(string $name): string
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

    public function doOperation($op)
    {
        return true;
    }

    public function createOperation($action, $args)
    {
        return new ScaffolderOperationModule($action, $args);

    }

    public function addOperation($op)
    {
        $this->operation[] = $op;
    }

    public function generateDestinationDirectory($name)
    {
        $basepath = $this->getInnerPath();
        $this->fileHelper->generateDestinationDirectory($basepath, $name);
    }

    public function generateDestinationFile($name, $directory = null)
    {
        $basepath = $this->getInnerPath();
        $this->fileHelper->generateDestinationFile($basepath, $name, $directory);
    }

    public function getInnerPath(){
        switch(self::SCAFFOLDER_TYPE){
            case 'module':
                return 'code';
            case 'frontend':
                return 'design' . DIRECTORY_SEPARATOR . 'frontend';
        }
    }

    public function getDestinationBasepath(){
        $basepath = $this->getInnerPath();
        $this->fileHelper->getDestinationBasepath($basepath, $this->vendorName, $this->extensionName);
    }
}
