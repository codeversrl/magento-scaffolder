<?php
namespace Codever\Scaffolder\Test\Unit;

use \PHPUnit\Framework\TestCase;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Codever\Scaffolder\Console\Command\Scaffolder;
use Codever\Scaffolder\Helper\ScaffolderFileHelper;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\Console\Cli;

/**
 * test suite for Scaffolder Command class
 *
 * launch test with command:
 *
 * ```
 * /usr/local/bin/php /var/www/html/./vendor/phpunit/phpunit/phpunit
 * ./vendor/codever/magento-scaffolder/Test/Unit/Console/Command/ScaffolderTest.php
 * ```
 */
class ScaffolderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Magento object manager
     *
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * the class to be tested
     *
     * @var ScaffolderModuleController
     */
    private ScaffolderModuleController $testedClass;

    /**
     * tester for the Command class
     *
     * @var CommandTester
     */
    private CommandTester $tester;

    /**
     * setUp function to initialize tests
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->testedClass = $this->objectManager->getObject(Scaffolder::class);
        $this->tester = new CommandTester($this->testedClass);
    }

    /**
     * tests command in an interactive way
     *
     * @return void
     * @see https://symfony.com/doc/4.4/components/console/helpers/questionhelper.html#testing-a-command-that-expects-input
     */
    public function testCommandExecute(): void
    {
        $this->tester->setInputs(['0', 'Aaa', 'Bbb', 'no']);
        $this->tester->execute([]);

        $output = $this->tester->getDisplay();
        $this->assertSame(Cli::RETURN_SUCCESS, $this->tester->getStatusCode());
        $this->assertStringContainsString('module', $output);
    }
}
