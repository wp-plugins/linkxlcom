<?php


class Headers {

    private $handlers;
    private $default;

    public function  __construct() {
        $this->handlers = array();
        $this->default = null;
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
        $is_handler_used = false;

        foreach($this->handlers as $name => $handler){
            if(isset($_SERVER[$name])){
                $this->validSiteToken($_SERVER[$name]);

                $this->useHandler($name);
                $is_handler_used = true;
            }
        }

        if(!$is_handler_used && $this->default){
            $this->useDefault();
        }
    }

    public function hasLinkXLHeaders()
    {
        foreach($this->handlers as $name => $handler){
            if(isset($_SERVER[$name])){
                return true;
            }
        }

        return false;
    }

    public function setDefault($function, $class=null)
    {
        $this->default = array(
            'function' => $function,
            'class' => $class
        );
    }

    private function useDefault()
    {
        $handler = $this->default;

        if($handler['class']){
            if(method_exists($handler['class'], $handler['function'])){
                call_user_func(array($handler['class'], $handler['function']));
            }
        }
        else{
            call_user_func($handler['function']);
        }
    }

    private function validSiteToken($token)
    {
        try{
            if($token != get_option('linkxl_site_token')){
                throw new Exception('Invalid site token');
            }
        }
        catch(Exception $e){
            echo $e->getMessage();
            exit();
        }
    }
}

?>
