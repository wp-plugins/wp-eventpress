<?php
/**
 * The Template for displaying all single event posts
 *
 * @package EventPress
 * @since 1.0
 */

get_header(); ?>

<div id="primary" class="site-content">
	<div id="content" role="main">

		<?php while ( have_posts() ) : the_post(); ?>
			<?php $event = new EP_Event( get_the_ID() ); ?>
			<div class="dg-eventpress">

				<?php do_action( 'ep_before_single', $event ); ?>

				<div class="dg-row dg-top-space dg-eventpress-single-header">
					<div class="dg-col-md-9 dg-col-sm-12 dg-eventpress-single-title">
						<h3><?php echo $event->get_title(); ?></h3>
						<p><?php echo $event->location; ?></p>
					</div>

					<div class="dg-col-md-3 dg-col-sm-12 dg-text-right dg-eventpress-single-action">
						<?php if( ! is_user_going() ) { ?>
							<?php if( apply_filters( 'ep_allow_join_rsvp', $event->can_rsvp ) == true ){ ?>
							<button type="button" class="dg-btn dg-btn-primary dg-btn-sm dg-btn-block ep-event-join">
							<?php echo apply_filters( 'epeevent_join_text', __( 'Join Now', 'ep' ) ) ?>
							</button>
							<?php }else{
								_e( 'Sorry! There is no ticket available', 'ep' );
								} ?>
						<?php }else{ ?>
						<button type="button" class="dg-btn dg-btn-primary dg-btn-sm dg-btn-block ep-event-join-cancel">
						<?php echo apply_filters( 'ep_event_cancel_text', __( 'Cancel Event', 'ep' ) ) ?>
						</button>
						<?php } ?>
					</div>
				</div>

				<?php do_action( 'ep_event_notice', $event ); ?>

				<div class="dg-row dg-eventpress-single-featured">
					<div class="dg-col-md-12">
						<?php
							if(has_post_thumbnail($event->get_id())){
								echo get_the_post_thumbnail($event->get_id(), '', array('class' => 'dg-img-responsive') );
							}
						?>

					</div>
				</div>
				<div class="dg-row dg-top-space dg-eventpress-single-datetime">
					<div class="dg-col-md-6 dg-eventpress-single-date">
						<div>
							<p class="dg-eventpress-title">Event <span class="dg-eventpress-red">Date</span></p>
						</div>
						<div class="dg-eventpress-single-datebox">
							<div class="dg-col-md-2 dg-text-center dg-eventpress-datetime-icon">
								<i class="fa fa-calendar"></i>
							</div>
							<div class="dg-col-md-10 dg-text-center dg-eventpress-datetime-details"><p><?php echo get_date_time_value( strtotime( $event->start_date ) ) . ' - ' . get_date_time_value( strtotime( $event->end_date ) ); ?></p></div>
						</div>
					</div>
					<div class="dg-col-md-6 dg-eventpress-single-time">
						<div>
							<p class="dg-eventpress-title">Event <span class="dg-eventpress-red">Time</span></p>
						</div>
						<div class="dg-eventpress-single-datebox">
							<div class="dg-col-md-2 dg-text-center dg-eventpress-datetime-icon">
								<i class="fa fa-clock-o"></i>
							</div>
							<div class="dg-col-md-10 dg-text-center dg-eventpress-datetime-details"><p><?php echo get_date_time_value( strtotime( $event->start_time ), false, true ) . ' - ' . get_date_time_value( strtotime( $event->end_time ), false, true ); ?></p></div>
						</div>
					</div>
				</div>


				<?php do_action( 'ep_before_single_content', $event ); ?>

				<div class="dg-row dg-top-space dg-eventpress-single-description">
					<div class="dg-col-md-12">
						<p class="dg-eventpress-title">Event <span class="dg-eventpress-red">Details</span></p>
					</div>
					<div class="dg-col-md-12">
						<p><?php echo $event->get_content(); ?></p>
					</div>
				</div>

				<?php do_action( 'ep_after_single_content', $event ); ?>
				
				<div class="dg-row dg-top-space dg-eventpress-guests">
					<div class="dg-col-md-12">
						<p class="dg-eventpress-title">Guest <span class="dg-eventpress-red">List</span></p>
						<ul>
						<?php
							$guests = $event->rsvp;
							foreach( $guests as $guest ){
								?>
								<li>
									<?php echo get_avatar( $guest, 64 ); ?><br>
									<?php
										$user = new WP_User( $guest );
										echo $user->display_name;
									?>
								</li>
								<?php
							}
						?>
						</ul>
					</div>
				</div>


			</div>

		<?php endwhile; // end of the loop. ?>


	</div><!-- #content -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>