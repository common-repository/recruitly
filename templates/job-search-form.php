<?php
/**
 * Shows the job search form.
 *
 * This template can be overridden by copying it to yourtheme/recruitly/job-search-form.php
 *
 * @author      Recruitly
 * @package     Recruitly
 * @category    Template
 */
?>
<div class="recruitly_jobsearch <?php echo esc_attr( $cssclass ); ?>">
    <form method="GET" role="search" class="cool-job-form form form-horizontal"
          action="<?php echo esc_attr( $target ); ?>">
        <div class="row form-group">
            <div class="cool-keyword-field col-md-12">
                <input type="search"
                       name="job_search"
                       class="form-control"
                       value="<?php ( isset( $_GET['job_search'] ) ? htmlspecialchars( $_GET['job_search'] ) : '' ) ?>"
                       placeholder="Keywords"/>
            </div>
        </div>
        <div class="row form-group">
            <div class="cool-sector-field col-md-3">
                <select title="Job Sector" name="job_sector" class="form-control">
                    <option value="">Sector</option>
					<?php $types = get_terms( array( 'taxonomy' => 'jobsector', 'hide_empty' => true ) ); ?>
					<?php if ( $types ): ?>
						<?php foreach ( $types as $type ): ?>
							<?php if ( isset( $_GET['job_sector'] ) && $type->slug == $_GET['job_sector'] ): ?>
                                <option value="<?php echo $type->slug ?>"
                                        selected><?php echo htmlspecialchars( $type->name ); ?></option>
							<?php else: ?>
                                <option value="<?php echo $type->slug ?>"><?php echo htmlspecialchars( $type->name ); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
                </select>
            </div>
            <div class="cool-jobtype-field col-md-3">
                <select title="Job Type" name="job_type" class="form-control">
                    <option value="">Job Type</option>
					<?php $types = get_terms( array( 'taxonomy' => 'jobtype', 'hide_empty' => true ) ); ?>
					<?php if ( $types ): ?>
						<?php foreach ( $types as $type ): ?>
							<?php if ( isset( $_GET['job_type'] ) && $type->slug == $_GET['job_type'] ): ?>
                                <option value="<?php echo $type->slug ?>"
                                        selected><?php echo htmlspecialchars( $type->name ); ?></option>
							<?php else: ?>
                                <option value="<?php echo $type->slug ?>"><?php echo htmlspecialchars( $type->name ); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
                </select>
            </div>
            <div class="cool-jobcity-field col-md-3">
                <select title="City" name="job_city" class="form-control">
                    <option value="">Location</option>
					<?php $types = get_terms( array( 'taxonomy' => 'jobcity', 'hide_empty' => true ) ); ?>
					<?php if ( $types ): ?>
						<?php foreach ( $types as $type ): ?>
							<?php if ( isset( $_GET['job_city'] ) && $type->slug == $_GET['job_city'] ): ?>
                                <option value="<?php echo $type->slug ?>"
                                        selected><?php echo htmlspecialchars( $type->name ); ?></option>
							<?php else: ?>
                                <option value="<?php echo $type->slug ?>"><?php echo htmlspecialchars( $type->name ); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
                </select>
            </div>
            <div class="cool-search-footer col-md-3">
                <button type="submit" value=" Search" class="form-control btn btn-primary" id="submit"><i
                            class="fa fa-magnifier"></i> Submit
                </button>
            </div>
        </div>
    </form>
</div>