
// Open popup
function dg_open_popup( width, height, content ){

	// Assigning default value
	width = typeof width !== 'undefined' ? width : 600;
	height = typeof height !== 'undefined' ? height : 400;
	content = typeof content !== 'undefined' ? content : '';

	var obj = jQuery( '.dg-popup-wrap' ),
		pop = jQuery( '.dg-popup' );

	pop
		.css({
			width: width + 'px',
			height: height + 'px'
		});

	pop
		.find('.dg-pop-content-details')
		.replaceWith(content);


	obj.fadeIn(500);


}

function dg_close_popup() {

	var obj = jQuery( '.dg-popup-wrap' ),
		pop = jQuery( '.dg-popup' );

	obj.fadeOut(500);

}

function replace_pop_content( content ){

	var obj = jQuery( '.dg-popup-wrap' ),
		pop = jQuery( '.dg-popup' );

	pop
		.find('.dg-pop-content')
		.html(content);

}

jQuery( '.dg-pop-close' ).click(function() {
	dg_close_popup();
});

function IsEmail(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);
}

