<?php

function addTags($content)
{
    $tag = getTag();

    return sprintf("{{%s}}%s{{/%s}}", $tag, $content, $tag);
}
/**
 *
 * @param ContractList $contract_list
 */
function addParseFilters($contract_list)
{
    if(get_option('linkxl_not_index_homepage') == 'on'
            && sprintf("http://%s", $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']) == get_option('home').'/'){
        return;
    }

    if(get_option('linkxl_index_all') == 'on' || get_option('linkxl_parse_all') == 'on'){
        add_filter('wp_head', buffor_start(array($contract_list, 'parse')));
        add_filter('wp_footer', 'buffor_end');
    }
    else{
        if(get_option('linkxl_index_post') == 'on'){
            add_filter('the_content', array($contract_list, 'parse'));
        }
        if(get_option('linkxl_index_title') == 'on'){
            add_filter('the_title', array($contract_list, 'parse'));
        }
        if(get_option('linkxl_index_comment') == 'on'){
            add_filter('comment_text', array($contract_list, 'parse'));
        }
    }
}

function buffor_start($callback)
{
    ob_start($callback);
}

function buffor_end()
{
    ob_end_flush();
}

?>
