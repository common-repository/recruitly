<?php
/**
 * Shows the job listing for the given sector.
 *
 * This template can be overridden by copying it to yourtheme/recruitly/job-sector-widget.php
 *
 * @author      Recruitly
 * @package     Recruitly
 * @category    Template
 */
?>
<div class="recruitly_sector_jobs">
    <?php
    global $wp_query;
    if ( $wp_query->have_posts() ) {
        while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
            <div class="cool-job-sector-row">
                <a class="cool-job-sector-link" title="View" href="<?php the_permalink() ?>">
                    <span class="cool-job-sector-title"><?php the_title(); ?></span>&nbsp;
                    <span class="cool-job-sector-loc"><i class="fa fa-map-marker" aria-hidden="true"></i> <?php echo recruitly_get_custom_post_value( 'cityOrRegion' ); ?></span>
                </a>
            </div>
        <?php
        endwhile;
        ?>
        <div class="cool-job-sector-footer">
            <a class="cool-job-sector-viewall-link" title="View All" href="<?php echo esc_attr( $target ); ?>?job_search=&job_sector=<?php echo esc_attr( $sector ); ?>">View All</a>
        </div>
        <?php
    }?>
</div>