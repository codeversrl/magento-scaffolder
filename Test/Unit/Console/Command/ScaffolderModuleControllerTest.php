<?php
namespace Codever\Scaffolder\Test\Unit;

use \PHPUnit\Framework\TestCase;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Codever\Scaffolder\Controller\ScaffolderModuleController;

/**
 * Test class for ScaffolderModuleController class
 */
class ScaffolderModuleControllerTest extends \PHPUnit\Framework\TestCase
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
     * setUp function to initialize tests
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->testedClass = $this->objectManager->getObject(ScaffolderModuleController::class);
    }

    /**
     * testing sanitize name function
     *
     * @return void
     */
    public function testCommandExecute(): void
    {
        $original = "my \" \\customù \\.? àmodule 2";
        $tobe = "mycustommodule";
        $sanitized = $this->testedClass->sanitizeExtensionName($original);
        $this->assertEquals($sanitized, $tobe);
    }
}
