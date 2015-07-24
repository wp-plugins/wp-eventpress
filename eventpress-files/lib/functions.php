<?php


/**
 * Check if an user is going to an event
 *
 * @param  integer $user_id  ID of logged in user
 * @param  integer $event_id ID of an event
 *
 * @return boolean           TRUE | FALSE
 */
function is_user_going( $user_id = 0, $event_id = 0 ) {

	if( ! is_user_logged_in() ) return false;

	if( $user_id == 0 ) $user_id = get_current_user_id();
	if( $event_id == 0 ){
		global $post;
		$event_id = $post->ID;
	}

	$event = new DG_Event( $event_id );
	return $event->is_user_going();

}