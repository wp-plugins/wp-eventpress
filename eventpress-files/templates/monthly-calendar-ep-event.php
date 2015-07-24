<?php
/**
 * The Template for displaying monthly calendar of event posts
 *
 * @package EventPress
 * @since 1.0
 */

get_header(); ?>


<?php
$today = getdate();
$start = mktime( 0, 0, 0, $today['mon'], 1, $today['year'] );
$first = getdate( $start );
$end = mktime( 0, 0, 0, $first['mon']+1, 0, $first['year'] );
$last = getdate( $end );
?>

<div class="container">
	<!--header class="entry-header">
		<h1><?php _e( 'EventPress Calendar', 'eventpress' ) ?></h1>
	</header-->
	<?php
		global $eventpress;
		echo $eventpress->ep_draw_calendar( isset( $_REQUEST['ep_month'] ) ? $_REQUEST['ep_month'] : '', isset( $_REQUEST['ep_year'] ) ? $_REQUEST['ep_year'] : '' );
	?>
</div><!-- /container -->

<?php get_footer(); ?>