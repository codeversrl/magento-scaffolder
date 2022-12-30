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
    const TYPE_THEME = "theme";

    private $scaffolderHelper;
    private $shell;

    public function __construct(
        ScaffolderModuleHelper $scaffolderHelper,
        string $name = null
        )
    {
        $this->scaffolderHelper = $scaffolderHelper;
        parent::__construct($name);
    }

    protected function configure()
    {
            $this->setName('codever:scaffolder');
            $this->setDescription('Command line Scaffolder for Magento 2 modules');
            $this->setDefinition([]);
            parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->shell = new SymfonyStyle($input, $output);
        $scaffolderType = $this->shell->choice('Select what do you want to generate', [self::TYPE_MODULE, self::TYPE_THEME], 0);
        switch ($scaffolderType) {
            case self::TYPE_MODULE:
                $this->scaffolderHelper->startCommand($this->shell);
                break;
            case self::TYPE_THEME:
                $this->shell->warning("theme scaffolder isn't available yet, sorry");
                break;
        }
    }
}
