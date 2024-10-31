<?php
/**
 * Renders Plugin Settings:
 * Users can enter their company name and API Key using settings page.
 */
function recruitly_wordpress_settings()
{

    if (!current_user_can('administrator')) {
        recruitly_admin_notice('Please login as administrator to configure Recruitly!!', 'error');
        exit;
    }

    if (isset($_POST['recruitly_apiserver']) && isset($_POST['recruitly_apikey'])) {
        //Verify NONCE
        if (wp_verify_nonce($_POST['recruitly_nonce'], 'recruitly_save_action')) {

            //Sanitize API Key
            $apiKey = sanitize_text_field($_POST['recruitly_apikey']);

            //Sanitize API Server
            $apiServer = sanitize_text_field($_POST['recruitly_apiserver']);

            $forceReload = false;

            if(isset($_POST['recruitly_force_reload']) && $_POST['recruitly_force_reload']=='yes'){
                $forceReload=true;
            }

            update_option('recruitly_force_reload', $forceReload);

            if(isset($_POST['recruitly_page_size'])){
                update_option('recruitly_page_size', (int)$_POST['recruitly_page_size']);
            }

            //Validate and Update Options
            if (strpos($apiServer, 'recruitly.io') !== false) {

                update_option('recruitly_apiserver', $apiServer);
                update_option('recruitly_apikey', $apiKey);
                update_option('recruitly_refresh', ( int )$_POST['recruitly_refresh']);

                recruitly_wordpress_insert_post_type(null,true);

            } else {
                recruitly_admin_notice('Invalid API Server!', 'error');
                exit;
            }

        } else {
            recruitly_admin_notice('Invalid Request!', 'error');
            exit;
        }
    }

    $last_refreshed = get_option('recruitly_last_refreshed', null);
    ?>
    <div class="wrap">
        <div class="recruitly-container card card-body" style="min-width: 700px !important;">
        <?php echo "<h3>" . __('Recruitly Wordpress Integration', 'Recruitly') . "</h3>"; ?>
        <p>The <a href="https://recruitly.io" target="_blank" title="Recruitly">Recruitly</a> plugin for WordPress helps users setup Job Board on the website within minutes, all you need
            is an API Key to get started.</p>
        <form name="recruitly_configuration_form" method="post" action="">
            <table class="recruitly-form-table form-table alternate" style="border:1px solid #bfbfbf;">
                <tbody>
                <tr>
                    <td>
                        <br/>
                        <strong><label><?php _e("Public API Key: "); ?></label></strong>
                        <input title="API Key" type="text" name="recruitly_apikey"
                               value="<?php echo esc_attr(get_option('recruitly_apikey', '')); ?>" size="40"/>
                    <p><small>You can create or retrieve public API keys from <a href="https://secure.recruitly.io/settings/api" target="_blank" rel="nofollow noopener">Recruitly API Configuration</a> page.</small></p>
                    </td>
                </tr>
                <tr>
                    <td><strong><label><?php _e("Data Load Page Size: "); ?></label></strong>
                        <input title="Page Size" type="number" name="recruitly_page_size"
                               value="<?php echo esc_attr(get_option('recruitly_page_size', '25')); ?>" min="10" max="100"/>
                       <p><small>Use the default of 25 or change it to suit to your WordPress hosting limitations.</small></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong><label for="chk_recruitly_force_reload"><?php _e("Force Resync "); ?></label></strong>
                        <input id="chk_recruitly_force_reload" type="checkbox" value="yes" name="recruitly_force_reload"/>
                        <p><small>Check this option if you want to force reload all the jobs from your Recruitly account.</small></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="hidden" name="recruitly_apiserver" value="https://api.recruitly.io">
                        <input type="hidden" name="recruitly_refresh" value="">
                        <input type="hidden" name="recruitly_api_details" value="1">
                        <?php
                        wp_nonce_field('recruitly_save_action', 'recruitly_nonce');
                        submit_button(__('Update Configuration', 'Recruitly'));
                        ?>
                    </td>
                </tr>
            </table>

        </form>

        <p></p>
        <p><strong>Data Sync Status</strong></p>
        <table class="widefat">
            <thead>
            <tr>
                <th>Parameter</th>
                <th>Value</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Sync In Progress</td>
                <td><?php echo esc_attr(get_option('recruitly_sync_in_progress', '0'))=='0'?'No':'Yes'; ?></td>
            </tr>
            <tr>
                <td>Last Full Sync Time</td>
                <td><?php echo recruitly_util_get_date(get_option('recruitly_last_sync_time', '')); ?></td>
            </tr>
            <tr>
                <td>Last API Push Time</td>
                <td><?php echo recruitly_util_get_date(get_option('recruitly_rest_jobs_refreshed', '')); ?></td>
            </tr>
        </table>
        </div>
    </div>
    <?php
}

function recruitly_util_get_date($epoc)
{
    try {
        if (!isset($epoc) || is_null($epoc) || empty($epoc)) {
            return '';
        }

        return date('d/m/Y H:i:s', (int)$epoc);
    }catch (Error $ex){
        return '';
    }

} ?>