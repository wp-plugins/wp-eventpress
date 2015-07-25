;jQuery(function($) {
	
	$('.ep-event-join').click(function() {
		if( obj.logged ){
			var con = confirm( obj.join_text );
			if( con ){
				window.location.href = obj.event_url + '?ep_join_event=yes';
			}
		}else{
			alert( obj.log_msg );
			window.location.href = obj.login_url;
		}
	});
	
	$('.ep-event-join-cancel').click(function() {
		var con = confirm( obj.cancel_text );
		if( con ){
			window.location.href = obj.event_url + '?ep_join_event_cancel=yes';
		}
	});
	
});

/*
;jQuery(function($) {

	var reg_content = '';
	reg_content += '<div class="dg-register">';
		reg_content += '<div class="dg-row dg-text-center dg-spacer">';
			reg_content += '<div class="dg-col-md-12">';
				reg_content += '<h1 style="font-size: 24px"><i class="fa fa-pencil-square-o"></i> Register Account</h1>';
			reg_content += '</div>';
		reg_content += '</div>';

		reg_content += '<div class="dg-row dg-text-center dg-spacer">';
			reg_content += '<div class="dg-col-md-12">';
				reg_content += '<input type="text" placeholder="Your Username" id="dg-reg-username" class="dg-text-center">';
			reg_content += '</div>';
		reg_content += '</div>';

		reg_content += '<div class="dg-row dg-text-center dg-spacer">';
			reg_content += '<div class="dg-col-md-12">';
				reg_content += '<input type="email" placeholder="Your Email" id="dg-reg-email" class="dg-text-center">';
			reg_content += '</div>';
		reg_content += '</div>';

		reg_content += '<div class="dg-row dg-text-center dg-spacer">';
			reg_content += '<div class="dg-col-md-12">';
				reg_content += '<input type="password" class="dg-text-center" id="dg-reg-pw" placeholder="Your Password">';
			reg_content += '</div>';
		reg_content += '</div>';

		reg_content += '<div class="dg-row dg-text-center dg-spacer">';
			reg_content += '<div class="dg-col-md-12">';
				reg_content += '<button class="dg-btn dg-btn-success dg-btn-sm" id="dg-register-btn" type="button" value="'+obj.register+'">Register</button><br>';
			reg_content += '</div>';
		reg_content += '</div>';

		reg_content += '<div class="dg-row dg-text-center dg-spacer">';
			reg_content += '<div class="dg-col-md-12">';
				reg_content += '<a id="dg-login-pop" href="#">Have an account already? <button type="button" class="dg-btn dg-btn-primary dg-btn-sm">Login</button></a>';
			reg_content += '</div>';
		reg_content += '</div>';

	reg_content += '</div>';

	reg_content += '<input type="hidden" id="dg-ep-nonce" value="'+ obj.ep_nonce +'">';

	var confirm_html = '<div class="dg-register">';
			confirm_html += '<div class="dg-row dg-text-center dg-spacer">';
				confirm_html += '<div class="dg-col-md-12">';
					confirm_html += '<h1><i class="fa fa-exclamation-triangle"></i> ' + obj.confirm_text + '</h1>';
				confirm_html += '</div>';
			confirm_html += '</div>';

			if( obj.ticket ){
			confirm_html += '<div class="dg-row dg-text-center dg-spacer">';
				confirm_html += '<div class="dg-col-md-12">';
					confirm_html += '<input type="text" class="dg-text-center" id="dg-reg-ticket" placeholder="' + obj.ticket_txt + '">';
				confirm_html += '</div>';
			confirm_html += '</div>';
			}

			confirm_html += '<div class="dg-row dg-text-center dg-spacer">';
				confirm_html += '<div class="dg-col-md-12">';
					confirm_html += '<button class="dg-btn dg-btn-success dg-btn-sm" id="ep-event-confirm" type="button" value="">'+obj.confirm_btn+'</button>';
				confirm_html += '</div>';
			confirm_html += '</div>';
		confirm_html += '</div>';
	confirm_html += '<input type="hidden" id="dg-ep-nonce" value="'+ obj.ep_nonce +'">';



	var login_content = '';
		login_content += '<div class="dg-register">';
			login_content += '<div class="dg-row dg-text-center dg-spacer">';
				login_content += '<div class="dg-col-md-12">';
					login_content += '<h1 style="font-size: 24px"><i class="fa fa-pencil-square-o"></i> Account Login</h1>';
				login_content += '</div>';
			login_content += '</div>';

			login_content += '<div class="dg-row dg-text-center dg-spacer">';
				login_content += '<div class="dg-col-md-12">';
					login_content += '<input type="text" placeholder="Your Username" id="dg-reg-username" class="dg-text-center">';
				login_content += '</div>';
			login_content += '</div>';

			login_content += '<div class="dg-row dg-text-center dg-spacer">';
				login_content += '<div class="dg-col-md-12">';
					login_content += '<input type="password" class="dg-text-center" id="dg-reg-pw" placeholder="Your Password">';
				login_content += '</div>';
			login_content += '</div>';

			login_content += '<div class="dg-row dg-text-center dg-spacer">';
				login_content += '<div class="dg-col-md-12">';
					login_content += '<button class="dg-btn dg-btn-success dg-btn-sm" id="dg-login-btn" value="'+obj.login+'" type="button">Login</button><br>';
				login_content += '</div>';
			login_content += '</div>';

		login_content += '</div>';

		login_content += '<input type="hidden" id="dg-ep-nonce" value="'+ obj.ep_nonce +'">';


	var cancel_html = '<div class="dg-register">';
			cancel_html += '<div class="dg-row dg-text-center dg-spacer">';
				cancel_html += '<div class="dg-col-md-12">';
					cancel_html += '<h1><i class="fa fa-exclamation-triangle"></i> ' + obj.cancel_text + '</h1>';
				cancel_html += '</div>';
			cancel_html += '</div>';
			cancel_html += '<div class="dg-row dg-text-center dg-spacer">';
				cancel_html += '<div class="dg-col-md-12">';
					cancel_html += '<button class="dg-btn dg-btn-success dg-btn-sm" id="ep-event-cancel" type="button" value="">'+ obj.cancel_btn +'</button>';
				cancel_html += '</div>';
			cancel_html += '</div>';
		cancel_html += '</div>';
	cancel_html += '<input type="hidden" id="dg-ep-nonce" value="'+ obj.ep_nonce +'">';



	$('.ep-event-join').click(function() {

		if( ! obj.logged ){

			dg_open_popup( 600, 400, reg_content );

		}else{

			if( obj.ticket ){

				dg_open_popup( 600, 400, confirm_html );

			}else{
				window.location.href = obj.event_url + '?ep_join_event=yes&ticket=1';
			}

		}

	});

	$('.ep-event-join-cancel').click(function() {

		window.location.href = obj.event_url + '?ep_join_event_cancel=yes&ticket=1';

	});

	$(document).on( 'click', '#dg-register-btn', function() {

		if( $('#dg-reg-username').val() == '' ){
			$('#dg-reg-username').addClass( 'dg-error-border' );
			return false;
		}else{
			$('#dg-reg-username').removeClass( 'dg-error-border' );
		}

		if( $('#dg-reg-email').val() == '' ){
			$('#dg-reg-email').addClass( 'dg-error-border' );
			return false;
		}else{
			$('#dg-reg-emailusername').removeClass( 'dg-error-border' );
		}

		if( ! IsEmail( $('#dg-reg-email').val() ) ){
			$('#dg-reg-email').addClass( 'dg-error-border' );
			return false;
		}else{
			$('#dg-reg-email').removeClass( 'dg-error-border' );
		}

		if( $('#dg-reg-pw').val() == '' ){
			$('#dg-reg-pw').addClass( 'dg-error-border' );
			return false;
		}else{
			$('#dg-reg-pw').removeClass( 'dg-error-border' );
		}

		var data = {
			action: 'ep_register_user',
			nonce: $('#dg-ep-nonce').val(),
			username: $('#dg-reg-username').val(),
			email: $('#dg-reg-email').val(),
			pw: $('#dg-reg-pw').val()
		};

		replace_pop_content( '<img class="dg-loading" src="' + obj.ep_files_url + '/assets/images/loading.gif">' );

		$.post( obj.ajax_url, data, function(response) {

			if( response == 'created' ){
				replace_pop_content( confirm_html );
			}else{
				replace_pop_content( reg_content );
			}

		});

	} );

	$(document).on( 'click', '#ep-event-confirm', function() {

		var ticket = $('#dg-reg-ticket').val();
		window.location.href = obj.event_url + '?ep_join_event=yes&ticket=' + ticket;

	} );

	$(document).on( 'click', '#dg-login-pop', function() {

		replace_pop_content( login_content );

		return false;

	} );

	$(document).on( 'click', '#dg-login-btn', function() {

		if( $('#dg-reg-username').val() == '' ){
			$('#dg-reg-username').addClass( 'dg-error-border' );
			return false;
		}else{
			$('#dg-reg-username').removeClass( 'dg-error-border' );
		}

		if( $('#dg-reg-pw').val() == '' ){
			$('#dg-reg-pw').addClass( 'dg-error-border' );
			return false;
		}else{
			$('#dg-reg-pw').removeClass( 'dg-error-border' );
		}

		var data = {
			action: 'ep_login_user',
			nonce: $('#dg-ep-nonce').val(),
			username: $('#dg-reg-username').val(),
			pw: $('#dg-reg-pw').val()
		};

		replace_pop_content( '<img class="dg-loading" src="' + obj.ep_files_url + '/assets/images/loading.gif">' );

		$.post( obj.ajax_url, data, function(response) {

			if( response == 'valid' ){

				replace_pop_content( confirm_html );

			}else{

				replace_pop_content( login_content );
			}

		});

	} );


	$(document).on( 'click', '#ep-event-cancel', function() {

		var data = {
			action: 'ep_cancel_event',
			nonce: obj.ep_nonce,
			id: obj.ID,
			ticket: 1
		}

		$.post( obj.ajax_url, data, function(response) {

			if( response == 'saved' ){
				setTimeout(function() {
					$('.dg-pop-close').click();
				}, 500);
				location.reload();
			}else if( response == 'exist' ){
				replace_pop_content( obj.exist_msg );
			}

		});

	} );

});
*/