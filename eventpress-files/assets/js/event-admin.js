;jQuery(function($) {

	// Event meta status toggle
	$(document).on( 'change', '.event_meta_type', function() {

		if( $(this).val() == 'Paid' ){
			$('.event_price_meta').slideDown();
		}else{
			$('.event_price_meta').slideUp();
		}

	} );

	// Default DatePicker Selection
	$( ".dg_datepicker" ).datepicker({
		showAnim: 				'clip',
		dateFormat: 			"yy-mm-dd",
		showOtherMonths: 		true,
      	selectOtherMonths: 		true,
      	changeMonth: 			true,
      	changeYear: 			true
	});

	// Default TimePicker Selection
	$('.dg_timepicker').timepicker();

	$('p.event_instance_chooser input').click(function() {

		var val = $(this).val(),
			_className = val + '_event_meta';

		if( ! $('.' + _className).is(':visible') ){
			$('.instance_meta').hide();
			$('.' + _className).show();
		}

	});

	$( '.rec_repeat' ).change(function() {
		var _className = 'rec_meta_' + $(this).val();
		if( ! $('.' + _className).is(':visible') ){
			$('.rec_repeat_ind_meta').hide();
			$('.' + _className).show();
		}
	});

});