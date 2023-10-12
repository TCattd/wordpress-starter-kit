/*
 +=====================================================================+
 |    ____          _        ____             __ _ _                   |
 |   / ___|___   __| | ___  |  _ \ _ __ ___  / _(_) | ___ _ __         |
 |  | |   / _ \ / _` |/ _ \ | |_) | '__/ _ \| |_| | |/ _ \ '__|        |
 |  | |__| (_) | (_| |  __/ |  __/| | | (_) |  _| | |  __/ |           |
 |   \____\___/ \__,_|\___| |_|   |_|  \___/|_| |_|_|\___|_|           |
 |                                                                     |
 |  (c) Jerome Bruandet ~ https://code-profiler.com/                   |
 +=====================================================================+
*/

// =====================================================================
// Generic stuff

// TipTip tooltip
jQuery( document ).ready(function( $ ) {
	'use strict';
	$('.code-profiler-tip').tipTip( {
		'attribute': 'data-tip',
		'fadeIn': 50,
		'fadeOut': 50,
		'delay': 100,
		'maxWidth' : '350px'
	});

	// We remove the '&action=delete_profiles...' query string because
	// if the user reloaded the page that would throw an error:
	var url = window.location.href;
	window.history.replaceState({},
		document.title,
		url.replace( /&action=delete_profiles&.+/, '')
	);

});

// Append/clear the search query to/from the query string
function cpjs_search_query() {

	'use strict';
	var query;
	if ( jQuery('#search_id-search-input').val().length > 0 ) {
		query = '&s=' + jQuery('#search_id-search-input').val();
		// Case sensitivity
		if ( jQuery('#case-search-input').is(':checked') === true ) {
			query += '&c=1';
		}
		jQuery('#profile-form').attr('action', jQuery('#profile-form').attr('action')+ query );

	} else {
		jQuery('#profile-form').attr('action','');
		var url = window.location.href;
		window.history.replaceState({},
			document.title,
			url.replace( /&s=[^&$]+/, '')
		);
		var url = window.location.href;
		window.history.replaceState({},
			document.title,
			url.replace( /&c=1/, '')
		);
	}
}

// =====================================================================
// Summary page

function cpjs_front_or_backend( item ) {
	'use strict';
	// Enable/disable frontend/backend select box
	// depending on user's choice
	if ( item == 1 ) {
		jQuery('#p-frontend').show();
		jQuery('#p-backend').hide();
		jQuery('#p-custom').hide();
		jQuery('#id-frontend').focus();
		jQuery('#user-unauthenticated').prop('disabled', false);
	} else if ( item == 2 ) {
		jQuery('#p-backend').show();
		jQuery('#p-frontend').hide();
		jQuery('#p-custom').hide();
		jQuery('#id-backend').focus();
		jQuery('#user-unauthenticated').prop('disabled', true);
		jQuery('#user-authenticated').prop('checked', true);
		jQuery('#user-name').prop('disabled', false);
	} else {
		jQuery('#p-custom').show();
		jQuery('#p-frontend').hide();
		jQuery('#p-backend').hide();
		jQuery('#id-custom').focus();
		jQuery('#user-unauthenticated').prop('disabled', false);
	}
}

function cpjs_authenticated( id ) {
	if ( id == 1 ) {
		jQuery('#user-name').prop('disabled', false);
		jQuery('#user-name').focus();
	} else {
		jQuery('#user-name').prop('disabled', true);
	}
}

function cpjs_show_adv_settings() {
	jQuery('#cp-advanced-settings').slideDown();
	jQuery('#button-adv-settings').prop('disabled', true);
}

function cpjs_get_post( id ) {
	if ( id == 1 ) {
		jQuery('#post-value').prop('disabled', false);
		jQuery('#post-value').focus();
	} else {
		jQuery('#post-value').prop('disabled', true);
	}
}

// =====================================================================
// Log page: filter and delete the log.

function cpjs_filter_log() {
	'use strict';
	// Create bitmask
	var bitmask = 0;
	if ( document.cplogform.info.checked == true )  { bitmask += 1; }
	if ( document.cplogform.warn.checked == true )  { bitmask += 2; }
	if ( document.cplogform.error.checked == true ) { bitmask += 4; }
	if ( document.cplogform.debug.checked == true ) { bitmask += 8; }

	// Clear the textarea
	document.cplogform.cptxtlog.value = '';

	// Browser through our array and return only selected verbosity
	var cp_count = 0;
	var i = 0;
	for ( i = 0; i < cplog_array.length; ++i ) {
		var line = decodeURIComponent( cplog_array[i] );
		var line_array = line.split('~~', 2 );
		if ( line_array[0] & bitmask ) {
			document.cplogform.cptxtlog.value += line_array[1];
			++cp_count;
		}
	}
	if ( cp_count == 0 ) {
		document.cplogform.cptxtlog.value = '\n  > ' + cpi18n.empty_log;
	}
}

function cpjs_delete_log() {
	'use strict';
	if ( confirm( cpi18n.delete_log ) ) {
		return true;
	}
	return false;
}
// =====================================================================
// AJAX call to start the profiler.

function cpjs_start_profiler() {
	'use strict';
	// Get the nonce
	var cp_nonce = jQuery('#cp_nonce').val();
	if ( cp_nonce == '') {
		alert( cpi18n.missing_nonce );
		return;
	}

	// Get the scan type (frontend/backend)
	var post;
	var where = jQuery('input[name="where"]:checked').val();
	if ( where == 'frontend') {
		post = jQuery('#id-frontend').val();

	} else if ( where == 'backend') {
		post = jQuery('#id-backend').val();

	} else if ( where == 'custom') {
		post = jQuery('#id-custom').val().trim(); // Trim user input

	} else {
		alert( cpi18n.missing_frontbackend );
		return;
	}
	if ( post == '') {
		alert( cpi18n.missing_post );
		return;
	}

	// Get the profile's name
	var profile = jQuery('input[name="profile"]').val();
	if ( profile == '') {
		alert( cpi18n.missing_profilename );
		jQuery('input[name="profile"]').focus();
		return;
	}

	var user = jQuery('input[name="user"]:checked').val();
	if ( user == '') {
		alert( cpi18n.missing_userauth );
		return;
	}

	var username = jQuery('#user-name').val().trim(); // Trim user input
	if ( user == 'authenticated') {
		if ( username == '') {
			alert( cpi18n.missing_username );
			jQuery('#user-name').focus();
			return;
		}
	}

	var ua = jQuery('#ua-id').val();
	if ( ua == 'undefined') {
		ua = 'FireFox';
	}

	// GET or POST
	var method = jQuery('input[name="method"]:checked').val();
	var payload = '';
	if ( method == 'post') {
		// Payload
		payload = jQuery('#post-value').val();
	} else {
		method = 'get';
	}

	var cookies = jQuery('#cp-cookies').val();
	var headers = jQuery('#custom-headers').val();

	// Change buttons status and add animated image with status message
	jQuery('#start-profile').prop('disabled', true);
	jQuery('#cp-progress-div').slideDown();
	jQuery('#progress-gif').slideDown();
	jQuery('#progress-text').slideDown();
	jQuery('#code-profiler-error').slideUp();
	jQuery('#cp-span-progress').css('width', '30%');

	var data = {
		'action': 'codeprofiler_start_profiler',
		'cp_nonce': cp_nonce,
		'where': where,
		'post': post,
		'profile': profile,
		'user': user,
		'username': username,
		'cookies': cookies,
		'custom_headers': headers,
		'method': method,
		'payload': payload,
		'ua': ua
	};
	// Send the request via AJAX
	jQuery.ajax( {
		type: 'POST',
		url: ajaxurl,
		data: data,
		dataType: 'json',
		success: function( response ) {

			if (typeof response === 'undefined') {
				cpjs_start_error('<p>'+ cpi18n.unknown_error +'</p>');
				return;
			}

			if ( response.status != 'success') {
				cpjs_start_error('<p>'+ response.message +'</p>');
				return;
			}

			// ------------------------------------------------------------
			// Inform the user we're moving to step 2
			jQuery('#cp-span-progress').css('width', '70%');
			jQuery('#progress-text').html( cpi18n.preparing_report );

			data['action'] = 'codeprofiler_prepare_report';
			data['microtime'] = response.microtime;
			jQuery.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: data,
				dataType: 'json',
				success: function( response ) {

					if (typeof response === 'undefined') {
						cpjs_start_error('<p>'+ cpi18n.unknown_error +'</p>');
						return;
					}

					if ( response.status != 'success') {
						cpjs_start_error('<p>'+ response.message +'</p>');
						return;
					}
					var cp_uri;
					if (response.cp_profile !== 'undefined' && response.cp_profile != '') {
						cp_uri = '&cptab=profiles_list&action=view_profile&id=' + response.cp_profile + '&section=1';
					} else {
						cp_uri = '&cptab=profiles_list';
					}

					// All good, redirect to the Profiles List tab
					jQuery('#cp-span-progress').css('width', '100%');
					window.location.href = window.location.href + cp_uri;
					return;

				},

				// Display non-200 HTTP response
				error: function( xhr, status, err ) {
					if ( err != '') {
						if (  xhr.status != 0 ) {
							var message = cpi18n.http_error +' '+ xhr.status +' '+ err;
							// Timeout
							if ( xhr.status == 503 || xhr.status == 504 ) {
								cpjs_start_error('<p>'+ message +'<br />'+ cpi18n.timeout_error +'.</p>');
							// Redirections, not found
							} else if ( xhr.status == 301 || xhr.status == 302 || xhr.status == 404 ) {
								cpjs_start_error('<p>'+ message +'<br />'+ cpi18n.notfound_error +'.</p>');
							// Forbidden
							} else if ( xhr.status == 403 ) {
								cpjs_start_error('<p>'+ message +'<br />'+ cpi18n.forbidden_error +'.</p>');
							// Internal error
							} else if ( xhr.status == 500 ) {
								cpjs_start_error('<p>'+ message +'<br />'+ cpi18n.internal_error +'.<br />'+ cpi18n.timeout_error +'.</p>');
							} else {
								cpjs_start_error('<p>'+ cpi18n.http_error +' '+ xhr.status +' '+ err +'.</p>');
							}
							return;

						} else {
							cpjs_start_error('<p>'+ cpi18n.unknown_error +' '+ err +'.</p>');
							return;
						}
					}
					return;
				}
			});
			// ------------------------------------------------------------
		},

		// Display non-200 HTTP response
		error: function( xhr, status, err ) {
			if ( err != '') {
				if (  xhr.status != 0 ) {
					var message = cpi18n.http_error +' '+ xhr.status +' '+ err;
					// Timeout
					if ( xhr.status == 503 || xhr.status == 504 ) {
						cpjs_start_error('<p>'+ message +'<br />'+ cpi18n.timeout_error +'.</p>');
					// Redirections, not found
					} else if ( xhr.status == 301 || xhr.status == 302 || xhr.status == 404 ) {
						cpjs_start_error('<p>'+ message +'<br />'+ cpi18n.notfound_error +'.</p>');
					// Forbidden
					} else if ( xhr.status == 403 ) {
						cpjs_start_error('<p>'+ message +'<br />'+ cpi18n.forbidden_error +'.</p>');
					// Internal error
					} else if ( xhr.status == 500 ) {
						cpjs_start_error('<p>'+ message +'<br />'+ cpi18n.internal_error +'.<br />'+ cpi18n.timeout_error +'.</p>');
					} else {
						cpjs_start_error('<p>'+ cpi18n.http_error +' '+ xhr.status +' '+ err +'.</p>');
					}
					return;

				} else {
					cpjs_start_error('<p>'+ cpi18n.unknown_error +' '+ err +'.</p>');
					return;
				}
			}
			return;
		}
	});
}

// Display error
function cpjs_start_error( error ) {
	'use strict';
	jQuery('#start-profile').prop('disabled', false);
	jQuery('#progress-gif').slideUp();
	jQuery('#progress-text').slideUp();
	jQuery('#cp-span-progress').css('width', '0%');
	jQuery('#code-profiler-error').html( error );
	jQuery('#code-profiler-error').slideDown();
	jQuery('#cp-progress-div').slideUp();

}

// =====================================================================
// Profiles List page.

function cpjs_delete_profile() {
	'use strict';
	if ( confirm( cpi18n.delete_profile ) ) {
		return true;
	}
	return false;
}

// Hide/show the edit box and buttons when renaming a profile.
function cpjs_toggle_name( row ) {
	if ( jQuery('#profile_div_'+ row).css('display') == 'none') {
		jQuery('#profile_name_'+ row).hide();
		jQuery('#profile_div_'+ row).show();
		jQuery('#edit-'+ row).val( jQuery('#profile_name_'+ row).html() );
		jQuery('#edit-'+ row).focus();
	} else {
		jQuery('#profile_div_'+ row).hide();
		jQuery('#profile_name_'+ row).show();
	}
}

// Edit the profile name via AJAX endpoint.
function cpjs_edit_name( profile, id, cp_nonce, row ) {

	'use strict';

	if ( cp_nonce == '') {
		alert( cpi18n.missing_nonce );
		return;
	}
	if ( profile == '') {
		alert( cpi18n.missing_profileid );
		return;
	}

	var new_name = jQuery('#edit-'+ id).val().trim();
	if ( new_name == '') {
		alert( cpi18n.missing_profilename );
		return;
	}

	jQuery('#profile_spinner_'+ row).addClass('is-active');

	var data = {
		'action':	'codeprofiler_rename',
		'cp_nonce':	cp_nonce,
		'new_name':	new_name,
		'profile':	profile,
	};

	// Send the request via AJAX
	jQuery.ajax( {
		type: 'POST',
		url: ajaxurl,
		data: data,
		dataType: 'json',
		success: function( response ) {
			jQuery('#profile_spinner_'+ row ).removeClass('is-active');

			if (typeof response === 'undefined') {
				alert( cpi18n.unknown_error );
				return;
			}

			if ( response.status != 'success') {
				alert( response.message );
				return;
			}

			// Success: update the name in the table
			jQuery('#profile_name_'+ id).html( response.newname );
			cpjs_toggle_name(row);
		},
		// Display non-200 HTTP response
		error: function( xhr, status, err ) {
			jQuery('#profile_spinner_'+ row ).removeClass('is-active');
			if ( err != '') {
				if (  xhr.status != 0 ) {
					var message = cpi18n.http_error +' '+ xhr.status +' '+ err;
					// Timeout
					if ( xhr.status == 503 || xhr.status == 504 ) {
						alert( message +'<br />'+ cpi18n.timeout_error );
					// Redirections, not found
					} else if ( xhr.status == 301 || xhr.status == 302 || xhr.status == 404 ) {
						alert( message +'<br />'+ cpi18n.notfound_error );
					// Forbidden
					} else if ( xhr.status == 403 ) {
						alert( message +'<br />'+ cpi18n.forbidden_error );
					// Internal error
					} else if ( xhr.status == 500 ) {
						alert( message +'<br />'+ cpi18n.internal_error +'.<br />'+ cpi18n.timeout_error );
					} else {
						alert( cpi18n.http_error +' '+ xhr.status +' '+ err );
					}
					return;

				} else {
					alert( cpi18n.unknown_error +' '+ err );
					return;
				}
			}
			return;
		}
	});
}

// =====================================================================
// Plugins/Theme chart.

function cpjs_plugins_chart( caxis, clabel, cdata, ctotal_time ) {
	'use strict';
	// Switch between vertical and horizontal bars
	var _indexAxis = caxis;
	document.getElementById('htov').onclick = function() {
		if ( _indexAxis == 'x') {
			_indexAxis = 'y';
		} else {
			_indexAxis = 'x';
		}
		myChart.destroy();
		myChart = new Chart(ctx, {
			type: 'bar',
			options: {
				animation: {
					duration: 1000,
					onComplete: function() {
						document.getElementById('download-png-img').href = myChart.toBase64Image();
					}
				},
				indexAxis: _indexAxis,
				plugins: chartPlugins
			},
			plugins:[ plugin],
			data: chartData
		});
	};

	var ctx = document.getElementById('myChart').getContext('2d');

	var chartData =    {
		labels: clabel,
		datasets: [{
			label: cpi18n.exec_sec_plugins,
			backgroundColor: 'rgba(204, 0, 0, .5)',
			borderColor: '#c00',
			borderWidth: 2,
			hoverBackgroundColor: '#B45252',
			data: cdata
	  }]
	};

	const total_time = ctotal_time;
	const footer = (tooltipItems) => {
		var sum;
		tooltipItems.forEach(function(tooltipItem) {
			if ( _indexAxis == 'x') {
				sum = Math.round( ( tooltipItem.parsed.y / total_time ) * 100 );
			} else {
				sum = Math.round( ( tooltipItem.parsed.x / total_time ) * 100 );
			}
		});
		return sum + cpi18n.pc_plugins;
	};

	var chartPlugins = {
		title: {
			display: true,
			text: cpi18n.exec_tot_plugins_1 +' (' + cpi18n.chart_total +' '+ ctotal_time +')'
		},
		legend: {
			display: false
		},
		tooltip: {
			borderWidth: 1,
			borderColor: '#666',
			displayColors: false,
			backgroundColor: '#F5F5B5',
			titleColor:'#666',
			padding: 8,
			footerColor: '#666',
			callbacks: {
				footer: footer,
				labelTextColor: function(context) {
					return '#543453';
				}
			}
		}
	}

	// We want a background colour for the downloaded PNG file
	const plugin = {
		id: 'custom_canvas_background_color',
		beforeDraw: (chart) => {
		const ctx = chart.canvas.getContext('2d');
		 ctx.save();
		 ctx.globalCompositeOperation = 'destination-over';
		 ctx.fillStyle = '#f0f0f1',
		 ctx.fillRect(0, 0, chart.width, chart.height);
		 ctx.restore();
	  }
	}

	var myChart = new Chart(ctx, {
		type: 'bar',
		data: chartData,
		options: {
			animation: {
				duration: 700,
				onComplete: function() {
					jQuery('#cp-footer-buttons').slideDown(200);
					document.getElementById('download-png-img').href = myChart.toBase64Image();
				}
			},
			indexAxis: _indexAxis,
			plugins: chartPlugins
		},
		plugins:[plugin]
	});
}

// =====================================================================
// File I/O chart.

function cpjs_iostats_chart( caxis, clabel, cdata, ctotal_calls ) {
	'use strict';
	// Switch between vertical and horizontal bars
	var _indexAxis = caxis;
	document.getElementById('htov').onclick = function() {
		if ( _indexAxis == 'x') {
			_indexAxis = 'y';
		} else {
			_indexAxis = 'x';
		}
		myChart.destroy();
		myChart = new Chart(ctx, {
			type: 'line',
			options: {
				animation: {
					duration: 1000,
					onComplete: function() {
						document.getElementById('download-png-img').href = myChart.toBase64Image();
					}
				},
				indexAxis: _indexAxis,
				plugins: chartPlugins
			},
			plugins:[ plugin],
			data: chartData
		});
	};

	var ctx = document.getElementById('myChart').getContext('2d');

	var chartData =    {
		labels: clabel,
		datasets: [{
			label: cpi18n.iolist_total_calls,
			backgroundColor: 'rgba(34, 113, 177, .5)',
			borderColor: '#2271B1',
			borderWidth: 2,
			hoverBackgroundColor: '#FF0000',
			data: cdata,
			fill: 'origin',
			radius: 6,
			pointBackgroundColor: '#B45252'
	  }]
	};
	var chartPlugins = {
		title: {
			display: true,
			text: cpi18n.io_calls +' (' + cpi18n.chart_total +' '+ ctotal_calls +')'
		},
		legend: {
			display: false
		},
		tooltip: {
			borderWidth: 1,
			borderColor: '#666',
			displayColors: false,
			backgroundColor: '#F5F5B5',
			titleColor:'#666',
			padding: 8,
			footerColor: '#666',
			callbacks: {
				labelTextColor: function(context) {
					return '#543453';
				}
			}
		}
	}
	// We want a background colour for the downloaded PNG file
	const plugin = {
		id: 'custom_canvas_background_color',
		beforeDraw: (chart) => {
		const ctx = chart.canvas.getContext('2d');
		 ctx.save();
		 ctx.globalCompositeOperation = 'destination-over';
		 ctx.fillStyle = '#f0f0f1',
		 ctx.fillRect(0, 0, chart.width, chart.height);
		 ctx.restore();
	  }
	}

	var myChart = new Chart(ctx, {
		type: 'line',
		data: chartData,
		options: {
			animation: {
				duration: 700,
				onComplete: function() {
					jQuery('#cp-footer-buttons').slideDown(200);
					document.getElementById('download-png-img').href = myChart.toBase64Image();
				}
			},
			indexAxis: _indexAxis,
			plugins: chartPlugins
		},
		plugins:[plugin]
	});

}
// =====================================================================
// Disk I/O chart.

function cpjs_diskio_chart( caxis, clabel, cdata ) {
	'use strict';
	// Switch between vertical and horizontal bars
	var _indexAxis = caxis;
	document.getElementById('htov').onclick = function() {
		if ( _indexAxis == 'x') {
			_indexAxis = 'y';
		} else {
			_indexAxis = 'x';
		}
		myChart.destroy();
		myChart = new Chart(ctx, {
			type: 'line',
			options: {
				animation: {
					duration: 1000,
					onComplete: function() {
						document.getElementById('download-png-img').href = myChart.toBase64Image();
					}
				},
				indexAxis: _indexAxis,
				plugins: chartPlugins
			},
			plugins:[ plugin],
			data: chartData
		});
	};
	var ctx = document.getElementById('myChart').getContext('2d');

	var chartData =    {
		labels: clabel,
		datasets: [{
			label: cpi18n.disk_io_bytes,
			backgroundColor: 'rgba(143, 240, 164, .5)',
			borderColor: '#41B141',
			borderWidth: 2,
			hoverBackgroundColor: '#FF0000',
			data: cdata,
			fill: 'origin',
			radius: 6,
			pointBackgroundColor: '#B45252'
	  }]
	};
	var chartPlugins = {
		title: {
			display: true,
			text: cpi18n.disk_io_title,
		},
		legend: {
			display: false
		},
		tooltip: {
			borderWidth: 1,
			borderColor: '#666',
			displayColors: false,
			backgroundColor: '#F5F5B5',
			titleColor:'#666',
			padding: 8,
			footerColor: '#666',
			callbacks: {
				labelTextColor: function(context) {
					return '#543453';
				}
			}
		}
	}
	// We want a background colour for the downloaded PNG file
	const plugin = {
		id: 'custom_canvas_background_color',
		beforeDraw: (chart) => {
		const ctx = chart.canvas.getContext('2d');
		 ctx.save();
		 ctx.globalCompositeOperation = 'destination-over';
		 ctx.fillStyle = '#f0f0f1',
		 ctx.fillRect(0, 0, chart.width, chart.height);
		 ctx.restore();
	  }
	}

	var myChart = new Chart(ctx, {
		type: 'line',
		data: chartData,
		options: {
			animation: {
				duration: 700,
				onComplete: function() {
					jQuery('#cp-footer-buttons').slideDown(200);
					document.getElementById('download-png-img').href = myChart.toBase64Image();
				}
			},
			indexAxis: _indexAxis,
			plugins: chartPlugins
		},
		plugins:[plugin]
	});
}

// =====================================================================
// Settings page

function cpjs_copy_textarea( id ) {
	document.getElementById(id).select();
	document.execCommand('copy');
	alert( cpi18n.text_copied );
}

// =====================================================================
// EOF
