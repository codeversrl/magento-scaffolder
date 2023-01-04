<?php
namespace Codever\Scaffolder\Model;

use Magento\Framework\Model\AbstractModel;

class ScaffolderOperationModel
{

    protected $action;

    public function __construct($action, $args)
    {
        $this->setAction($action);
        $this->setArgs($args);
    }

    protected function setArgs($args)
    {
        if(count($args)){
            foreach($args as $key=>$value){
                $this->$key = $value;
            }
        }
    }

    public function getArg($name)
    {
        if(property_exists($this, $name)){
            return $this->$name;
        }
        return null;
    }

    protected function setAction(string $action)
    {
        $this->action = $action;
    }

    public function getAction() :string
    {
        return $this->action;
    }


}
