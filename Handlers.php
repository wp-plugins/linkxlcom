<?php
/**
 * Plugin Name: LinkXL.com
 * Plugin URI: http://linkxl.com
 * Settings URI:
 * Description: LinkXL enables Wordpress bloggers to easily sell text link advertisting within existing content.
 * Version: 2.7
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

add_action('update_option_linkxl_site_token', array('Handlers', 'sync'));
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
        }
        catch(Exception $e){
            echo json_encode(array('result' => false));

        }

        exit();
    }

    public static function sync()
    {
        try{
            if(function_exists('curl_init')){
                require_once 'SyncCurl.php';
                require_once 'WPOptionHelper.php';
                $response = SyncCurl::getSyncResponse(getSyncUrl());
            }
            else{
                require_once 'SyncFopen.php';
                require_once 'WPOptionHelper.php';
                $response = SyncFopen::getSyncResponseByFsockopen(getSyncUrl());
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
        if(get_option('linkxl_not_index_homepage') == 'on'
            && sprintf("http://%s", $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']) == get_option('home').'/'){
                return;
        }

        if(get_option('linkxl_index_all') == 'on'){
            add_filter('wp_head', buffor_start('addTags'));
            add_filter('wp_footer', 'buffor_end');
        }
        else{
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
    }

    public static function showVersion()
    {
        echo json_encode(array('VERSION' => '2.7', 'TYPE' => 'WORDPRESS_PHP5'));

        exit();
    }

    public static function showSettingsInfo()
    {
        echo json_encode(array(
            'linkxl_index_all' => get_option('linkxl_index_all'),
            'linkxl_not_index_homepage' => get_option('linkxl_not_index_homepage'),
            'linkxl_index_post' => get_option('linkxl_index_post'),
            'linkxl_index_comment' => get_option('linkxl_index_comment'),
            'linkxl_index_title' => get_option('linkxl_index_title'),
            'linkxl_last_sync_status' => get_option('linkxl_last_sync_status')
        ));

        exit();
    }

    public static function setSettings()
    {
        $available_fields = array(
            'linkxl_index_all',
            'linkxl_not_index_homepage',
            'linkxl_index_post',
            'linkxl_index_comment',
            'linkxl_index_title',
            'linkxl_last_sync_status',
            'linkxl_parse_all'
        );

        $available_values = array('on', 'off');

        if(!isset($_POST['settings'])){
            echo 'Parameter "settings" required.<br/>';
            exit();
        }

        $data = json_decode(stripcslashes($_POST['settings']), true);

        foreach($data as $key => $value){
            if(in_array($key, $available_fields) && in_array($value, $available_values)){
                update_option($key, $value);
            }
        }

        echo json_encode(array('result' => true));

        exit();
    }

    public static function testConnection()
    {
        $sync_url = getSyncUrl();

        echo 'Connection to: '.$sync_url.'<br/>';

        if(function_exists('curl_init')){
            echo 'curl_init was found<br/>';

            $response = SyncCurl::getSyncResponse($sync_url);

            echo 'response: '.(($response)?'OK':'FAIL').'<br/><br/>';
        }
        else{
            echo 'curl_init was not found<br/>';
        }

        if(function_exists('fsockopen')){
            echo 'fsockopen was found<br/>';

            $response = SyncFopen::getSyncResponseByFsockopen($sync_url);

            echo 'response: '.(($response)?'OK':'FAIL').'<br/><br/>';
        }
        else{
            echo 'fsockopen was not found<br/>';
        }

        exit();
    }

    public static function showLinks()
    {
        if(get_option('linkxl_configuration')){
            addParseFilters(new ContractsList());
        }
    }

    public static function setHandlers(Headers $headers)
    {
        $headers->addHandler('HTTP_LINKXLINFO', 'showInfo', 'Handlers');
        $headers->addHandler('HTTP_LINKXLSYNC', 'sync_request', 'Handlers');
        $headers->addHandler('HTTP_LINKXLSHOWTAG', 'showTag', 'Handlers');
        $headers->addHandler('HTTP_LINKXLSHOWVERSION', 'showVersion', 'Handlers');
        $headers->addHandler('HTTP_LINKXLSETTINGS', 'showSettingsInfo', 'Handlers');
        $headers->addHandler('HTTP_LINKXLSETSETTINGS', 'setSettings', 'Handlers');
        $headers->addHandler('HTTP_LINKXLTESTCONNECTION', 'testConnection', 'Handlers');


        $headers->addHandler('LINKXLINFO', 'showInfo', 'Handlers');
        $headers->addHandler('LINKXLSYNC', 'sync_request', 'Handlers');
        $headers->addHandler('LINKXLSHOWTAG', 'showTag', 'Handlers');
        $headers->addHandler('LINKXLSHOWVERSION', 'showVersion', 'Handlers');
        $headers->addHandler('LINKXLSETTINGS', 'showSettingsInfo', 'Handlers');
        $headers->addHandler('LINKXLSETSETTINGS', 'setSettings', 'Handlers');
        $headers->addHandler('LINKXLTESTCONNECTION', 'testConnection', 'Handlers');

        $headers->addHandler('HTTP_HTTP_LINKXLINFO', 'showInfo', 'Handlers');
        $headers->addHandler('HTTP_HTTP_LINKXLSYNC', 'sync_request', 'Handlers');
        $headers->addHandler('HTTP_HTTP_LINKXLSHOWTAG', 'showTag', 'Handlers');
        $headers->addHandler('HTTP_HTTP_LINKXLSHOWVERSION', 'showVersion', 'Handlers');
        $headers->addHandler('HTTP_HTTP_LINKXLSETTINGS', 'showSettingsInfo', 'Handlers');
        $headers->addHandler('HTTP_HTTP_LINKXLSETSETTINGS', 'setSettings', 'Handlers');
        $headers->addHandler('HTTP_HTTP_LINKXLTESTCONNECTION', 'testConnection', 'Handlers');

        $headers->setDefault('showLinks', 'Handlers');
    }
}

?>
