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
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = @curl_exec($curl);

        if($response === false){
            echo curl_error($curl).'('.curl_errno($curl).')'."<br />\n";
        }

        curl_close($curl);

        return $response;
    }
}
?>
