<?php
add_shortcode('recruitly_jobs', 'recruitly_wordpress_job_listing_shortcode');
add_shortcode('recruitly_job_search', 'recruitly_wordpress_job_search_shortcode');
add_shortcode('recruitly_job_sector_widget', 'recruitly_wordpress_job_sector_widget_shortcode');
add_shortcode('recruitly_job_tag_widget', 'recruitly_wordpress_job_tags_widget_shortcode');
add_shortcode('recruitly_job_image', 'recruitly_wordpress_job_detail_image');
add_shortcode('recruitly_jobs_recent', 'recruitly_wordpress_recent_jobs_widget_shortcode');
add_shortcode('recruitly_jobs_count', 'recruitly_wordpress_active_job_count');
add_shortcode('recruitly_job_language_list', 'recruitly_wordpress_job_languages_shortcode');
add_shortcode('recruitly_job_web_field', 'recruitly_wordpress_job_web_field_shortcode');
add_shortcode('recruitly_job_recruiter_image', 'recruitly_wordpress_job_recruiter_image_shortcode');
add_shortcode('recruitly_job_location', 'recruitly_wordpress_job_remote_location_shortcode');
add_shortcode('recruitly_job_recruiter_languages', 'recruitly_wordpress_job_recruiter_languages_shortcode');

/**
 * Renders Count of All Active Jobs
 */
function recruitly_wordpress_active_job_count()
{
    $count_posts = wp_count_posts(RECRUITLY_POST_TYPE)->publish;
    echo '' . $count_posts;
}

/**
 * Renders Job Detail Image
 */
function recruitly_wordpress_job_detail_image()
{
    $image_url = get_post_meta(get_the_ID(), 'jobDetailImageUrl', true);
    if (empty($image_url)) {
        $image_url = 'https://via.placeholder.com/150';
    }
    echo '<img src="' . $image_url . '" />';
}

/**
 * Renders Job Search Form
 */
function recruitly_wordpress_job_search_shortcode($atts)
{
    ob_start();
    extract(shortcode_atts(array(
        'target' => '',
        'cssclass' => 'jumbotron'
    ), $atts));
    get_recruitly_template('job-search-form.php');
    wp_reset_query();
    return ob_get_clean();
}

/**
 * Lists all jobs with pagination support
 */
function recruitly_wordpress_job_listing_shortcode()
{

    //recruitly_wordpress_insert_post_type();

    global $wp_query;

    $temp = $wp_query;

    if (get_query_var('paged')) {

        $paged = get_query_var('paged');

    } elseif (get_query_var('page')) {

        $paged = get_query_var('page');

    } else {

        $paged = 1;

    }

    $args = array(
        'post_type' => RECRUITLY_POST_TYPE,
        'posts_per_page' => 25,
        'meta_key' => 'uniqueId',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'paged' => $paged
    );

    if (isset($_GET['job_search'])) {

        //Escape Output
        $job_type = htmlspecialchars($_GET['job_type']);
        $job_sector = htmlspecialchars($_GET['job_sector']);
        $job_city = htmlspecialchars($_GET['job_city']);
        $job_tag = htmlspecialchars($_GET['job_tag']);
        $q = htmlspecialchars($_GET['job_search']);

        if ($q) {
            $args['s'] = $q;
        }

        $args['tax_query'] = array('relation' => 'AND');
        //Tag is a meta query
        if ($job_tag) {
            $args['meta_query'] = array('relation' => 'AND');
            $args['meta_query'][] = array(
                'value' => $job_tag
            );
        }

        //Job Type, Sector and City are Taxonomy Queries
        if ($job_type) {
            $args['tax_query'][] = array(
                'taxonomy' => 'jobtype',
                'field' => 'slug',
                'terms' => $job_type
            );
        }

        if ($job_sector) {
            $args['tax_query'][] = array(
                'taxonomy' => 'jobsector',
                'field' => 'slug',
                'terms' => $job_sector
            );
        }

        if ($job_city) {
            $args['tax_query'][] = array(
                'taxonomy' => 'jobcity',
                'field' => 'slug',
                'terms' => $job_city
            );
        }

    }

    $wp_query = new WP_Query($args);
    ob_start();
    get_recruitly_template('job-listing.php');
    wp_reset_query();
    $wp_query = $temp;
    return ob_get_clean();
}

/**
 * Renders Job Sector Widget
 */
function recruitly_wordpress_job_sector_widget_shortcode($atts)
{

    global $wp_query;
    $temp = $wp_query;
    ob_start();

    extract(shortcode_atts(array(
        'target' => '',
        'sector' => '',
        'count' => 10
    ), $atts));

    $job_sector = htmlspecialchars($sector);
    $job_count = htmlspecialchars($count);
    $job_search_page = htmlspecialchars($target);

    $job_params = array(
        'sector' => $job_sector,
        'target' => $job_search_page
    );

    $args = array(
        'post_type' => RECRUITLY_POST_TYPE,
        'posts_per_page' => $job_count,
        'meta_key' => 'jobStatus',
        'meta_value' => 'OPEN',
        'paged' => 1
    );
    $args['tax_query'][] = array(
        'taxonomy' => 'jobsector',
        'field' => 'slug',
        'terms' => $job_sector
    );

    $wp_query = new WP_Query($args);
    get_recruitly_template('job-sector-widget.php', $job_params);
    wp_reset_query();
    $wp_query = $temp;
    return ob_get_clean();
}

/**
 * Renders Jobs by Tags Widget
 */
function recruitly_wordpress_job_tags_widget_shortcode($atts)
{

    global $wp_query;
    $temp = $wp_query;
    ob_start();

    extract(shortcode_atts(array(
        'target' => '',
        'tagkey' => '',
        'tagvalue' => '',
        'count' => 10
    ), $atts));

    $job_tagkey = htmlspecialchars($tagkey);
    $job_tagval = htmlspecialchars($tagvalue);
    $job_count = htmlspecialchars($count);
    $job_search_page = htmlspecialchars($target);

    $job_params = array(
        'tagkey' => $job_tagkey,
        'tagvalue' => $job_tagval,
        'target' => $job_search_page
    );

    $args = array(
        'post_type' => RECRUITLY_POST_TYPE,
        'posts_per_page' => $job_count,
        'meta_value' => $job_tagval,
        'paged' => 1
    );

    $wp_query = new WP_Query($args);
    get_recruitly_template('job-tag-widget.php', $job_params);
    wp_reset_query();
    $wp_query = $temp;
    return ob_get_clean();
}


/**
 * Renders n Recent Jobs
 */
function recruitly_wordpress_recent_jobs_widget_shortcode($atts)
{

    global $wp_query;
    $temp = $wp_query;
    ob_start();

    extract(shortcode_atts(array(
        'count' => 10,
        'companyname' => '',
        'target' => '',
    ), $atts));

    $job_count = htmlspecialchars($count);
    $job_company = htmlspecialchars($companyname);
    $job_search_page = htmlspecialchars($target);

    $job_params = array(
        'companyName' => $job_company,
        'target' => $job_search_page
    );

    if (!empty($job_company)) {
        $args = array(
            'post_type' => RECRUITLY_POST_TYPE,
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => $job_count,
            'meta_key' => 'companyName',
            'meta_value' => $job_company,
            'paged' => 1
        );
    } else {
        $args = array(
            'post_type' => RECRUITLY_POST_TYPE,
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => $job_count,
            'meta_key' => 'jobStatus',
            'meta_value' => 'OPEN',
            'paged' => 1
        );
    }

    $wp_query = new WP_Query($args);
    get_recruitly_template('job-recent-jobs-widget.php', $job_params);
    wp_reset_query();
    $wp_query = $temp;
    return ob_get_clean();
}


/**
 * Renders Languages for the current Job
 */
function recruitly_wordpress_job_languages_shortcode($atts)
{

    extract(shortcode_atts(array(
        'mode' => 'CSV',
    ), $atts));

    //Mode can be CSV or LIST
    $job_lang_disp_mode = htmlspecialchars($mode);

    $languageJson = get_post_meta(get_the_ID(), 'languages', true);

    if (!empty($languageJson)) {

        $res = json_decode($languageJson, true);

        if (strtolower($job_lang_disp_mode) === 'list') {
            $langLIItems = "<ul class='r-language-list'>";
            foreach ($res as $lang) {
                $langName = $lang['name'];
                $langLIItems .= "<li class='r-language-item'>$langName</li>";
            }
            $langLIItems .= "</ul>";
            echo $langLIItems;
        } else if (strtolower($job_lang_disp_mode) === 'csv') {
            $langCSVItems = '';
            foreach ($res as $lang) {
                $langName = $lang['name'];
                $langCSVItems .= $langName . ", ";
            }
            echo rtrim($langCSVItems, ', ');
        }
    } else {
        echo '';
    }
}


/**
 * Renders Web Advert field for the current job
 */
function recruitly_wordpress_job_web_field_shortcode($atts)
{
    extract(shortcode_atts(array(
        'fieldname' => '',
    ), $atts));

    $web_field_name = 'webAdvert' . htmlspecialchars($fieldname);

    $field_val = get_post_meta(get_the_ID(), $web_field_name, true);

    if (!empty($field_val)) {
        header('Content-Type: text/html; charset=UTF-8');
        echo utf8_decode(nl2br($field_val));
    }

    echo '';
}

/**
 * Renders Job Recruiter Profile Picture Image
 */
function recruitly_wordpress_job_recruiter_image_shortcode()
{
    $image_url = get_post_meta(get_the_ID(), 'recruiterPic', true);
    if (empty($image_url)) {
        $image_url = 'https://via.placeholder.com/150';
    }
    echo '<img src="' . $image_url . '" />';
}

/**
 * Renders Job Remote Location Flag
 */
function recruitly_wordpress_job_remote_location_shortcode($atts)
{
    extract(shortcode_atts(array(
        'displayfield' => 'city'
    ), $atts));

    $locationType = htmlspecialchars($displayfield);

    $isRemoteWorking = get_post_meta(get_the_ID(), 'remoteWorking', true);

    if ($isRemoteWorking) {
        $jobCountry = get_post_meta(get_the_ID(), 'country', true);
        echo "<span class='jobcountry'>$jobCountry</span> <span class='jobremote'>Remote</span>";
    } else {

        $jobRegion = '';

        if (strcasecmp('town', $locationType)==0 || strcasecmp('city', $locationType)==0) {
            $jobRegion = get_post_meta(get_the_ID(), 'town', true);
        } else if (strcasecmp('region', $locationType)==0 || strcasecmp('county', $locationType)==0) {
            $jobRegion = get_post_meta(get_the_ID(), 'countyName', true);
        } else {
            $jobRegion = get_post_meta(get_the_ID(), 'cityOrRegion', true);
        }

        echo "<span class='jobcityregion'>" . $jobRegion . '</span>';
    }
}


/**
 * Renders Job Recruiter Languages List
 */
function recruitly_wordpress_job_recruiter_languages_shortcode()
{

    $languageJson = get_post_meta(get_the_ID(), 'recruiterLanguages', true);

    $langLIItems = '';

    if (isset($languageJson) && !empty($languageJson)) {
        $langLIItems .= "<ul class='r-recruiter-language-list'>";
        $res = json_decode($languageJson, true);
        echo '<span class="recruiterlangjson" style="height:1px;width:1px;visibility: hidden">' . $res . '</span>';
        foreach ($res as $lang) {
            $langLIItems .= "<li class='r-recruiter-language-item'>$lang</li>";
        }
        $langLIItems .= "</ul>";
    }

    echo $langLIItems;
}

?>