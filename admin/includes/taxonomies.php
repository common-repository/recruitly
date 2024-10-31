<?php
add_action('init', 'recruitly_wordpress_setup_taxonomies');

/**
 * Register taxonomy
 * This taxonomy holds list of all job sectors
 */
function recruitly_wordpress_setup_taxonomies()
{

    try {

        if (!taxonomy_exists('jobindustry')) {

            $labels = array(
                'name' => 'Job Industries',
                'singular_name' => 'Job Industry',
                'search_items' => 'Search Job Industries',
                'all_items' => 'All Job Industries',
                'edit_item' => 'Edit Job Industry',
                'update_item' => 'Update Job Industry',
                'add_new_item' => 'Add New Job Industry',
                'new_item_name' => 'New Job Industry',
                'menu_name' => 'Job Industries'
            );

            register_taxonomy('jobindustry', RECRUITLY_POST_TYPE, array(
                'hierarchical' => true,
                'labels' => $labels,
                'query_var' => true,
                'show_admin_column' => false
            ));

        }


    } catch (Throwable  $ex) {
    }

    try {

        if (!taxonomy_exists('jobsector')) {

            $labels = array(
                'name' => 'Job Sectors',
                'singular_name' => 'Job Sector',
                'search_items' => 'Search Job Sectors',
                'all_items' => 'All Job Sectors',
                'edit_item' => 'Edit Job Sector',
                'update_item' => 'Update Job Sector',
                'add_new_item' => 'Add New Job Sector',
                'new_item_name' => 'New Job Sector',
                'menu_name' => 'Job Sectors'
            );

            register_taxonomy('jobsector', RECRUITLY_POST_TYPE, array(
                'hierarchical' => true,
                'labels' => $labels,
                'query_var' => true,
                'show_admin_column' => false
            ));

        }


    } catch (Throwable  $ex) {
    }

    try {

        if (!taxonomy_exists('jobcounty')) {

            $labels = array(
                'name' => 'Countys',
                'singular_name' => 'County ',
                'search_items' => 'Search Countys ',
                'all_items' => 'All Countys',
                'edit_items' => 'Edit County',
                'update_item' => 'Update County',
                'add_new_item' => 'Add New County',
                'new_item_name' => 'New County',
                'menu name' => 'Countys'
            );
            register_taxonomy('jobcounty', RECRUITLY_POST_TYPE, array(
                'hierarchical' => true,
                'labels' => $labels,
                'query_var' => true,
                'show_admin_column' => false
            ));

        }

    } catch (Throwable  $ex) {
    }

    try {

        if (!taxonomy_exists('jobcity')) {

            $labels = array(
                'name' => 'Cities',
                'singular_name' => 'City ',
                'search_items' => 'Search Cities ',
                'all_items' => 'All Cities',
                'edit_items' => 'Edit City',
                'update_item' => 'Update City',
                'add_new_item' => 'Add New City',
                'new_item_name' => 'New City',
                'menu name' => 'Cities'
            );
            register_taxonomy('jobcity', RECRUITLY_POST_TYPE, array(
                'hierarchical' => true,
                'labels' => $labels,
                'query_var' => true,
                'show_admin_column' => false
            ));

        }

    } catch (Throwable  $ex) {
    }

    try {

        if (!taxonomy_exists('jobcountry')) {
            $labels = array(
                'name' => 'Countries',
                'singular_name' => 'Country ',
                'search_items' => 'Search Countries ',
                'all_items' => 'All Countries',
                'edit_items' => 'Edit Country',
                'update_item' => 'Update Country',
                'add_new_item' => 'Add New Country',
                'new_item_name' => 'New Country',
                'menu name' => 'Countries'
            );
            register_taxonomy('jobcountry', RECRUITLY_POST_TYPE, array(
                'hierarchical' => true,
                'labels' => $labels,
                'query_var' => true,
                'show_admin_column' => false
            ));
        }

    } catch (Throwable  $ex) {
    }

    recruitly_setup_location_taxonomy();

    try {

        if (!taxonomy_exists('jobtype')) {
            $labels = array(
                'name' => 'Job Types',
                'singular_name' => 'Job Type ',
                'search_items' => 'Search Types ',
                'all_items' => 'All Job Types',
                'edit_items' => 'Edit Job Type',
                'update_item' => 'Update Job Type',
                'add_new_item' => 'Add New Job Type',
                'new_item_name' => 'New Job Type',
                'menu name' => 'Job Types'
            );
            register_taxonomy('jobtype', RECRUITLY_POST_TYPE, array(
                'hierarchical' => true,
                'labels' => $labels,
                'query_var' => true,
                'show_admin_column' => false
            ));
        }

    } catch (Throwable  $ex) {
    }

    try {

        if (!taxonomy_exists('jobtags')) {
            $labels = array(
                'name' => 'Job Tags',
                'singular_name' => 'Job Tag ',
                'search_items' => 'Search Tags ',
                'all_items' => 'All Job Tags',
                'edit_items' => 'Edit Job Tag',
                'update_item' => 'Update Job Tag',
                'add_new_item' => 'Add New Job Tag',
                'new_item_name' => 'New Job Tag',
                'menu name' => 'Job Tagss'
            );
            register_taxonomy('jobtags', RECRUITLY_POST_TYPE, array(
                'hierarchical' => false,
                'labels' => $labels,
                'query_var' => true,
                'show_admin_column' => false
            ));
        }


    } catch (Throwable  $ex) {
    }

    try {
        if (!taxonomy_exists('joblanguages')) {
            $labels = array(
                'name' => 'Job Languages',
                'singular_name' => 'Job Language ',
                'search_items' => 'Search Languages ',
                'all_items' => 'All Job Languages',
                'edit_items' => 'Edit Job Language',
                'update_item' => 'Update Job Language',
                'add_new_item' => 'Add New Job Language',
                'new_item_name' => 'New Job Language',
                'menu name' => 'Job Languages'
            );
            register_taxonomy('joblanguages', RECRUITLY_POST_TYPE, array(
                'hierarchical' => false,
                'labels' => $labels,
                'query_var' => true,
                'show_admin_column' => false
            ));
        }

    } catch (Throwable  $ex) {
    }
}

/**
 * Setup Location as hierarchical taxonomy
 * @return void
 */
function recruitly_setup_location_taxonomy()
{
    try {
        if (taxonomy_exists('joblocation')) {
            unregister_taxonomy_for_object_type('joblocation', RECRUITLY_POST_TYPE);
        }
    } catch (Throwable  $ex) {
    }

    try {

        register_taxonomy('joblocation', RECRUITLY_POST_TYPE, array(
            'hierarchical' => true,
            'labels' => array(
                'name' => 'Locations',
                'singular_name' => 'Location',
                'search_items' => __('Search Locations'),
                'all_items' => __('All Locations'),
                'parent_item' => __('Parent Location'),
                'parent_item_colon' => __('Parent Location:'),
                'edit_item' => __('Edit Location'),
                'update_item' => __('Update Location'),
                'add_new_item' => __('Add New Location'),
                'new_item_name' => __('New Location Name'),
                'menu_name' => __('Job Locations'),
            ),
            'rewrite' => array(
                'slug' => 'joblocation', // This controls the base slug that will display before each term
                'with_front' => false, // Dont display the category base before "/joblocation/"
                'hierarchical' => true // This will allow URLs like "/joblocation/boston/cambridge/"
            ),
        ));
    } catch (Throwable  $ex) {
    }
}

function recruitly_delete_terms_data($taxonomy_name)
{
    try {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy_name,
            'hide_empty' => false
        ));
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, $taxonomy_name);
        }
    } catch (Throwable  $ex) {
        //Ignore for backward compatibility
    }
}

/**
 * De-register taxonomies.
 */
function recruitly_wordpress_delete_taxonomies()
{

    try
    {

        recruitly_delete_terms_data('jobcity');
        recruitly_delete_terms_data('jobcounty');
        recruitly_delete_terms_data('jobcountry');
        recruitly_delete_terms_data('jobsector');
        recruitly_delete_terms_data('jobtype');
        recruitly_delete_terms_data('jobtags');
        recruitly_delete_terms_data('joblanguages');

        unregister_taxonomy_for_object_type('jobcity', RECRUITLY_POST_TYPE);
        unregister_taxonomy_for_object_type('jobcounty', RECRUITLY_POST_TYPE);
        unregister_taxonomy_for_object_type('jobcountry', RECRUITLY_POST_TYPE);
        unregister_taxonomy_for_object_type('jobsector', RECRUITLY_POST_TYPE);
        unregister_taxonomy_for_object_type('jobtype', RECRUITLY_POST_TYPE);
        unregister_taxonomy_for_object_type('jobtags', RECRUITLY_POST_TYPE);
        unregister_taxonomy_for_object_type('joblanguages', RECRUITLY_POST_TYPE);

    } catch (Throwable  $ex) {
        //Ignore for backward compatibility
    }

    try {
        recruitly_delete_terms_data('jobindustry');
        unregister_taxonomy_for_object_type('jobindustry', RECRUITLY_POST_TYPE);
    } catch (Throwable  $ex) {
        //Ignore for backward compatibility
    }

    try {
        recruitly_delete_terms_data('joblocation');
        unregister_taxonomy_for_object_type('joblocation', RECRUITLY_POST_TYPE);
    } catch (Throwable  $ex) {
        //Ignore for backward compatibility
    }

}