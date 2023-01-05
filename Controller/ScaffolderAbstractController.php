<?php

namespace Codever\Scaffolder\Controller;

use Symfony\Component\Console\Style\SymfonyStyle;
use Magento\Framework\App\Helper\Context;
use Codever\Scaffolder\Helper\ScaffolderFileHelper;
use Codever\Scaffolder\Model\ScaffolderOperationModel;

/**
 * Base controller extended by scaffolder controllers
 *
 * The controller allows the creation of Magento extensions (modules and themes)
 * so that developers can have a base to start easier and faster.
 * The flow has been made in a way to be overridable and pluggable, the methods sequence
 * is listed below:
 *
 * execute() this is the starting point of the flow, called from scaffolder shell Command
 *
 * validate() validates user data making sure vendor name and extension name are consistent
 *
 * check() checks if filesystem allows the extension creation
 *
 * prepare() generates operations needed to create new extension's directories and files.
 *           The method uses to overridable functions:
 *           - prepareDestinationDirectories() generates all operations to create directories
 *           - prepareDestinationFiles() generates all operations to create files
 *
 * do() executes all operations generated on prepare() step
 *
 * success() displays a final success message to the user
 *
 * failure() displays a final error message to the user
 */
abstract class ScaffolderAbstractController
{

    protected const OPERATION_DIRECTORY_NEW = 'scaffolder:directory:new';
    protected const OPERATION_FILE_NEW = 'scaffolder:file:new';
    private const SLEEP_TIME = 0.05;

    /**
     * Module helper for filesystem operations
     *
     * @var ScaffolderFileHelper
     */
    protected ScaffolderFileHelper $fileHelper;

    /**
     * Array of ScaffolderOperationModel objects
     *
     * @var array
     */
    protected array $operations;

    /**
     * Console helper
     *
     * @var SymfonyStyle
     */
    protected $shell;

    /**
     * The name of the new extension to be created
     *
     * @var string
     */
    protected string $extensionName;

    /**
     * The name of the vendor of the new extension to be created
     *
     * @var string
     */
    protected string $vendorName;

    /**
     * Class constructor
     *
     * @param ScaffolderFileHelper $fileHelper
     * @return void
     */
    public function __construct(
        ScaffolderFileHelper $fileHelper
    ) {
        $this->extensionName = '';
        $this->vendorName = '';
        $this->shell = null;
        $this->fileHelper = $fileHelper;
        $this->operations = [];
    }

    /**
     * Executes the flow to generate the new extension
     *
     * @param  SymfonyStyle $shell
     * @return void
     */
    public function execute(SymfonyStyle $shell): void
    {
        $this->shell = $shell;
        if ($this->validate() && $this->check()) {
            $this->prepare();
            $res = $this->do();
            if ($res) {
                $this->success();
                return;
            }
        }
        $this->failure();
    }

    /**
     * Validates user input
     *
     * @return bool
     */
    public function validate(): bool
    {
        $vendorName = $this->askName(static::SCAFFOLDER_TYPE, 'vendor');
        if ($vendorName) {
            $extensionName = $this->askName(static::SCAFFOLDER_TYPE, 'name');
            if ($extensionName) {
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

    /**
     * Checks filesystem before creating folders and files
     *
     * @return bool
     */
    public function check(): bool
    {
        $destinationBasepath = $this->getDestinationBasepath();
        if ($this->fileHelper->fileExists($destinationBasepath)) {
            if (!$this->fileHelper->fileIsReadable($destinationBasepath)) {
                $this->shell->warning("the directory $destinationBasepath already exists and is not readable.");
                return false;
            }
            if (!$this->fileHelper->dirIsEmpty($destinationBasepath)) {
                $this->shell->warning("the directory $destinationBasepath already exists and is not empty.");
                return false;
            }
            if (!$this->fileHelper->fileIsWritable($destinationBasepath)) {
                $this->shell->warning("the directory $destinationBasepath already exists and is not writable.");
                return false;
            }
        }
        return true;
    }

    /**
     * Generates operations needed to create new extension's directories and files.
     *
     * The method uses to overridable functions:
     * - prepareDestinationDirectories() generates all operations to create directories
     * - prepareDestinationFiles() generates all operations to create files
     *
     * @return void
     */
    public function prepare(): void
    {
        $this->prepareDestinationDirectories();
        $this->prepareDestinationFiles();
    }

    /**
     * Executes all operations generated on prepare() step
     *
     * @return bool
     */
    public function do() :bool
    {
        try {
            $totalOperations = count($this->operations);
            $this->shell->progressStart($totalOperations);
            foreach ($this->operations as $op) {
                $this->doOperation($op);
            }
            $this->shell->progressFinish();
            return true;
        } catch (\Exception $e) {
            $this->shell->error($e->getMessage());
            return false;
        }
    }

    /**
     * Displays a final success message to the user
     *
     * @return void
     */
    public function success(): void
    {
        $this->shell->success('The command has been executed successfully');
    }

    /**
     * Displays a final error message to the user
     *
     * @return void
     */
    public function failure(): void
    {
        $this->shell->error('An error occurred during the command run');
    }

    /**
     * Creates a content array to be used in templates
     *
     * @return array
     */
    protected function createDestinationFileContent(): array
    {
        $data = [];
        $data['vendor'] = $this->vendorName;
        $data['extension'] = $this->extensionName;
        return $data;
    }

    /**
     * Executes a ScaffolderOperationModel object if the action is a new directory or file creation
     *
     * @param  ScaffolderOperationModel $op
     * @return void
     */
    public function doOperation(ScaffolderOperationModel $op): void
    {
        switch ($op->getAction()) {
            case self::OPERATION_DIRECTORY_NEW:
                if ($op->getArg('name')) {
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

    /**
     * Produces a summary of user provided information to be showed in console
     *
     * @return array
     */
    protected function showRecap(): array
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

    /**
     * Asks to the user a question to get desidered vendor name or extension name.
     *
     * The value inserted by the user is sanitized, reduce to lowercase, setting only
     * the first char capitalized
     *
     * @param  string $type
     * @param  string $name
     * @param  string $default
     * @return string
     */
    public function askName(string $type, string $name, string $default = ''): string
    {
        $userProvidedValue = $this->shell->ask('Your ' . $type . ' ' . $name . ' name:', $default);
        $userProvidedValue = $this->sanitizeExtensionName($userProvidedValue);
        if (empty($userProvidedValue)) {
            $this->shell->warning('Cannot create a ' . $type . ' ' . $name . ' with empty name');
            return false;
        }
        return $userProvidedValue;
    }

    /**
     * Sanitize a string so that only case-insensitive letters are allowed
     *
     * No numbers, spaces or special chars are allowed
     *
     * @param  string $name
     * @return string
     */
    public function sanitizeExtensionName(string $name) :string
    {
        return preg_replace("/[^A-Za-z]+/", "", $name);
    }

    /**
     * Makes Symfony progress bar advance
     *
     * @return void
     */
    public function advanceProgress(): void
    {
        if ($this->shell) {
            $this->shell->progressAdvance();
            $this->msleep(self::SLEEP_TIME);
        }
    }

    /**
     * Delays execution of the script by the given time.
     *
     * @param mixed $time Time to pause script execution. Can be expressed as an integer or a decimal.
     * @return void
     * @see great implementation by Diego Andrade https://www.php.net/manual/en/function.sleep.php#118635
     * @example msleep(1.5); // delay for 1.5 seconds
     * @example msleep(.1); // delay for 100 milliseconds
     */
    protected function msleep($time): void
    {
        usleep((int)($time * 1000000));
    }

    /**
     * Creates a new ScaffolderOperationModel operation
     *
     * @param  string $action
     * @param  array $args
     * @return void
     */
    public function createOperation(string $action, array $args): ScaffolderOperationModel
    {
        return new ScaffolderOperationModel($action, $args);
    }

    /**
     * Appends a new ScaffolderOperationModel operation to the operations list to be executed
     *
     * @param  ScaffolderOperationModel $op
     * @return void
     */
    public function addOperation(ScaffolderOperationModel $op): void
    {
        $this->operations[] = $op;
    }

    /**
     * Generates a new directory for the new extension
     *
     * @param  string $name
     * @return void
     */
    public function generateDestinationDirectory(string $name): void
    {
        $basepath = $this->getDestinationAppPath();
        $this->fileHelper->generateDestinationDirectory($basepath, $name);
    }

    /**
     * Generates a new file for the new extension
     *
     * @param  string $fileName the name of the new file. The same name will be used for template
     *                          appending an extension to it
     * @param  array $data the list of variables to be used with origin template to generate the new file
     * @param  string $directory the subpath where file will be created, starting from new extension root
     * @return void
     */
    public function generateDestinationFile(string $fileName, array $data, string $directory = null): void
    {
        $originBasepath = $this->getTemplateBasepath();
        $destinationBasepath = $this->getDestinationAppPath();
        $this->fileHelper->generateDestinationFile($originBasepath, $destinationBasepath, $fileName, $data, $directory);
    }

    /**
     * Allows to specify the subpath of the new extension starting from Magento "app" folder.
     *
     * This method should be overridden by every scaffolder controller, to specify its right subpath
     *
     * @return string
     */
    public function getDestinationAppPath(): string
    {
        return 'code' . DIRECTORY_SEPARATOR . $this->vendorName . DIRECTORY_SEPARATOR . $this->extensionName;
    }

    /**
     * Returns the full root path of the new extension
     *
     * @return string
     */
    public function getDestinationBasepath(): string
    {
        $basepath = $this->getDestinationAppPath();
        return $this->fileHelper->getDestinationBasepath($basepath);
    }

    /**
     * Creates a new ScaffolderOperationModel for directory creation and appends it to list of operations to be execued
     *
     * @param  string $name
     * @return void
     */
    public function generateDirectoryOperation(string $name = null): void
    {
        $args = ['name' => $name];
        $op = $this->createOperation(self::OPERATION_DIRECTORY_NEW, $args);
        $this->addOperation($op);
    }

    /**
     * Creates a new ScaffolderOperationModel for file creation and appends it to the list of operations to be execued
     *
     * @param  string $name
     * @param  string $directory
     * @return void
     */
    public function generateFileOperation(string $name, string $directory = null): void
    {
        $args = ['name'=>$name];
        if ($directory !== null) {
            $args['directory'] = $directory;
        }
        $op = $this->createOperation(self::OPERATION_FILE_NEW, $args);
        $this->addOperation($op);
    }

    /**
     * Returns the full path to the template directory root in current module
     *
     * @return string
     */
    public function getTemplateBasepath(): string
    {
        $originPath = $this->fileHelper->getModulePath('Codever_Scaffolder');
        $subPaths = [
            $originPath,
            'templates'
        ];
        return implode(DIRECTORY_SEPARATOR, $subPaths);
    }
}
