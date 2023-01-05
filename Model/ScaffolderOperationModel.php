<?php
namespace Codever\Scaffolder\Model;

use Magento\Framework\Model\AbstractModel;

class ScaffolderOperationModel
{
    /**
     * the action of the current operation
     *
     * @var string
     */
    protected string $action;

    /**
     * Constructor
     *
     * @param  string $action
     * @param  array $args
     * @return void
     */
    public function __construct(string $action, array $args)
    {
        $this->setAction($action);
        $this->setArgs($args);
    }

    /**
     * Sets key/value pairs for attributes
     *
     * @param  array $args
     * @return void
     */
    protected function setArgs(array $args): void
    {
        if (count($args)) {
            foreach ($args as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Gets a value from its key
     *
     * @param  string $name
     * @return string|null
     */
    public function getArg(string $name): string|null
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    /**
     * Sets a value for action
     *
     * @param  string $action
     * @return void
     */
    protected function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * Gets the action value
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }
}
