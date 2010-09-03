<?php


/**
 * Description of SyncFopen
 *
 * @author LinkXL
 */
class SyncFopen {
    public static function getSyncResponse($url)
    {
        $handle = @fopen($url, 'r');

        if($handle == null){
            return '';
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

    public static function getSyncResponseByFsockopen($url)
    {
        $response = '';

        $url_temp = substr($url, 7);
        $pos = strpos($url_temp, '/');
        $domain = substr($url_temp, 0, $pos);
        $add = substr($url_temp, $pos);

        $fp = @fsockopen($domain, 80, $errno, $errstr, 30);
        if (!$fp) {
            echo "$errstr ($errno)<br />\n";
        } else {

            $out = sprintf("GET %s HTTP/1.1\r\n", $add);
            $out .= sprintf("Host: %s\r\n", $domain);
            $out .= "Connection: Close\r\n\r\n";
            fwrite($fp, $out);
            while (!feof($fp)) {
                $response .= fgets($fp, 128);
            }
            fclose($fp);
            }

            return substr($response, strpos($response, '{'));
    }
}
?>
