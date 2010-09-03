<?php

/**
 * Description of ContractsList
 *
 * @author LinkXL
 */
class ContractsList {

    private $contract_list;

    private $separate_tags_bad = array('a', 'textarea', 'option', 'script');

    public function __construct()
    {
        $json_config = getJSONResponse();
        $contracts = $json_config->getContracts();
        $page_url = sprintf("http://%s", $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);

        $temp = array();
        $i = 0;
        if(is_array($contracts)){
            foreach($contracts as $contract){
                if($contract['publisher_url'] == $page_url){
                    $temp[$i] = $contract;
                    $i++;
                }
            }
        }

        $this->contract_list = $temp;
    }

    public function parse($content)
    {
        $content = ' '.$content.' ';

        if(count($this->contract_list)>0){

            foreach($this->contract_list as $j => $contract){
                $keyword_s = strtolower($contract['keyword']);
                $l = strlen($keyword_s)+2;

                $content_length = strlen($content);

                for($i=0;$i+$l<$content_length;$i++){
                    $str = substr($content, $i, $l);

                    if(preg_match('/\W{1}'.$keyword_s.'\W{1}/', strtolower($str))){
                        if(!$this->can_change_keyword($i+1, $i+$l-2, $content, $str)){

                            continue;
                        }

                        $content_temp = substr($content, 0, $i+1);
                        $content_temp .= sprintf('<a href="%s" target="new">%s</a>', $contract['advertiser_url'], substr($content, $i+1, $l-2));
                        $content_temp .= substr($content, $i+1+strlen($contract['keyword']));

                        unset($this->contract_list[$j]);
                        $content = $content_temp;

                        break;
                    }
                }
            }
        }

        return trim($content);
    }
    /**
     * $t_b_s_p - tag begin start position in content
     * $t_b_e_p - tag begin end position in content
     * $t_e_s_p - tag end start position in content
     * $t_e_e_p - tag end end position in content
     *
     * @param int $start_postion
     * @param int $end_postion
     * @param string $content
     * @param string $key
     * @return boolean
     */
    private function can_change_keyword($start_postion, $end_postion, $content, $key)
    {
        $bad_tags = implode('|', $this->separate_tags_bad);

        $pattern_1 = "/<(".$bad_tags.")*\/(".$bad_tags.")>/";

        $content_length = strlen($content);

        $t_b_s_p = $this->str_last_pos($content, '<', $start_postion);

        $t_b_e_p = $this->str_last_pos($content, '>', $start_postion);

        $t_e_s_p = $this->str_first_pos($content, '<', $end_postion);

        $t_e_e_p = $this->str_first_pos($content, '>', $end_postion);

        if(($t_b_s_p === false && $t_b_e_p === false) || ($t_e_s_p === false && $t_e_e_p === false)){
            return true;
        }

        if($t_b_s_p !== false && $t_b_e_p !== false && $t_e_s_p !== false && $t_e_e_p !== false && ($t_b_s_p < $t_b_e_p)
                && ($t_e_s_p < $t_e_e_p) && ($content[$t_e_s_p+1] == '/') && ($content[$t_b_s_p+1] != '/')){
            return !preg_match($pattern_1, $this->str_cut($t_b_s_p, $t_e_e_p, $content));
        }

        if($t_b_s_p !== false && $t_e_e_p !== false && ($t_b_s_p > $t_b_e_p) && ($t_e_s_p > $t_e_e_p)){
            return strip_tags($this->str_cut($t_b_s_p, $t_e_e_p, $content)) != '';
        }


        return true;
    }

    private function str_last_pos($str, $search, $end_pos)
    {
        $l = strlen($search);

        $i = $end_pos;
        $str_t = '';
        while($i>=0){
            $str_t = $str[$i].$str_t;
            if($str[$i] == $search){
                $this->ss[] = $str_t;
                return $i;
            }
            $i--;
        }

        return false;
    }

    private function str_first_pos($str, $search, $start_pos)
    {
        $l = strlen($search);
		$str_length = strlen($str);
        $i = $start_pos;

        while($i<$str_length){
            if($str[$i] == $search){
                return $i;
            }
            $i++;
        }

        return false;
    }

    private function str_cut($start, $end, $text)
    {
        $str = '';

	for($i=$start;$i<=$end;$i++){
            $str .= $text[$i];
	}

	return $str;
    }
}
?>
