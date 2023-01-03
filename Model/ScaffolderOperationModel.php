<?php
namespace Codever\Scaffolder\Model;

use Magento\Framework\Model\AbstractModel;

class ScaffolderOperationModel extends AbstractModel
{

    private $action;

    protected function _construct($action, $args)
    {
        $this->$action = $action;
        $this->setArgs($args);
    }

    private setArgs($args)
    {
        if(count($args)){
            foreach($args as $key=>$value){
                $this->$key = $value;
            }
        }
    }

    public getArg($name){
        if(property_exists($this,$name)){
            return $this->$name;
        }
        return null;
    }


}
