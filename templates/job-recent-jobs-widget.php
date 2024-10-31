<?php
/**
 * Shows the recent jobs.
 *
 * This template can be overridden by copying it to yourtheme/recruitly/job-recent-jobs-widget.php
 *
 * @author      Recruitly
 * @package     Recruitly
 * @category    Template
 */
?>
<div class="recruitly-recent-jobs">
    <?php
    global $wp_query;
    if ( $wp_query->have_posts() ) {
        while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
            <div class="recruitly-recent-job-block">

                <div class="row recruitly-recent-job-row">
                    <div class="col-12">
                        <a class="recruitly-recent-job-link" title="View Job" href="<?php the_permalink() ?>">
                            <span class="recruitly-recent-job-title"><?php the_title(); ?></span>&nbsp;
                        </a>
                    </div>
                </div>

                <?php if (!empty(recruitly_get_custom_post_value('cityOrRegion'))) { ?>
                    <div class="row recruitly-recent-job-row">
                        <div class="col-12">
                            <span class="recruitly-recent-job-loc"><?php echo recruitly_get_custom_post_value( 'cityOrRegion' ); ?></span>
                        </div>
                    </div>
                <?php } ?>

                <?php if (!empty(recruitly_get_custom_post_value('payLabel') && strtolower(recruitly_get_custom_post_value('payLabel')) != 'unknown' )) { ?>
                    <div class="row recruitly-recent-job-row">
                        <div class="col-12">
                            <span class="recruitly-recent-job-pay"><?php echo recruitly_get_custom_post_value( 'payLabel' ); ?></span>
                        </div>
                    </div>
                <?php } ?>

                <div class="row recruitly-recent-job-row">
                    <div class="col-12">
                        <div class="recruitly-recent-job-excerpt"><?php the_excerpt(); ?></div>
                    </div>
                </div>
            </div>

        <?php
        endwhile;
    }?>
</div>