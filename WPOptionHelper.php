<?php

function getSyncUrl()
{
    $site_token = get_option('linkxl_site_token');

    if($option = get_option('linkxl_configuration')){
        $json_config = getJSONResponse();
        $config = $json_config->getConfig();
        return sprintf($config['sync_url'], $site_token);
    }
    else{
        return sprintf(get_option('linkxl_sync_url'), $site_token);
    }
}

function setConfig($config)
{
    try{
        if($config == ''){
            throw new Exception('No response');
        }
        $response = new JSONResponse($config);
        if(!$response->isValid()){
            throw new InvalidArgumentException('Error in JSON parsing.');
        }
    }
    catch(Exception $e){
        update_option('linkxl_last_sync_status', 'fail');
        return;
    }

    if(get_option('linkxl_configuration') == ''){
        delete_option('linkxl_configuration');
    }

    if(get_option('linkxl_configuration')){
        update_option('linkxl_configuration', $config);
    }
    else{
        add_option('linkxl_configuration', $config);
        delete_option('linkxl_sync_url');
    }

    update_option('linkxl_last_sync_status', 'good');
}

function getTag()
{
    if($option = get_option('linkxl_configuration')){
        $yaml_config = getJSONResponse();
        $config = $yaml_config->getConfig();
        return sprintf($config['tag']);
    }

    return 'linkxl';
}

function getJSONResponse()
{
    return new JSONResponse(get_option('linkxl_configuration'));
}

?>
