<?php
/**
 * The Template for displaying event archive posts
 *
 * @package EventPress
 * @since 1.0
 */

get_header(); ?>

<div id="primary" class="site-content">
	<div id="content" role="main">
		<?php
		global $event_archive;
		foreach( $event_archive as $event ) {
		?>
			<div class="dg-eventpress">
				<div class="dg-eventpress-list-single">
					<div class="dg-row">
						<div class="dg-col-md-4">
							<?php
								if(has_post_thumbnail($event->get_id())){
									echo get_the_post_thumbnail($event->get_id(), '', array('class' => 'dg-img-responsive') );
								} else {
							?>
							<img src="https://www.bowen.co.nz/__data/assets/image/0016/6910/GP-Conference-Connect.jpg" class="dg-img-responsive">
							<?php
								}
							?>
						</div>
						<div class="dg-col-md-8">
							<div class="dg-row">
								<div class="dg-col-md-12"><strong><?php echo $event->get_title(); ?></strong></div>
							</div>
							<div class="dg-row">
								<div class="dg-col-md-12">
									<div class="dg-row">
										<div class="dg-col-md-4"><strong>Event Date:</strong></div>
										<div class="dg-col-md-8"><?php echo $event->start_date . ' - ' . $event->end_date; ?></div>
									</div>
								</div>
							</div>
							<div class="dg-row">
								<div class="dg-col-md-12">
									<div class="dg-row">
										<div class="dg-col-md-4"><strong>Event Time:</strong></div>
										<div class="dg-col-md-8"><?php echo date("g:i a", strtotime($event->start_time) ) . ' - ' . date("g:i a", strtotime($event->end_time) ); ?></div>
									</div>
								</div>
							</div>
							<div class="dg-row">
								<div class="dg-col-md-12">
									<div class="dg-row">
										<div class="dg-col-md-4"><strong>Event Venue:</strong></div>
										<div class="dg-col-md-6"><?php echo $event->location; ?></div>
										<div class="dg-col-md-2">
											<a class="dg-btn dg-btn-primary dg-btn-sm" href="<?php echo get_permalink($event->get_id()); ?>">Details</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		<?php } // end of the loop. ?>


	</div><!-- #content -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>