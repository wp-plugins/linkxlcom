<?php
/**
 * Plugin Name: LinkXL.com
 * Plugin URI: http://linkxl.com
 * Settings URI: 
 * Description: LinkXL enables Wordpress bloggers to easily sell text link advertisting within existing content.
 * Version: 2.3
 * Author: LinkXL
 * Author URI: http://linkxl.com
 */

require_once 'JSONResponse.php';
require_once 'PluginConfig.php';
require_once 'Headers.php';
require_once 'SyncCurl.php';
require_once 'WPOptionHelper.php';
require_once 'ContentHelper.php';
require_once 'ContractsList.php';
require_once 'SyncFopen.php';

if(!function_exists('json_encode')){
    function json_encode($content){
        require_once 'Services/JSON.php';
        $json = new Services_JSON;

        return $json->encode($content);
    }
}

$headers = new Headers();
Handlers::setHandlers($headers);
$headers->checkHeaders();

add_action('update_option_linkxl_sync_count', array('Handlers', 'sync'));

if(get_option('linkxl_configuration')){
    addParseFilters(new ContractsList());
}

/**
 * Description of Handlers
 *
 * @author Kamil
 */
class Handlers {

    public static function showInfo()
    {
        echo get_option('linkxl_configuration');

        exit();
    }

    public static function sync_request()
    {
        try{
            if(function_exists('curl_init')){
                $response = SyncCurl::getSyncResponse(getSyncUrl());
            }
            else{
                $response = SyncFopen::getSyncResponse(getSyncUrl());
            }

            if(!get_option('linkxl_last_sync_status')){
                add_option('linkxl_last_sync_status');
            }

            try{
                if($response == ''){
                    throw new Exception('No response');
                }
                $valid_response = new JSONResponse($response);
                if(!$valid_response->isValid()){
                    throw new InvalidArgumentException('Error in JSON parsing.');
                }
            }
            catch(Exception $e){
                echo json_encode(array('result' => false));
                exit();
            }

            setConfig($response);

            echo json_encode(array('result' => true));
            exit();
        }
        catch(Exception $e){
            echo json_encode(array('result' => false));
            exit();
        }
    }

    public static function sync()
    {
        try{
            if(function_exists('curl_init')){
                $response = SyncCurl::getSyncResponse(getSyncUrl());
            }
            else{
                $response = SyncFopen::getSyncResponse(getSyncUrl());
            }

            if(!get_option('linkxl_last_sync_status')){
                add_option('linkxl_last_sync_status');
            }

            setConfig($response);
        }
        catch(Exception $e){
            throw $e;
        }
    }

    public static function showTag()
    {
        if(get_option('linkxl_index_title') == 'on'){
            add_filter('the_title', 'addTags');
        }
        if(get_option('linkxl_index_post') == 'on'){
            add_filter('the_content', 'addTags');
        }
        if(get_option('linkxl_index_comment') == 'on'){
            add_filter('comment_text', 'addTags');
        }
    }

    public static function setHandlers(Headers $headers)
    {
        $headers->addHandler('HTTP_LINKXLINFO', 'showInfo', 'Handlers');
        $headers->addHandler('HTTP_LINKXLSYNC', 'sync_request', 'Handlers');
        $headers->addHandler('HTTP_LINKXLSHOWTAG', 'showTag', 'Handlers');

        $headers->addHandler('LINKXLINFO', 'showInfo', 'Handlers');
        $headers->addHandler('LINKXLSYNC', 'sync_request', 'Handlers');
        $headers->addHandler('LINKXLSHOWTAG', 'showTag', 'Handlers');

        $headers->addHandler('HTTP_HTTP_LINKXLINFO', 'showInfo', 'Handlers');
        $headers->addHandler('HTTP_HTTP_LINKXLSYNC', 'sync_request', 'Handlers');
        $headers->addHandler('HTTP_HTTP_LINKXLSHOWTAG', 'showTag', 'Handlers');
    }
}

?>
