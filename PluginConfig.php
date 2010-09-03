<?php

add_action('update_option_linkxl_sync_count', array('Handlers', 'sync'));

add_action('update_option_linkxl_site_token', array('Handlers', 'sync'));

if(count($_POST) && !function_exists('register_setting')){
    if(array_key_exists('linkxl_sync_url', $_POST)){
        update_option('linkxl_sync_url', $_POST['linkxl_sync_url']);
    }
    if(array_key_exists('linkxl_index_title', $_POST)){
        update_option('linkxl_index_title', $_POST['linkxl_index_title']);
    }
    if(array_key_exists('linkxl_index_post', $_POST)){
        update_option('linkxl_index_post', $_POST['linkxl_index_post']);
    }
    if(array_key_exists('linkxl_index_comment', $_POST)){
        update_option('linkxl_index_comment', $_POST['linkxl_index_comment']);
    }
    if(array_key_exists('linkxl_not_index_homepage', $_POST)){
        update_option('linkxl_not_index_homepage', $_POST['linkxl_not_index_homepage']);
    }
    if(array_key_exists('linkxl_index_all', $_POST)){
        update_option('linkxl_index_all', $_POST['linkxl_index_all']);
    }
    if(array_key_exists('linkxl_sync_count', $_POST)){
        update_option('linkxl_sync_count', $_POST['linkxl_sync_count']);
    }
    if(array_key_exists('linkxl_site_token', $_POST)){
        update_option('linkxl_site_token', $_POST['linkxl_site_token']);
    }
}

// create custom plugin settings menu
add_action('admin_menu', 'linkxl_settings_menu');

add_filter('plugin_row_meta', 'register_plugin_links',10,2);

function register_plugin_links($links, $file){



    if('linkxlcom/Handlers.php' == $file){

        $request_uri = $_SERVER['REQUEST_URI'];
        $request_uri = explode('plugins.php', $request_uri);
        $request_uri = $request_uri[0].'admin.php?page=linkxlcom/PluginConfig.php';

        $links[] = '<a href="'.$request_uri.'">'.__('Settings').'</a>';
    }

    return $links;
}

function linkxl_settings_menu() {

	//create new top-level menu
	add_menu_page('LinkXL Plugin Settings', 'LinkXL Settings', 'administrator', __FILE__, 'linkxl_settings_page',array());

	//call register settings function
	add_action( 'admin_init', 'register_linkxl_settings' );
}


function register_linkxl_settings() {
	//register our settings
    if(function_exists('register_setting')){
        register_setting( 'linkxl-settings-group', 'linkxl_sync_url' );

        register_setting('linkxl-settings-group', 'linkxl_not_index_homepage');
        register_setting('linkxl-settings-group', 'linkxl_index_all');

        register_setting( 'linkxl-settings-group', 'linkxl_index_title' );
        register_setting( 'linkxl-settings-group', 'linkxl_index_post' );
        register_setting( 'linkxl-settings-group', 'linkxl_index_comment' );

        register_setting( 'linkxl-settings-group', 'linkxl_site_token' );
        register_setting( 'linkxl-settings-group', 'linkxl_sync_count' );
    }
}

function linkxl_settings_page() {
?>
<div class="wrap">
<h2>LinkXL</h2>
<p><?php _e('Please <a target="new" href="http://linkxl.com/registration">register as a publisher</a> then add'.
        ' your website to <a target="new" href="http://linkxl.com">LinkXL.com</a>.'.
        ' Your site token will be sent via email and you can also access it by going'.
        ' to "My Websites" in your publisher account.') ?></p>

<form method="post" action="<?php echo (function_exists('register_setting'))?'options.php':'' ?>" name="linkxlform">
    <?php (function_exists('settings_fields'))?settings_fields( 'linkxl-settings-group' ):''; ?>
    <table class="form-table">
        <thead>
            <?php if(get_option('linkxl_last_sync_status') == 'fail'): ?>
                <tr valign="top">
                    <th colspan="2"><?php _e('Your site token is invalid. Please update it and <a href="javascript:void(0)" onclick="linkxlform.submit();">try again</a>. You can also contact <a href="http://linkxl.com/contact">our technical support</a>.') ?></th>
                </tr>
            <?php endif ?>

            <tr valign="top">
            <th scope="row"><?php _e('Site token') ?></th>
            <td><input type="text" size="40px" name="linkxl_site_token" value="<?php echo get_option('linkxl_site_token'); ?>" /></td>
            </tr>

            <tr valign="top">
            <td><input type="text" style="display: none" name="linkxl_sync_url"
                       value="<?php echo ($option = get_option('linkxl_sync_url'))?$option:'http://pluginsync.linkxl.com/?token=%s'; ?>" /></td>
            </tr>

            <?php select_form('DO NOT sell links on homepage', 'linkxl_not_index_homepage', array(), 'off') ?>
            <?php select_form('Sell links everywhere (including footer and sidebar)', 'linkxl_index_all',
                    array('id' => 'linkxl_index_all', 'onchange' => 'change_mode()'), 'off') ?>
        </thead>
        <tbody id="parts" <?php echo (get_option('linkxl_index_all') == 'on')?'style="display:none;"':'' ?>>
                <?php select_form('Sell links in titles', 'linkxl_index_title') ?>
                <?php select_form('Sell links in posts & pages', 'linkxl_index_post') ?>
                <?php select_form('Sell links in comments', 'linkxl_index_comment') ?>
        </tbody>
        <tfoot>
            <tr valign="top">
                <th scope="row"></th>
                <td>
                    <input type="hidden" value="<?php echo ($option = get_option('linkxl_sync_count'))?$option+1:1 ?>" name="linkxl_sync_count">
                </td>
            </tr>
        </tfoot>
    </table>

    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>

<script type="text/javascript">
    //<![CDATA[
    function change_mode()
    {
        if(document.getElementById('linkxl_index_all').value == 'on'){
            document.getElementById('parts').style.display = 'none';
        }
        else{
            document.getElementById('parts').style.display = '';
        }
    }
    //]]>
</script>



<?php }

function select_form($title, $name, $options=array(), $default='on')
    {
        $params = '';
        foreach($options as $key => $value){
            $params .= sprintf(' %s="%s"', $key, $value);
        }
        ?>
            <tr valign="top">
            <th scope="row"><?php _e($title) ?></th>
                <td>
                    <select style="width: 70px;" name="<?php echo $name ?>"<?php echo $params ?>>
                        <?php if(($option = get_option($name)) == 'on' || ($option == '' && $default == 'on')): ?>
                            <option value="on"><?php _e('Yes') ?></option>
                            <option value="off"><?php _e('No') ?></option>
                        <?php else: ?>
                            <option value="off"><?php _e('No') ?></option>
                            <option value="on"><?php _e('Yes') ?></option>
                        <?php endif ?>
                    </select>
                </td>
            </tr>
        <?php
    } ?>