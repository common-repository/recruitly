<?php
add_action('admin_menu', 'recruitly_wordpress_setup_menu');

function recruitly_wordpress_setup_menu()
{
    $customPostType=RECRUITLY_POST_TYPE;
    add_submenu_page("edit.php?post_type=$customPostType", 'Recruitly Jobs Admin', 'Settings', 'edit_posts', basename(__FILE__), 'recruitly_wordpress_settings');
}