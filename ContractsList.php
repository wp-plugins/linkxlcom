<?php

/**
 * Description of ContractsList
 *
 * @author LinkXL
 */
class ContractsList {

    private $contract_list;

    public function  __construct()
    {
        $json_config = getJSONResponse();
        $contracts = $json_config->getContracts();
        $page_url = sprintf("http://%s", $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);

        $temp = array();
        $i = 0;
        foreach($contracts as $contract){
            if($contract['publisher_url'] == $page_url){
                $temp[$i] = $contract;
                $i++;
            }
        }

        $this->contract_list = $temp;
    }

    public function parse($content)
    {
        $content = ' '.$content;
        $content_s = strtolower($content);
        foreach($this->contract_list as $j => $contract){
            $keyword_s = strtolower($contract['keyword']);
            $l = strlen($keyword_s)+2;

            $content_length = strlen($content);

            for($i=0;$i+$l<$content_length;$i++){
                $str = substr($content_s, $i, $l);

                if(preg_match('/\W{1}'.$keyword_s.'\W{1}/', $str)){
                    $content_temp = substr($content, 0, $i+1);
                    $content_temp .= sprintf('<a href="%s" target="new">%s</a>', $contract['advertiser_url'], substr($content, $i+1, $l-2));
                    $content_temp .= substr($content, $i+1+strlen($contract['keyword']));

                    unset($this->contract_list[$j]);
                    $content = $content_temp;

                    break;
                }
            }
        }

        return substr($content, 1);
    }
}
?>
