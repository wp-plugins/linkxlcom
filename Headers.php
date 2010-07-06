<?php


class Headers {

    private $handlers;

    public function  __construct() {
        $this->handlers = array();
    }

    public function addHandler($header_name, $function, $class=null)
    {
        $this->handlers[$header_name] = array(
            'function' => $function,
            'class' => $class
        );
    }

    private function useHandler($header_name)
    {
        $handler = $this->handlers[$header_name];

        if($handler['class']){
            if(method_exists($handler['class'], $handler['function'])){
                call_user_func(array($handler['class'], $handler['function']));
            }
        }
        else{
            call_user_func($handler['function']);
        }
    }

    public function checkHeaders()
    {
        foreach($this->handlers as $name => $handler){
            if(isset($_SERVER[$name])){
                validSiteToken($_SERVER[$name]);

                $this->useHandler($name);
            }
        }
    }
}

?>
