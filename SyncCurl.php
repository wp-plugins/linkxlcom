<?php


/**
 * Description of SyncCurl
 *
 * @author LinkXL
 */
class SyncCurl {
    /**
     *
     * @param string $url
     * @return string
     */
    public static function getSyncResponse($url)
    {
        
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        curl_close($curl);
        
        return $response;
    }
}
?>
