<?php


/**
 * Description of SyncFopen
 *
 * @author LinkXL
 */
class SyncFopen {
    public static function getSyncResponse($url)
    {
        $handle = fsockopen($url);

        if(!$handle){
            throw new Exception("Plugin can't content a connection, please try again later.");
        }

        $response = '';

        $header = 'GET / HTTP/1.1\r\n';
        $header .= 'Connection: Close\r\n\r\n';
        fwrite($handle, $header);
        while(!feof($handle)){
            $response .= fgets($handle);
        }

        fclose($handle);

        return $response;
    }
}
?>
