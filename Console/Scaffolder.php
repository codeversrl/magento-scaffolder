<?php
namespace Codever\Scaffolder\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Codever\Scaffolder\Helper\ScaffolderModuleHelper;
use Symfony\Component\Console\Style\SymfonyStyle;

class Scaffolder extends Command
{
    const ARGUMENT_TYPE = "type";
    const TYPE_MODULE = "module";

    private $scaffolderHelper;
    private $shell;

    public function __construct(
        ScaffolderModuleHelper $scaffolderHelper
        )
    {
        $this->scaffolderHelper = $scaffolderHelper;
        parent::__construct();
    }

    protected function configure()
    {
            $this->setName('codever:scaffolder');
            $this->setDescription('Command line Scaffolder for Magento 2 modules');
            $this->setDefinition([
            new InputArgument(
                self::ARGUMENT_TYPE,
                InputArgument::OPTIONAL,
                "Resource type"
            )
            ]);
            parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->shell = new SymfonyStyle($input, $output);
        $scaffolderType = $input->getArgument(self::ARGUMENT_TYPE);
        if (empty($scaffolderType)) {
            $scaffolderType = self::TYPE_MODULE;
            $this->shell->warning("no scaffolder type provided, I will assume it is \"module\"");
        }
        switch ($scaffolderType) {
            case self::TYPE_MODULE:
                $this->scaffolderHelper->startCommand($this->shell);
                break;
            default:
                $this->shell->warning('Only "module" scaffolder is currently supported');
                break;
        }
    }
}
