<?php

namespace Codever\Scaffolder\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Style\SymfonyStyle;
use Magento\Framework\App\Helper\Context;

class ScaffolderFileHelper extends AbstractHelper {

    private $data;

    public function __construct(Context $context){
        parent::__construct($context);
    }
    public function render($templateFilepath, $data) {
        $this->data = $data;
        ob_start();
        try {
            include $templateFilepath;
        } catch (\Exception $exception) {
            ob_end_clean();
            throw $exception;
        }
        return ob_get_clean();
    }

    public function write($filepath, $content){
        if (!file_exists($filepath)) {
            $fp = fopen($filepath, "w");
            fwrite($fp, $content);
            fclose($fp);
        }
    }

    private function getData(string $key){
        if(empty($key) || is_null($key) || !isset($this->data[$key])) {
            return '';
        }
        return $this->data[$key];
    }

}
