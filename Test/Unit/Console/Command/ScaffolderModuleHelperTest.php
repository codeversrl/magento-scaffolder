<?php
namespace Codever\Scaffolder\Test\Unit;

use \PHPUnit\Framework\TestCase;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Codever\Scaffolder\Helper\ScaffolderModuleHelper;

class ScaffolderModuleHelperTest extends \PHPUnit\Framework\TestCase
{
    private $command;
    private $scaffolderFileHelperMock;
    private $objectManager;

    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->testedClass = $this->objectManager->getObject(ScaffolderModuleHelper::class);
    }

    public function testCommandExecute()
    {
        $original = "my \" \\customù \\.? àmodule 2";
        $tobe = "mycustommodule";
        $sanitized = $this->testedClass->sanitizeModuleName($original);
        $this->assertEquals($sanitized, $tobe);
    }
}
