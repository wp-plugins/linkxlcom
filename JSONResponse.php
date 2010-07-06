<?php
/**
 * Description of JSONResponse
 *
 * @author LinkXL
 */
class JSONResponse {

    private $json;
    private $json_array;

    public function  __construct($json) {
        $this->json = $json;
        $this->json_array = json_decode($json, true);
    }
    /**
     *
     * @return boolean
     */
    public function isValid()
    {
        return is_array($this->json_array);
    }
    /**
     *
     * @return array
     */
    public function getAll()
    {
        return $this->json_array;
    }
    /**
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->json_array['plugin']['config'];
    }
    /**
     *
     * @return array
     */
    public function getContracts()
    {
        return $this->json_array['plugin']['contracts'];
    }

    public function  __toString()
    {
        return $this->getAll();
    }
}
?>
