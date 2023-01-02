<?php
namespace Codever\Scaffolder\Test\Unit;

use \PHPUnit\Framework\TestCase;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Codever\Scaffolder\Console\Scaffolder;
use Codever\Scaffolder\Helper\ScaffolderFileHelper;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\Console\Cli;

/**
 * /usr/local/bin/php /var/www/html/./vendor/phpunit/phpunit/phpunit  ./vendor/codever/magento-scaffolder/Test/Unit/Console/Command/ScaffolderTest.php
 */
class ScaffolderTest extends \PHPUnit\Framework\TestCase
{
    private $command;
    private $testedClass;
    private $objectManager;
    private $tester;

    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->testedClass = $this->objectManager->getObject(Scaffolder::class);
        $this->tester = new CommandTester($this->testedClass);
    }

    /**
     * @see https://symfony.com/doc/4.4/components/console/helpers/questionhelper.html#testing-a-command-that-expects-input
     */
    public function testCommandExecute()
    {
        $this->tester->setInputs(['module', 'Aaa', 'Bbb', 'no']);
        $this->tester->execute([]);

        $output = $this->tester->getDisplay();
        $this->assertSame(Cli::RETURN_SUCCESS, $this->tester->getStatusCode());
        $this->assertStringContainsString('module', $output);
    }
}
