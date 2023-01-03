<?php
namespace Codever\Scaffolder\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Codever\Scaffolder\Controller\ScaffolderModuleController;

class Scaffolder extends Command
{
    const ARGUMENT_TYPE = "type";
    const TYPE_MODULE = "module";
    const TYPE_THEME_FRONTEND = "frontend";

    private $fileHelper;
    private $shell;

    public function __construct(
        ScaffolderFileHelper $fileHelper,
        string $name = null
    ) {
        $this->fileHelper = $fileHelper;
        parent::__construct($name);
    }

    protected function configure()
    {
            $this->setName('codever:scaffolder');
            $this->setDescription('Command line Scaffolder for Magento 2 modules and themes');
            $this->setDefinition([]);
            parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->shell = new SymfonyStyle($input, $output);
        $scaffolderTypes = $this->getScaffoldingTypeList();
        $scaffolderType = $this->shell->choice('Select what do you want to generate', array_keys($scaffolderTypes), 0);
        $this->scaffolder = new $scaffolderTypes[$scaffolderType]($this->fileHelper);
        $this->scaffolder->startCommand($this->shell);
        break;
    }

    public function getScaffoldingTypeList(){
        return [
            self::TYPE_MODULE => ,
            self::TYPE_THEME_FRONTEND
        ]
    }
}
