<?php
namespace Codever\Scaffolder\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Codever\Scaffolder\Helper\ScaffolderModuleHelper;

class Scaffolder extends Command
{
    const ARGUMENT_TYPE = "type";
    const TYPE_MODULE = "module";

    private $scaffolderHelper;

    public function __construct(\Codever\Scaffolder\Helper\ScaffolderModuleHelper $scaffolderHelper, string $name = null)
    {
        $this->scaffolderHelper = $scaffolderHelper;
        parent::__construct($name);
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
        $scaffolderType = $input->getArgument(self::ARGUMENT_TYPE);
        if (empty($scaffolderType)) {
            $scaffolderType = self::TYPE_MODULE;
            $output->writeln("no scaffolder type provided, I will assume it is \"module\"");
        }
        switch ($scaffolderType) {
            case self::TYPE_MODULE:
                $this->scaffolderHelper->startCommand($input, $output);
                break;
            default:
                $output->writeln('Only "module" scaffolder is currently supported');
                break;
        }
    }
}
