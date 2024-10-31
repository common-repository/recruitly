<?php
/**
 * Shows the job listing page.
 *
 * This template can be overridden by copying it to yourtheme/recruitly/job-listing.php
 *
 * @author      Recruitly
 * @package     Recruitly
 * @category    Template
 */
?>
<div class="recruitly_jobs">
    <?php
    global $wp_query;
    if ($wp_query->have_posts()) {
        while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
            <div class="cool-jobrow"
                 style="background-color: #fff;display: block;min-height: 100px;border: 1px solid #e6e6e6;border-radius: 4px;position: relative;padding: 15px;margin-bottom: 20px;">
                <div class="cool-job-header">
                    <div class="cool-job-title" style="font-weight:bold;font-size:16px;"><?php the_title(); ?></div>

                    <?php if (!empty(recruitly_get_custom_post_value('cityOrRegion'))) { ?>
                        <div class="cool-job-loc"><i class="fa fa-map-marker" aria-hidden="true"></i>
                            <?php echo recruitly_get_custom_post_value('cityOrRegion'); ?>
                        </div>
                    <?php } ?>

                    <?php if (!empty(recruitly_get_custom_post_value('jobType'))) { ?>
                        <div class="cool-job-type"><i class="fa fa-clock-o" aria-hidden="true"></i>
                            <?php echo recruitly_get_custom_post_value('jobType'); ?>
                        </div>
                    <?php } ?>

                    <?php if (!empty(recruitly_get_custom_post_value('payLabel') && strtolower(recruitly_get_custom_post_value('payLabel')) != 'unknown' )) { ?>
                        <div class="cool-job-pay">
                            <?php echo recruitly_get_custom_post_value('payLabel'); ?>
                        </div>
                    <?php } ?>

                    <?php if (!empty(recruitly_get_custom_post_value('postedOn'))) { ?>
                        <div class="cool-job-date"><i class="fa fa-calendar" aria-hidden="true"></i>
                            <?php echo recruitly_get_custom_post_value('postedOn'); ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="cool-job-excerpt">
                    <div class="cool-job-desc"> <?php echo recruitly_get_custom_post_value('shortDesc'); ?></div>
                </div>
                <div class="cool-job-footer" style="text-align: right;">
                    <a class="cool-job-view-btn" title="View" href="<?php the_permalink() ?>">View</a>&nbsp;
                    <a class="cool-job-apply-btn"
                       data-featherlight="iframe"
                       data-featherlight-close-on-esc="false"
                       data-featherlight-close-on-click="false"
                       data-featherlight-iframe-frameborder="0"
                       data-featherlight-iframe-style="border-radius:10px;display:block;border:none;height:90vh;width:550px;"
                       title="Apply for <?php the_title(); ?>"
                       href="https://jobs.recruitly.io/widget/apply/<?php echo recruitly_get_custom_post_value('jobId'); ?>"
                       target="_blank"
                       style="font-weight:bold;">Apply</a>
                </div>
            </div>
        <?php
        endwhile;
        ?>
        <nav>
            <?php echo previous_posts_link('&laquo; Newer') ?>
            <?php echo next_posts_link('Older &raquo;') ?>
        </nav>
        <?php
    } ?>
</div>
