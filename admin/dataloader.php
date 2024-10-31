<?php
/**
 * Deletes all jobs from wordpress.
 *
 * This function is called after deactivating Recruitly plugin and
 * after making configuration changes.
 */
function recruitly_wordpress_truncate_post_type()
{
    try {

        global $wpdb;

        $postType = RECRUITLY_POST_TYPE;

        $query = "SELECT ID FROM $wpdb->posts WHERE post_type = '$postType'";

        $results = $wpdb->get_results($query);

        if (count($results)) {
            foreach ($results as $post) {
                $purge = wp_delete_post($post->ID);
            }
        }

    } catch (Throwable $ex) {
        recruitly_log_exception($ex);
    }

}

/**
 * Insert all jobs into wordpress custom post type.
 *
 * @see https://api.recruitly.io
 * @see function recruitly_wordpress_insert_post_type()
 *
 */
function recruitly_wordpress_insert_post_type($request = null, $resyncAll = false)
{

    if (get_option('recruitly_apikey', 'na') !== 'na' && get_option('recruitly_apiserver', 'na') !== 'na') {

        $apiKey = esc_html(get_option('recruitly_apikey'));

        $apiServer = esc_url(get_option('recruitly_apiserver'));

        if ($resyncAll) {

            $forceResync = esc_attr(get_option('recruitly_force_reload','false'));

            if($forceResync==true || $forceResync=='true'){
                update_option('recruitly_sync_in_progress', '0');
            }

            recruitly_wordpress_truncate_post_type();
            recruitly_wordpress_sync_post_type($apiKey, $apiServer);
        } elseif ($request == null || false == $request->has_param('action') || empty($request->get_param('action'))) {
            recruitly_wordpress_sync_post_type($apiKey, $apiServer);
        } else {
            $action = $request->get_param('action');
            if ('delete' === $action) {
                $jobId = $request->get_param('id');
                recruitly_wordpress_delete_post_type($jobId);
            } else {
                $reqId = $request->get_param('id');
                $jobIds = explode(',',$reqId);
                foreach ($jobIds as $jobId){
                    recruitly_wordpress_sync_post_type_single($apiKey, $apiServer, $jobId);
                }
            }
        }
    }
}

function recruitly_wordpress_insert_job($job)
{

    $jobExcerpt = "";

    try {
        if (isset($job->shortDescription) && !empty($job->shortDescription)) {
            $jobExcerpt = $job->shortDescription;
        }
    } catch (Throwable $ex) {
        echo $ex->getMessage();
    }

    $post_id = wp_insert_post(array(
        'post_type' => RECRUITLY_POST_TYPE,
        'post_title' => $job->title,
        'post_content' => $job->description,
        'post_excerpt' => $jobExcerpt,
        'post_status' => 'publish',
        'comment_status' => 'closed',
        'ping_status' => 'closed',
        'post_date' => date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $job->postedOn)))
    ));

    $industry_id = recruitly_get_taxonomy_id($job->industry, 'jobindustry');
    $sector_id = recruitly_get_taxonomy_id($job->sector, 'jobsector');
    $job_type_id = recruitly_get_taxonomy_id($job->jobType, 'jobtype');

    $country_id = recruitly_get_taxonomy_id($job->location->country, 'jobcountry');
    $county_id = recruitly_get_taxonomy_id($job->location->regionName, 'jobcounty');
    $city_id;
    if ($job->remoteWorking) {
        $city_id = recruitly_get_taxonomy_id('Remote', 'jobcity');
    } else {
        $city_id = recruitly_get_taxonomy_id($job->location->cityRegion, 'jobcity');
    }

    $location_country_id = 0;

    if (!empty($job->location->country)) {
        $location_country_id = recruitly_get_taxonomy_id($job->location->country, 'joblocation');
    }

    $location_county_id = 0;

    if (!empty($job->location->regionName)) {
        $location_county_id = recruitly_get_taxonomy_id($job->location->regionName, 'joblocation', $location_country_id);
    }

    $location_city_id = 0;

    if (!empty($job->location->cityName)) {
        $location_city_id = recruitly_get_taxonomy_id($job->location->cityName, 'joblocation', $location_county_id);
    }

    add_post_meta($post_id, 'jobId', $job->id);
    add_post_meta($post_id, 'uniqueId', $job->uniqueId);
    add_post_meta($post_id, 'jobStatus', $job->status);
    add_post_meta($post_id, 'reference', $job->reference);
    add_post_meta($post_id, 'jobType', $job->jobType);
    add_post_meta($post_id, 'jobTitle', $job->title);
    add_post_meta($post_id, 'postedOn', $job->postedOn);
    add_post_meta($post_id, 'shortDesc', $job->shortDescription);
    add_post_meta($post_id, 'payLabel', $job->pay->label);
    add_post_meta($post_id, 'minSalaryRange', $job->pay->minPay);
    add_post_meta($post_id, 'maxSalaryRange', $job->pay->maxPay);
    add_post_meta($post_id, 'salaryPackage', $job->packageOverview);
    add_post_meta($post_id, 'jobType', $job->jobType);
    add_post_meta($post_id, 'experience', $job->experience);
    add_post_meta($post_id, 'sector', $job->sector);
    add_post_meta($post_id, 'industry', $job->industry);
    add_post_meta($post_id, 'hot', $job->hot);
    add_post_meta($post_id, 'applyUrl', $job->applyUrl);
    add_post_meta($post_id, 'countryCode', $job->location->countryCode);
    add_post_meta($post_id, 'country', $job->location->country);
    add_post_meta($post_id, 'countyName', $job->location->regionName);
    add_post_meta($post_id, 'county', $job->location->regionName);
    add_post_meta($post_id, 'postCode', $job->location->postCode);
    add_post_meta($post_id, 'town', $job->location->cityName);
    add_post_meta($post_id, 'cityOrRegion', $job->location->cityRegion);

    try {
        add_post_meta($post_id, 'remoteWorking', $job->remoteWorking);
        add_post_meta($post_id, 'latitude', $job->location->latitude);
        add_post_meta($post_id, 'longitude', $job->location->longitude);
    } catch (Throwable $ex) {
    }

    try {
        if (isset($job->languages) && is_array($job->languages)) {
            add_post_meta($post_id, 'languages', json_encode($job->languages));
        }
    } catch (Throwable $ex) {
    }

    try {
        if (isset($job->recruiter)) {

            add_post_meta($post_id, 'recruiterId', $job->recruiter->id);
            add_post_meta($post_id, 'recruiterName', $job->recruiter->fullName);
            add_post_meta($post_id, 'recruiterEmail', $job->recruiter->email);
            add_post_meta($post_id, 'recruiterPic', $job->recruiter->profilePicUrl);
            add_post_meta($post_id, 'recruiterMobile', $job->recruiter->mobile);
            add_post_meta($post_id, 'recruiterWorkPhone', $job->recruiter->workPhone);
            add_post_meta($post_id, 'recruiterLinkedIn', $job->recruiter->linkedIn);

            if (isset($job->recruiter->languages) && is_array($job->recruiter->languages)) {
                add_post_meta($post_id, 'recruiterLanguages', json_encode($job->recruiter->languages));
            }

        }
    } catch (Throwable $ex) {
    }


    try {
        if (isset($job->webAdvert)) {
            add_post_meta($post_id, 'webAdvert', json_encode($job->webAdvert));
            add_post_meta($post_id, 'webAdvertrecruitmentProcess', utf8_encode($job->webAdvert->recruitmentProcess));
            add_post_meta($post_id, 'webAdvertmainResponsibilities', utf8_encode($job->webAdvert->mainResponsibilities));
            add_post_meta($post_id, 'webAdvertwhatsOnOffer', utf8_encode($job->webAdvert->whatsOnOffer));
            add_post_meta($post_id, 'webAdvertcoreSkills', utf8_encode($job->webAdvert->coreSkills));
            add_post_meta($post_id, 'webAdvertwhatWillYouLearn', utf8_encode($job->webAdvert->whatWillYouLearn));
            add_post_meta($post_id, 'webAdvertkeyLanguages', utf8_encode($job->webAdvert->keyLanguages));
        }
    } catch (Throwable $ex) {
    }

    try {
        update_post_meta($post_id, 'companyName', $job->companyName);
    } catch (Throwable $ex) {
    }

    $tagPairSet = [];

    if (isset($job->tags) && is_array($job->tags) && !empty($job->tags)) {

        foreach ($job->tags as $tag) {
            try {
                add_post_meta($post_id, $tag->key, $tag->value);
                array_push($tagPairSet, $tag->key . ':' . $tag->value);
            } catch (Throwable $ex) {
                echo $ex->getMessage();
            }
        }
    }

    $languageSetIds = [];

    try {
        if (isset($job->languages) && is_array($job->languages) && !empty($job->languages)) {
            foreach ($job->languages as $lang) {
                try {
                    $lang_tax_id = recruitly_get_taxonomy_id($lang->name, 'joblanguages');
                    array_push($languageSetIds, $lang_tax_id);
                } catch (Throwable $ex) {
                    echo $ex->getMessage();
                }
            }
        }
    } catch (Throwable $ex) {
    }


    if (!empty($languageSetIds)) {
        wp_set_post_terms($post_id, $languageSetIds, 'joblanguages', false);
    }

    if (!empty($tagPairSet)) {
        wp_set_post_terms($post_id, $tagPairSet, 'jobtags');
    }

    if (!empty($country_id)) {
        wp_set_post_terms($post_id, array($country_id), 'jobcountry', false);
    }

    if (!empty($job_type_id)) {
        wp_set_post_terms($post_id, array($job_type_id), 'jobtype', false);
    }

    if (!empty($sector_id)) {
        wp_set_post_terms($post_id, array($sector_id), 'jobsector', false);
    }

    if (!empty($industry_id)) {
        wp_set_post_terms($post_id, array($industry_id), 'jobindustry', false);
    }

    if (!empty($county_id)) {
        wp_set_post_terms($post_id, array($county_id), 'jobcounty', false);
    }

    if (!empty($city_id)) {
        wp_set_post_terms($post_id, array($city_id), 'jobcity', false);
    }

    if (!empty($location_city_id) && !empty($location_county_id) && !empty($location_country_id)) {
        wp_set_post_terms($post_id, array($location_country_id, $location_county_id, $location_city_id), 'joblocation', true);
    } else if (!empty($location_county_id) && !empty($location_country_id)) {
        wp_set_post_terms($post_id, array($location_country_id, $location_county_id), 'joblocation', true);
    } else if (!empty($location_country_id)) {
        wp_set_post_terms($post_id, array($location_country_id), 'joblocation', true);
    }

    recruitly_set_featured_image($post_id, $job);

}

function recruitly_wordpress_update_job($job, $postId)
{

    $jobExcerpt = "";

    try {
        if (isset($job->shortDescription) && !empty($job->shortDescription)) {
            $jobExcerpt = $job->shortDescription;
        }
    } catch (Throwable $ex) {
        echo $ex->getMessage();
    }

    $post_id = wp_update_post(array(
        'ID' => $postId,
        'post_type' => RECRUITLY_POST_TYPE,
        'post_title' => $job->title,
        'post_content' => $job->description,
        'post_excerpt' => $jobExcerpt,
        'post_status' => 'publish',
        'comment_status' => 'closed',
        'ping_status' => 'closed',
        'post_date' => date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $job->postedOn)))
    ));

    $industry_id = recruitly_get_taxonomy_id($job->industry, 'jobindustry');
    $sector_id = recruitly_get_taxonomy_id($job->sector, 'jobsector');
    $job_type_id = recruitly_get_taxonomy_id($job->jobType, 'jobtype');

    $country_id = recruitly_get_taxonomy_id($job->location->country, 'jobcountry');
    $county_id = recruitly_get_taxonomy_id($job->location->regionName, 'jobcounty');

    $city_id;
    if ($job->remoteWorking) {
        $city_id = recruitly_get_taxonomy_id('Remote', 'jobcity');
    } else {
        $city_id = recruitly_get_taxonomy_id($job->location->cityRegion, 'jobcity');
    }

    $location_country_id = 0;

    if (!empty($job->location->country)) {
        $location_country_id = recruitly_get_taxonomy_id($job->location->country, 'joblocation');
    }

    $location_county_id = 0;

    if (!empty($job->location->regionName)) {
        $location_county_id = recruitly_get_taxonomy_id($job->location->regionName, 'joblocation', $location_country_id);
    }

    $location_city_id = 0;

    if (!empty($job->location->cityName)) {
        $location_city_id = recruitly_get_taxonomy_id($job->location->cityName, 'joblocation', $location_county_id);
    }

    update_post_meta($post_id, 'jobId', $job->id);
    update_post_meta($post_id, 'uniqueId', $job->uniqueId);
    update_post_meta($post_id, 'jobStatus', $job->status);
    update_post_meta($post_id, 'reference', $job->reference);
    update_post_meta($post_id, 'jobType', $job->jobType);
    update_post_meta($post_id, 'jobTitle', $job->title);
    update_post_meta($post_id, 'postedOn', $job->postedOn);
    update_post_meta($post_id, 'shortDesc', $job->shortDescription);
    update_post_meta($post_id, 'payLabel', $job->pay->label);
    update_post_meta($post_id, 'minSalaryRange', $job->pay->minPay);
    update_post_meta($post_id, 'maxSalaryRange', $job->pay->maxPay);
    update_post_meta($post_id, 'salaryPackage', $job->packageOverview);
    update_post_meta($post_id, 'jobType', $job->jobType);
    update_post_meta($post_id, 'experience', $job->experience);
    update_post_meta($post_id, 'sector', $job->sector);
    update_post_meta($post_id, 'industry', $job->industry);
    update_post_meta($post_id, 'hot', $job->hot);
    update_post_meta($post_id, 'applyUrl', $job->applyUrl);
    update_post_meta($post_id, 'applyEmail', $job->applyEmail);
    update_post_meta($post_id, 'countryCode', $job->location->countryCode);
    update_post_meta($post_id, 'country', $job->location->country);
    update_post_meta($post_id, 'countyName', $job->location->regionName);
    update_post_meta($post_id, 'county', $job->location->regionName);
    update_post_meta($post_id, 'postCode', $job->location->postCode);
    update_post_meta($post_id, 'town', $job->location->cityName);
    update_post_meta($post_id, 'cityOrRegion', $job->location->cityRegion);
    update_post_meta($post_id, 'remoteWorking', $job->remoteWorking);


    try {
        update_post_meta($post_id, 'latitude', $job->location->latitude);
        update_post_meta($post_id, 'longitude', $job->location->longitude);
    } catch (Throwable $ex) {
    }

    try {
        if (isset($job->languages) && is_array($job->languages)) {
            update_post_meta($post_id, 'languages', json_encode($job->languages));
        } else {
            delete_post_meta($post_id, 'languages');
        }

    } catch (Throwable $ex) {
    }

    try {
        if (isset($job->recruiter)) {

            update_post_meta($post_id, 'recruiterId', $job->recruiter->id);
            update_post_meta($post_id, 'recruiterName', $job->recruiter->fullName);
            update_post_meta($post_id, 'recruiterEmail', $job->recruiter->email);
            update_post_meta($post_id, 'recruiterPic', $job->recruiter->profilePicUrl);
            update_post_meta($post_id, 'recruiterMobile', $job->recruiter->mobile);
            update_post_meta($post_id, 'recruiterWorkPhone', $job->recruiter->workPhone);
            update_post_meta($post_id, 'recruiterLinkedIn', $job->recruiter->linkedIn);

            if (isset($job->recruiter->languages) && is_array($job->recruiter->languages)) {
                update_post_meta($post_id, 'recruiterLanguages', json_encode($job->recruiter->languages));
            } else {
                delete_post_meta($post_id, 'recruiterLanguages');
            }
        }
    } catch (Throwable $ex) {
    }

    try {
        if (isset($job->webAdvert)) {
            update_post_meta($post_id, 'webAdvert', json_encode($job->webAdvert));
            update_post_meta($post_id, 'webAdvertrecruitmentProcess', utf8_encode($job->webAdvert->recruitmentProcess));
            update_post_meta($post_id, 'webAdvertmainResponsibilities', utf8_encode($job->webAdvert->mainResponsibilities));
            update_post_meta($post_id, 'webAdvertwhatsOnOffer', utf8_encode($job->webAdvert->whatsOnOffer));
            update_post_meta($post_id, 'webAdvertcoreSkills', utf8_encode($job->webAdvert->coreSkills));
            update_post_meta($post_id, 'webAdvertwhatWillYouLearn', utf8_encode($job->webAdvert->whatWillYouLearn));
            update_post_meta($post_id, 'webAdvertkeyLanguages', utf8_encode($job->webAdvert->keyLanguages));
        }
    } catch (Throwable $ex) {
    }

    try {
        update_post_meta($post_id, 'companyName', $job->companyName);
    } catch (Throwable $ex) {
    }

    if (isset($job->jobDetailImage) && !empty($job->jobDetailImage->url)) {
        update_post_meta($post_id, 'jobDetailImageUrl', $job->jobDetailImage->url);
    }

    $tagPairSet = [];

    if (isset($job->tags) && is_array($job->tags) && !empty($job->tags)) {

        foreach ($job->tags as $tag) {
            try {
                delete_post_meta($post_id, $tag->key);
            } catch (Throwable $ex) {
                echo $ex->getMessage();
            }
        }

        foreach ($job->tags as $tag) {
            try {
                add_post_meta($post_id, $tag->key, $tag->value);
                array_push($tagPairSet, $tag->key . ':' . $tag->value);
            } catch (Throwable $ex) {
                echo $ex->getMessage();
            }
        }

    }

    $languageSetIds = [];

    try {
        if (isset($job->languages) && is_array($job->languages) && !empty($job->languages)) {
            foreach ($job->languages as $lang) {
                try {
                    $lang_tax_id = recruitly_get_taxonomy_id($lang->name, 'joblanguages');
                    array_push($languageSetIds, $lang_tax_id);
                } catch (Throwable $ex) {
                    echo $ex->getMessage();
                }
            }
        }
    } catch (Throwable $ex) {
    }

    if (!empty($languageSetIds)) {
        wp_set_post_terms($post_id, $languageSetIds, 'joblanguages', false);
    }

    if (!empty($tagPairSet)) {
        wp_set_post_terms($post_id, $tagPairSet, 'jobtags', false);
    }

    if (!empty($country_id)) {
        wp_set_post_terms($post_id, array($country_id), 'jobcountry', false);
    }

    if (!empty($job_type_id)) {
        wp_set_post_terms($post_id, array($job_type_id), 'jobtype', false);
    }

    if (!empty($industry_id)) {
        wp_set_post_terms($post_id, array($industry_id), 'jobindustry', false);
    }

    if (!empty($sector_id)) {
        wp_set_post_terms($post_id, array($sector_id), 'jobsector', false);
    }

    if (!empty($county_id)) {
        wp_set_post_terms($post_id, array($county_id), 'jobcounty', false);
    }

    if (!empty($city_id)) {
        wp_set_post_terms($post_id, array($city_id), 'jobcity', false);
    }

    if (!empty($location_city_id) && !empty($location_county_id) && !empty($location_country_id)) {
        wp_set_post_terms($post_id, array($location_country_id, $location_county_id, $location_city_id), 'joblocation', true);
    } else if (!empty($location_county_id) && !empty($location_country_id)) {
        wp_set_post_terms($post_id, array($location_country_id, $location_county_id), 'joblocation', true);
    } else if (!empty($location_country_id)) {
        wp_set_post_terms($post_id, array($location_country_id), 'joblocation', true);
    }

    recruitly_set_featured_image($post_id, $job);

}

/**
 * Delete a single job from wordpress custom post type.
 *
 * @see https://api.recruitly.io
 * @see function recruitly_wordpress_delete_post_type()
 *
 */
function recruitly_wordpress_delete_post_type($jobId)
{

    try {

        //Check if this job exists in the custom post type.
        global $wpdb;

        $postType = RECRUITLY_POST_TYPE;

        $queryRjobs = "SELECT ID FROM $wpdb->posts WHERE post_type = '$postType'";

        $queryResults = $wpdb->get_results($queryRjobs);

        if (count($queryResults)) {
            foreach ($queryResults as $post) {
                $recruitlyJobId = get_post_meta($post->ID, 'jobId', true);

                if ($recruitlyJobId === $jobId) {
                    $purge = wp_delete_post($post->ID);
                }
            }
        }

    } catch (Throwable $ex) {
        recruitly_log_exception($ex);
    }

}

/**
 * Insert a single job into wordpress custom post type.
 *
 * @see https://api.recruitly.io
 * @see function recruitly_wordpress_sync_post_type_single()
 *
 */
function recruitly_wordpress_sync_post_type_single($apiKey, $apiServer, $jobId)
{

    try {

        global $wp_version;
        $wpv = $wp_version;
        $phpVersion = phpversion();
        $siteUrl = site_url();
        $homeUrl = home_url();
        $postType = RECRUITLY_POST_TYPE;

        $apiUrl = $apiServer . '/api/job/view?jobId=' . $jobId . '&apiKey=' . $apiKey . '&su=' . $siteUrl . '&pt=' . $postType . '&wp=' . $wpv . '&hu=' . $homeUrl . '&pv=' . $phpVersion . '&rv=' . RECRUITLY_PLUGIN_VERSION;

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_MAXREDIRS => 5
        ]);

        $curlResp = curl_exec($ch);

        if (curl_errno($ch))
            return;

        curl_close($ch);

        if (is_null($curlResp))
            return;

        if (empty($curlResp))
            return;

        $job = json_decode($curlResp);

        if (!property_exists($job, 'id')) {
            return;
        }

        //Check if this job exists in the custom post type.
        global $wpdb;

        $postType = RECRUITLY_POST_TYPE;

        $queryRjobs = "SELECT ID FROM $wpdb->posts WHERE post_type = '$postType'";

        $queryResults = $wpdb->get_results($queryRjobs);

        $postIds = array();
        $jobIdList = array();

        if (count($queryResults)) {
            foreach ($queryResults as $post) {
                $coolJobId = get_post_meta($post->ID, 'jobId', true);

                if ($coolJobId === $job->id) {
                    $jobIdList[] = $coolJobId;
                    $postIds[$coolJobId] = $post->ID;
                }
            }
        }

        if (in_array($job->id, $jobIdList, false) == 0) {
            try {
                recruitly_wordpress_insert_job($job);
            } catch (Throwable $ex) {
                recruitly_log_exception($ex);
            }
        } else {
            try {
                recruitly_wordpress_update_job($job, $postIds[$job->id]);
            } catch (Throwable $ex) {
                recruitly_log_exception($ex);
            }
        }

    } catch (Throwable $ex) {
        recruitly_log_exception($ex);
    }

}

function recruitly_wordpress_verify_and_resync()
{

    $currentTime = time();
    $last_updated_at = get_option('recruitly_rest_jobs_refreshed', null);

    //If data is reloaded less than 10 minutes ago then ignore this request.
    if (null != $last_updated_at && $last_updated_at > $currentTime - (600)) {
        return;
    }

    try {
        update_option('recruitly_rest_jobs_refreshed', time());

        recruitly_wordpress_insert_post_type(null, false);

    } catch (Throwable $ex) {
        recruitly_log_exception($ex);
        return;
    }
}

function recruitly_wordpress_get_total_jobs_count($apiKey, $apiServer)
{

    try {

        global $wp_version;
        $wpv = $wp_version;
        $phpVersion = phpversion();
        $siteUrl = site_url();
        $homeUrl = home_url();
        $postType = RECRUITLY_POST_TYPE;

        $apiUrl = $apiServer . '/api/job/count?apiKey=' . $apiKey . '&su=' . $siteUrl . '&pt=' . $postType . '&wp=' . $wpv . '&hu=' . $homeUrl . '&pv=' . $phpVersion . '&rv=' . RECRUITLY_PLUGIN_VERSION;

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_MAXREDIRS => 5
        ]);

        $restResponse = json_decode(curl_exec($ch));

        if (curl_errno($ch))
            return 0;
        curl_close($ch);

        return (int)$restResponse->count;

    } catch (Throwable $ex) {
        recruitly_log_exception($ex);
        return 0;
    }

}

function recruitly_wordpress_count_post_type_all()
{

    try {

        //Check if this job exists in the custom post type.
        global $wpdb;

        $postType = RECRUITLY_POST_TYPE;

        $queryRjobs = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = '$postType'";

        return $wpdb->get_var($queryRjobs);

    } catch (Throwable $ex) {
        recruitly_log_exception($ex);
    }

    return 0;

}

/**
 * Insert all jobs into wordpress custom post type.
 *
 * @see https://api.recruitly.io
 * @see function recruitly_wordpress_sync_post_type()
 *
 */
function recruitly_wordpress_sync_post_type($apiKey, $apiServer)
{

    try {

        $totalJobsCount = recruitly_wordpress_get_total_jobs_count($apiKey, $apiServer);

        if ($totalJobsCount <= 0) {
            recruitly_wordpress_truncate_post_type();
            return;
        }

        $totalJobsInLocal = recruitly_wordpress_count_post_type_all();

        if ($totalJobsInLocal == $totalJobsCount) {
            return;
        }

        //If sync is already in progress then don't run this again
        if (get_option('recruitly_sync_in_progress', '0') !== '0') {
            return;
        }

        try {

            global $wp_version;
            $wpv = $wp_version;
            $phpVersion = phpversion();
            $siteUrl = site_url();
            $homeUrl = home_url();
            $postType = RECRUITLY_POST_TYPE;

            $pageSize = (int) get_option('recruitly_page_size','25');

            $totalPages = ceil($totalJobsCount / $pageSize);

            update_option('recruitly_sync_in_progress', "$totalJobsCount");

            //To store POST ID's retrieved from local database
            $postIds = array();

            //To store existing JOB ID's retrieved from local database
            $jobIdList = array();

            //To store new JOB ID's returned by the server.
            $newJobIdList = array();

            for ($pageNumber = 0; $pageNumber < $totalPages; $pageNumber++) {

                try {

                    $apiUrl = $apiServer . '/api/job?apiKey=' . $apiKey . '&paginated=true&pageNumber=' . $pageNumber . '&pageSize=' . $pageSize . '&su=' . $siteUrl . '&pt=' . $postType . '&wp=' . $wpv . '&hu=' . $homeUrl . '&pv=' . $phpVersion . '&rv=' . RECRUITLY_PLUGIN_VERSION;

                    $ch = curl_init();

                    curl_setopt_array($ch, [
                        CURLOPT_URL => $apiUrl,
                        CURLOPT_RETURNTRANSFER => 1,
                        CURLOPT_FOLLOWLOCATION => 1,
                        CURLOPT_MAXREDIRS => 5
                    ]);

                    $restResponse = json_decode(curl_exec($ch));

                    if (curl_errno($ch))
                        continue;
                    curl_close($ch);

                    //Verify server response and display errors.
                    if (property_exists($restResponse, 'reason') && property_exists($restResponse, 'message')) {
                        recruitly_admin_notice(htmlspecialchars($restResponse['message']), 'error');
                        continue;
                    }

                    //Check if this job exists in the custom post type.
                    global $wpdb;

                    $postType = RECRUITLY_POST_TYPE;
                    $queryRjobs = "SELECT ID FROM $wpdb->posts WHERE post_type = '$postType'";

                    $queryResults = $wpdb->get_results($queryRjobs);

                    if (count($queryResults)) {
                        foreach ($queryResults as $post) {
                            $coolJobId = get_post_meta($post->ID, 'jobId', true);
                            $jobIdList[] = $coolJobId;
                            $postIds[$coolJobId] = $post->ID;
                        }
                    }

                    foreach ($restResponse->data as $job) {

                        //Collect list of all job ID's - we use this to sync deleted jobs.
                        $newJobIdList[] = $job->id;

                        //If job does not exist then create one.
                        if (in_array($job->id, $jobIdList, false) == 0) {
                            try {
                                recruitly_wordpress_insert_job($job);
                            } catch (Throwable $ex) {
                                recruitly_log_exception($ex);
                                continue;
                            }
                        } else {
                            try {
                                recruitly_wordpress_update_job($job, $postIds);
                            } catch (Throwable $ex) {
                                recruitly_log_exception($ex);
                                continue;
                            }
                        }

                    }

                } catch (Throwable $ex) {
                    recruitly_log_exception($ex);
                    continue;
                }

            }

            try {

                //Perform delete operation.
                //Check if JOB ID stored in local database exists in the list returned by the server.
                //If not found then JOB is deleted on the server and we remove it from local database too.
                if (!empty($jobIdList)) {
                    foreach ($jobIdList as $localJobId) {
                        //If job stored in local database does not exist in remote
                        //then delete the job.
                        if (in_array($localJobId, $newJobIdList, false) == 0) {
                            $purge = wp_delete_post($postIds[$localJobId]);
                        }
                    }
                }

            } catch (Throwable $ex) {
                update_option('recruitly_sync_in_progress', '0');
            }

            update_option('recruitly_total_jobs', $totalJobsCount);
            update_option('recruitly_sync_in_progress', '0');
            update_option('recruitly_last_sync_time', time());
            update_option('recruitly_last_refreshed', time());


        } catch (Throwable $ex) {
            update_option('recruitly_sync_in_progress', '0');
            recruitly_log_exception($ex);
        }

        update_option('recruitly_sync_in_progress', '0');

    } catch (Throwable $ex) {
        update_option('recruitly_sync_in_progress', '0');
        recruitly_log_exception($ex);
    }

}

function recruitly_set_featured_image($post_id, $job)
{

    try {

        if (isset($job->bannerImage) && !empty($job->bannerImage->url)) {

            $localFile = get_post_meta($post_id, 'bannerImageLocalFile', true);

            $localOriginalFile = get_post_meta($post_id, 'bannerImageUrl', true);

            if (file_exists($localFile) && $job->bannerImage->url == $localOriginalFile) {
                return;
            }

            $image_name = $job->bannerImage->name;

            $image_url = $job->bannerImage->url;

            $upload_dir = wp_upload_dir();

            $image_data = file_get_contents($image_url);

            $upload_path = $upload_dir['basedir'] . '/recruitly/';

            $unique_file_name = wp_unique_filename($upload_path, $image_name);

            $filename = basename($unique_file_name); // Create image file name

            if (wp_mkdir_p($upload_path)) {
                $file = $upload_path . $filename;
            } else {
                $file = $upload_path . $filename;
            }

            file_put_contents($file, $image_data);

            $wp_filetype = wp_check_filetype($filename, null);

            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $file, $post_id, false, false);

            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $attach_data = wp_generate_attachment_metadata($attach_id, $file);

            wp_update_attachment_metadata($attach_id, $attach_data);

            set_post_thumbnail($post_id, $attach_id);

            update_post_meta($post_id, 'bannerImageLocalFile', $file);
            update_post_meta($post_id, 'bannerImageUrl', $job->bannerImage->url);

        }

    } catch (Throwable $ex) {
        recruitly_log_exception($ex);
    }
}

function recruitly_get_taxonomy_id($value, $taxonomy, $parentId = 0)
{

    try {
        if (!taxonomy_exists($taxonomy)) {
            recruitly_wordpress_setup_taxonomies();
        }

        if (is_null($value)|| $value === ''){
            return 0;
        }

        if (is_null($parentId) || $parentId === false || $parentId === '') {
            $parentId = 0;
        }

        $term_id = 0;

        $term_flags = get_term_by('name', $value, $taxonomy);

        if(is_wp_error($term_flags)) {
            recruitly_log_error($term_flags);
            return 0;
        }else if ($term_flags === false) {
            //No term found with this name - insert one.
            $t = wp_insert_term($value, $taxonomy, array("parent" => $parentId));
            if(is_wp_error($t)){
                recruitly_log_error($t);
            }else {
                $term_id = intval($t['term_id']);
            }
        } //Multiple terms found with this name - check the parent and return
        else if (is_array($term_flags)) {
            foreach ($term_flags as $term_flag) {
                if ($term_flag->parent === intval($parentId)) {
                    $term_id = $term_flag->term_id;
                    break;
                }
            }
        } //Single term found with this name - check the parent and return
        else if (is_object($term_flags)) {
            if ($term_flags->parent === intval($parentId)) {
                $term_id = $term_flags->term_id;
            }
        }

        //Term ID with the parent supplied is not found, so create a new term
        if ($term_id === 0) {
            $tnew = wp_insert_term($value, $taxonomy, array("parent" => $parentId));
            if(is_wp_error($tnew)){
                recruitly_log_error($tnew);
            }else {
                $term_id = intval($tnew['term_id']);
            }
        }

        return $term_id;

    } catch (Throwable $ex) {
        recruitly_log_exception($ex);
    }

    return 0;

}

/**
 * Helper function to display notice on admin pages.
 *
 * @param $message String message to display
 * @param $type String notice type
 */
function recruitly_admin_notice($message, $type)
{
    $message = esc_html($message);
    $type = esc_html($type);
    echo "<div class='notice notice-$type is-dismissible'> <p><strong>$message</strong></p></div>";
}


/**
 * Helper function to log WP Error
 *
 * @param $wperror WP_Error
 */
function recruitly_log_error(WP_Error $wperror)
{
    try {
        error_log(print_r($wperror->get_error_message(), true));
    } catch (Throwable $e) {
    }
}

/**
 * Helper function to log errors
 *
 * @param $exception Exception
 */
function recruitly_log_exception(Exception $exception)
{
    try {
        error_log(print_r($exception->getTraceAsString(), true));
    } catch (Throwable $e) {
    }
}


function recruitly_refresh_cron()
{
    wp_clear_scheduled_hook(RECRUITLY_CRON_ACTION);
    if (!wp_next_scheduled(RECRUITLY_CRON_ACTION)) {
        wp_schedule_event(time(), 'recruitly', RECRUITLY_CRON_ACTION);
    }
}