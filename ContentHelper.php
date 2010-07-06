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

?>
