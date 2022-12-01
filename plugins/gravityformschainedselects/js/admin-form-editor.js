/**
 * GF Chained Selects Admin
 */
( function( $ ) {

	window.GFCSAdmin = {

		getDefaultInputs: function( field ) {
			var inputs = $.extend( true, [], gformChainedSelectData.defaultInputs );
			for( var i = 0; i < inputs.length; i++ ) {
				inputs[ i ].id = field.id + "." + ( i + 1 );
			}
			return inputs;
		},

		updateAlignment: function( alignment ) {

			var field = GetSelectedField();
			field.chainedSelectsAlignment = alignment;

			$( '#field_' + field.id ).find( '.ginput_container' ).removeClass( 'horizontal vertical' ).addClass( alignment );

		}

	};

	var multipartParams = {
		gform_unique_id: generateUniqueID(),
		field_id: null,
		form_id: null
	};

	multipartParams[ '_gform_file_upload_nonce_{0}'.format( form.id ) ] = window.gform_chainedselects_file_upload_nonce;

	var uploader = new plupload.Uploader( {
		runtimes: 'html5,flash,silverlight,html4',
		browse_button: document.getElementById( 'pickfiles' ),
		container: document.getElementById( 'gfcs-container' ),
		progress: document.getElementById ( 'gfcs-progress' ),
		drop_element: document.getElementById ( 'gfcs-drop' ),
		dragdrop: true,
		url: gformChainedSelectData.fileUploadUrl,
		filters: {
			max_file_size: ( parseInt( gformChainedSelectData.maxFileSize ) / 1000000 ) + 'mb',
			mime_types: [
				{ title: '', extensions: 'csv' }
			]
		},
		flash_swf_url: '/plupload/js/Moxie.swf',
		silverlight_xap_url: '/plupload/js/Moxie.xap',
		multipart_params: multipartParams,
		init: {
			PostInit: function ( up ) {

				$( document ).bind( 'gform_load_field_settings', function( event, field, form ) {

					if( ! isCorrectField( up ) ) {
						abort( up );
					}

					if( field.type != 'chainedselect' ) {
						return;
					} else if( field.gfcsFilterEnabled && ! field.gfcsFile ) {
						var $progress = $( up.settings.progress );
						$progress.html( getFilteredFileErrorMarkup() );
						toggleDropElement( up, false );
						return;
					}

					up.field = $.extend( {}, field );

					// reset's markup
					document.getElementById( 'gfcs-progress' ).innerHTML = '';

					if( field['gfcsFile'] ) {
						updateUploadedFilePreview( up, field['gfcsFile'] );
						toggleDropElement( up, false );
					} else {
						reset( up );
					}

				} );

				$( up.settings.drop_element ).on( 'dragover', function() {
					$( this ).addClass( 'gf-dragging' );
				} ).on( 'dragleave', function() {
					$( this ).removeClass( 'gf-dragging' );
				} );

				// fixes drag and drop in IE10
				$( up.settings.drop_element ).on( {
					"dragenter": ignoreDrag,
					"dragover": ignoreDrag
				} );

				$( up.settings.progress ).on( 'click', 'span.gfcs-remove', function() {
					if( ! up.field['gfcsFilterEnabled'] ) {
						reset( up );
					}
				} );

				gform.addFilter( 'gform_duplicate_field_chainedselect', function( field ) {
					if( field.gfcsFilterEnabled ) {
						field.gfcsFile = null;
						field.gfcsFilterEnabled = false;
						field.choices = getDefaultChoices();
						field.inputs = getDefaultInputs( field );
					}
					return field;
				} );

			},
			FilesAdded: function ( up, files ) {

				toggleDropElement( up, false );

				var max           = 1,
					totalCount    = up.files.length,
					isMaxExceeded = totalCount > max;

				files = up.files;

				if( isMaxExceeded ) {
					var lastIndex = files.length - 1;
					$.each( files, function ( i, file ) {
						if( i < lastIndex ) {
							up.removeFile( file );
						}
					} );
				}

				$.each( files, function ( i, file ) {

					if( ( file.status == plupload.FAILED ) ) {
						up.removeFile( file );
						return;
					}

					updateUploadedFilePreview( up, file );

					$( '#' + file.id ).attr( 'class', getStatusClass( file ) );

					var multipartParams = up.getOption( 'multipart_params' );
					multipartParams.field_id = field.id;
					multipartParams.form_id  = form.id;
					multipartParams.original_filename = file.name;
					up.setOption( 'multipart_params', multipartParams );

				} );

				// Reposition Flash
				up.refresh();

				up.start();

			},
			UploadProgress: function ( up, file ) {

				if( ! isCorrectField( up ) ) {
					abort( up );
					return;
				}

				document.getElementById( file.id ).getElementsByTagName( 'b' )[0].innerHTML =  '<span>' + file.percent + '%</span>';

				$( '#' + file.id ).attr( 'class', getStatusClass( file ) );

			},
			Error: function ( up, err ) {
				reset( up );
				var message = err.message;
				if( err.code == -601 ) {
					message = gformChainedSelectData.strings.errorFileType;
				} else if( err.code == -600 ) {
					message = gformChainedSelectData.strings.errorFileSize;
				}
				displayError( up, gformChainedSelectData.strings.errorUploadingFile + '<br>Error: ' + err.code + ', Message: ' + message );
			},
			FileUploaded: function ( up, file, result ) {

				var response;

				try {
					response = $.secureEvalJSON( result.response );
				} catch( e ) {
					response = { status: 'error', error: { message: false } };
				}
				if( response.status == 'error' ) {
					reset( up );
					displayError( up, gformChainedSelectData.strings.errorProcessingFile + '<br>Error: ' + response.error.code + ', Message: ' + response.error.message );
					return;
				}

				if( file.percent == 100 && response.status && response.status == 'ok' ) {

					var field = GetSelectedField();

					field.choices  = response.data.choices;
					field.inputs   = response.data.inputs;
					field.gfcsFile = {
						name: file.name,
						type: file.type,
						size: file.size,
						dateUploaded: Math.round( ( new Date() ).getTime() / 1000 ),
						isFromFilter: false
					};

					updateFieldPreview( field );

					$( '#' + file.id ).attr( 'class', getStatusClass( file ) );

				}

			}
		}
	} );

	function generateUniqueID() {
			return 'xxxxxxxx'.replace( /[xy]/g, function ( c ) {
				var r = Math.random() * 16 | 0, v = c == 'x' ? r : r & 0x3 | 0x8;
				return v.toString( 16 );
			} );
		}

	function ignoreDrag( e ) {
		e.preventDefault();
	}

	function toggleDropElement( up, isEnabled ) {

		var $drop   = $( up.settings.drop_element ),
			$sample = $( '#gfcs-sample' );

		$drop.removeClass( 'gf-dragging' );

		if( isEnabled ) {
			$sample.show();
			$drop.show();
		} else {
			$sample.hide();
			$drop.hide();
		}

	}

	function getFileMarkup( file ) {

		var size          = plupload.formatSize( file.size ).toUpperCase(),
			css           = getStatusClass( file ),
			dateUploaded  = file.dateUploaded ? file.dateUploaded : Math.round( Date.now() / 1000 ),
			removeButton  = file.isFromFilter ? '' : '<span class="gfcs-remove"><i class="gficon-subtract"></i></span>',
			sourceMessage = file.isFromFilter ? getFilteredFileMarkup( file ) : '';

		return '\
			<div id="{0}" class="{3}"> \
				<span class="gfcs-file-icon"></span> {1} <span class="gfcs-file-size">{2}</span> <span class="gfcs-file-date"> | {4}</span> <b class="gfcs-file-percent"></b> \
				<span class="gfcs-success"><i class="gficon-tick gf_valid"></i></span> \
				{5} \
				<span class="gfcs-processing"></span> \
			</div> \
			{6}'
			.format( file.id, file.name, size, css, timeAgo( dateUploaded ), removeButton, sourceMessage );
	}

	function getFilteredFileMarkup() {
		return '<div class="gfcs-source-message gforms_help_alert"><i class="fa fa-warning"></i> {0}</div>'.format( gformChainedSelectData.strings.importedFilterFile );
	}

	function getFilteredFileErrorMarkup() {
		return '<div class="gfcs-source-message gforms_red_alert"><i class="fa fa-warning"></i> {0}</div>'.format( gformChainedSelectData.strings.errorImportingFilterFile );
	}

	function updateUploadedFilePreview( up, file ) {
		var $progress = $( up.settings.progress );
		if( file ) {
			$progress.html( getFileMarkup( file ) );
		} else {
			$progress.html( '' );
		}
	}

	function updateFieldPreview( field ) {

		var $inputContainer = $( '#field_' + field.id + ' .ginput_container' ),
			markup          = '';

		for( var i = 0; i < field.inputs.length; i++ ) {
			var options = '<option>' + field.inputs[i].label + '</option>';
			markup += '<span><select disabled="disabled">' + options + '</select></span>' + "\n";
		}

		$inputContainer.html( markup );

	}

	function isCorrectField( up ) {
		var field = GetSelectedField();
		return field && ( typeof up.field == 'undefined' || up.field.id == field.id );
	}

	function abort( up ) {
		up.stop();
		up.refresh();
		up.start();
	}

	function reset( up ) {

		up.files.splice( 0, up.files.length );
		up.refresh();
		up.start();

		var field = GetFieldById( up.field.id );
		field.choices  = gformChainedSelectData.defaultChoices;
		field.inputs   = getDefaultInputs( field );
		field.gfcsFile = null;

		updateFieldPreview( field );
		updateUploadedFilePreview( up, false );
		toggleDropElement( up, true );

	}

	function getDefaultChoices() {
		return gformChainedSelectData.defaultChoices;
	}

	function getDefaultInputs( field ) {
		var inputs = $.extend( true, [], gformChainedSelectData.defaultInputs );
		for( var i = 0; i < inputs.length; i++ ) {
			inputs[ i ].id = field.id + "." + ( i + 1 );
		}
		return inputs;
	}

	function getStatusClass( file ) {
		return 'gfcs-status-{0}'.format( getStatus( file ) );
	}

	function getStatus( file ) {

		var status = '';

		if( file.status && file.status == plupload.UPLOADING ) {
			if( file.percent == 100 ) {
				status = 'processing';
			} else {
				status = 'uploading';
			}
		} else {
			status = 'complete';
		}

		return status;
	}

	function displayError( up, message ) {

		// clear existing timeout and error (if needed)
		delete window[ 'gfcsErrorTimeout' ];
		$( '#gfcs-error' ).remove();

		var $error = $( '<div id="gfcs-error" class="error" style="width:375px;padding:1px 12px;"><p>{0}</p></div>'.format( message ) );

		$( up.settings.progress ).html( $error );

		window[ 'gfcsErrorTimeout' ] = setTimeout( function() {
			$error.slideUp( function() {
				$error.remove();
			} );
		}, 10000 );

	}

	function timeAgo( timestamp ) {

		var diff = ( Date.now() / 1000 ) - timestamp,
			formats = {
				hours:   { period: 60 * 60, label: { singular: 'hour', plural: 'hours' } },
				minutes: { period: 60,      label: { singular: 'min', plural: 'mins' } },
				seconds: { period: 1,       label: { singular: 'sec', plural: 'secs' } }
			};

		for ( var key in formats ) {

			if( ! formats.hasOwnProperty( key ) ) {
				continue;
			}

			var format = formats[ key ],
				count  = Math.round( diff / format.period ),
				output = '';

			if( key == 'hours' && count >= 24 ) {
				break;
			} else if( key == 'seconds' && count == 0 ) {
				output = '{0} {1} ago'.format( 1, format.label.singular );
			} else if( count > 0 ) {
				var label = count > 1 ? format.label.plural : format.label.singular;
				output = '{0} {1} ago'.format( count, label );
				break;
			}

		}

		if( ! output ) {

			var date     = new Date( timestamp * 1000 ),
				dateBits = date.toDateString().split( ' ' );

			output = '{0} {1}, {2}'.format( dateBits[1], dateBits[2], dateBits[3] );

		}

		return output;
	}

	uploader.init();

} )( jQuery );