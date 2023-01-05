<?php
namespace Codever\Scaffolder\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Codever\Scaffolder\Controller\ScaffolderModuleController;
use Codever\Scaffolder\Helper\ScaffolderFileHelper;

class Scaffolder extends Command
{
    public const ARGUMENT_TYPE = "type";
    public const TYPE_MODULE = "module";
    public const TYPE_THEME_FRONTEND = "frontend";
    public const TYPE_THEME_ADMINHTML = "adminhtml";

    /**
     * Filesystem helper
     *
     * @var ScaffolderFileHelper
     */
    private ScaffolderFileHelper $fileHelper;

    /**
     * Symfony Console decorator
     *
     * @var SymfonyStyle
     */
    private SymfonyStyle $shell;

    /**
     * Class constructor
     *
     * @param ScaffolderFileHelper $fileHelper
     * @param string $name
     * @return void
     */
    public function __construct(
        ScaffolderFileHelper $fileHelper,
        string $name = null
    ) {
        $this->fileHelper = $fileHelper;
        parent::__construct($name);
    }

    /**
     * Console/Command configuration
     *
     * @return void
     */
    protected function configure(): void
    {
            $this->setName('codever:scaffolder');
            $this->setDescription('Command line Scaffolder for Magento 2 modules and themes');
            $this->setDefinition([]);
            parent::configure();
    }

    /**
     * Console/Command entrypoint
     *
     * @param  InputInterface $input
     * @param  OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->shell = new SymfonyStyle($input, $output);
        $scaffolderTypes = $this->getScaffoldingTypeList();
        $scaffolderType = $this->shell->choice('Select what do you want to generate', array_keys($scaffolderTypes), 0);
        $className = $scaffolderTypes[$scaffolderType];
        $r = new \ReflectionClass($className);
        $this->scaffolder = $r->newInstanceArgs([$this->fileHelper]);
        $this->scaffolder->execute($this->shell);
    }

    /**
     * List of Controller available to the user
     *
     * Each controller in this list provides a scaffolder for a specific type of extension
     *
     * @return array
     */
    public function getScaffoldingTypeList()
    {
        return [
            self::TYPE_MODULE => \Codever\Scaffolder\Controller\ScaffolderModuleController::class,
            self::TYPE_THEME_FRONTEND => \Codever\Scaffolder\Controller\ScaffolderFrontendThemeController::class,
            self::TYPE_THEME_ADMINHTML => \Codever\Scaffolder\Controller\ScaffolderAdminhtmlThemeController::class
        ];
    }
}
