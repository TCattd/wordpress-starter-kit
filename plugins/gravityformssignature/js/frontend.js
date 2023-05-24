/* global jQuery, Base64, SignatureEnabled, ResizeSignature, ClearSignature, LoadSignature */
/* eslint-disable new-cap */

/**
 * Handles updating of the signature field on document resize.
 *
 * @return {void}
 */
function gformSignatureResize( $instance ) {
	/**
	 * Get the cached components of the signature field.
	 *
	 * @since 4.1
	 *
	 * @param {Object} $signatureContainer jQuery signature container object.
	 * @return {Object} Signature data object.
	 */
	function getSignature( $signatureContainer ) {
		var $gfield = $signatureContainer.closest( '.gfield' );
		return {
			$container: $signatureContainer,
			fieldID: $gfield.find( '.gfield_label' ).attr( 'for' ),
			gfieldWidth: $gfield.innerWidth(),
			width: $signatureContainer.data( 'original-width' ),
			dataInput: $signatureContainer.parent().find( 'input[name$="_data"]:eq( 0 )' ),
			fieldExists: function() {
				return typeof this.fieldID !== 'undefined';
			},
			dataInputExists: function() {
				return this.dataInput.length > 0;
			}
		};
	}

	/**
	 * Get the resized width of the signature.
	 *
	 * @since 4.1
	 *
	 * @param {Object} signature The signature data object.
	 * @return {*|null} The new signature width.
	 */
	function getNewSignatureWidth( signature ) {
		return signature.gfieldWidth > signature.width ? signature.width : signature.gfieldWidth;
	}

	/**
	 * Get the resized height of the signature.
	 *
	 * @since 4.1
	 *
	 * @param {Number} resizedSignatureWidth The width of the resized signature.
	 * @param {object} signature The original signature data.
	 * @return {number} The new signature height.
	 */
	function getNewSignatureHeight( resizedSignatureWidth, signature ) {
		return Math.round( resizedSignatureWidth * signature.$container.data( 'ratio' ) );
	}

	/**
	 * Get an object representing the signature's new height and width.
	 *
	 * @since 4.1
	 *
	 * @param {Object} signature The original signature object.
	 * @return {{width: (*|null), height: number}} The resized signature data.
	 */
	function getResizedSignatureDimensions( signature ) {
		return {
			width: getNewSignatureWidth( signature ),
			height: getNewSignatureHeight( getNewSignatureWidth( signature ), signature )
		};
	}

	/**
	 * Initializes the signature resize functionality.
	 *
	 * @since 4.3
	 *
	 * @param {Object} $instance jQuery signature container object.
	 */
	function init( $instance ) {
		var $this = $instance;

		// Reset the width on the signature field containers before we update our
		// dimensions. This helps us get around a difference in how CSS grid sizes a
		// parent container's width when a child has an explicitly set width greater
		// than the parent in Firefox.
		if (
			( typeof $this.closest( '.gfield' ).find( '.gfield_label' ).attr( 'for' ) !== 'undefined' ) &&
			$this.parent().find( 'input[name$="_data"]:eq( 0 )' ).length > 0
		) {
			$this.css( 'width', '' ).find( 'canvas' ).css( 'width', '' ).attr( 'width', '' );
			$this.next().css( 'width', '' );
		}

		// Update field dimensions
		var signature = getSignature( $this );
		var resizedSignature = getResizedSignatureDimensions( signature );
		var fieldWidth = resizedSignature.width;
		var fieldHeight = resizedSignature.height > 180 ? resizedSignature.height : 180;

		if ( ! signature.fieldExists() || ! signature.dataInputExists() ) {
			return;
		}

		var decodedSignatureData = Base64.decode( signature.dataInput.val() );

		// If we have a signature, let's lock the field down and show the locked reset button.
		if ( decodedSignatureData ) {
			SignatureEnabled( signature.fieldID, false );
			jQuery( '#' + signature.fieldID + '_lockedReset' ).show();
			// If the width field setting is greater than the field's available width,
			// let's not resize the field and instead use the signed signature's dimensions.
			if ( signature.width > resizedSignature.width ) {
				var signatureDimensions = decodedSignatureData.split(';')[0];
				fieldWidth = signatureDimensions.split(',')[3];
				fieldHeight = signatureDimensions.split(',')[4];
			}
		}

		// Resize signature.
		ResizeSignature( signature.fieldID, fieldWidth, fieldHeight );
		// Set toolbar width to new signature area width
		$this.closest( '.gfield_signature_ui_container' ).find('div:last-child').css('width', resizedSignature.width);
		ClearSignature( signature.fieldID );

		if ( decodedSignatureData ) {
			LoadSignature( signature.fieldID, decodedSignatureData, 1 );
		}
	}

	init( $instance );
}

/**
 * Handles initialization of all reset buttons and resizing logic
 *
 * @since 4.4
 *
 * @param form_id Current form ID.
 */
function gformSignatureInit( parent ) {
	parent.find( '.gfield_signature_container' ).each( function() {

		var $this = jQuery( this );

		// If original width is already set, exit.
		if ( $this.data( 'original-width' ) ) {
			return;
		}

		var width = parseFloat( $this.css( 'width' ) );
		var height = parseFloat( $this.css( 'height' ) );
		var containerID = $this.attr( 'id' ).replace( '_Container', '' );
		var $resetButton = jQuery( '#' + containerID + '_resetbutton' );

		// Add a locked reset button for when a signature is present and resized.
		$resetButton.parent().append( '<button type="button" id="' + containerID + '_lockedReset" class="gform_signature_locked_reset gform-theme-no-framework" style="color: var(--gform-theme-control-description-color);display:none;height:24px;cursor:pointer;padding: 0 0 0 1.8em;opacity:0.75;font-size:0.813em;border:0;background: transparent url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0NDggNTEyIiBjbGFzcz0idW5kZWZpbmVkIj48cGF0aCBkPSJNNDAwIDIyNGgtMjR2LTcyQzM3NiA2OC4yIDMwNy44IDAgMjI0IDBTNzIgNjguMiA3MiAxNTJ2NzJINDhjLTI2LjUgMC00OCAyMS41LTQ4IDQ4djE5MmMwIDI2LjUgMjEuNSA0OCA0OCA0OGgzNTJjMjYuNSAwIDQ4LTIxLjUgNDgtNDhWMjcyYzAtMjYuNS0yMS41LTQ4LTQ4LTQ4em0tMTA0IDBIMTUydi03MmMwLTM5LjcgMzIuMy03MiA3Mi03MnM3MiAzMi4zIDcyIDcydjcyeiIgY2xhc3M9InVuZGVmaW5lZCIvPjwvc3ZnPg==) no-repeat left center;background-size:16px;float:left;direction:ltr;">' + gform_signature_frontend_strings.lockedReset + '</button>' );
		var $lockedResetButton = jQuery( '#' + containerID + '_lockedReset' );

		// Force reset button to work even when Signature is disabled.
		$resetButton.click( function() {
			SignatureEnabled( containerID, true );
			ClearSignature( containerID );
			gformSignatureResize( $this );
			// Hide the locked reset resize button
			$lockedResetButton.hide();
		} );

		$lockedResetButton.click( function() {
			jQuery( this ).hide();
			$resetButton.click();
		} );

		// Setup the "Sign Again" icons
		jQuery( '#' + containerID + '_signAgain, #' + containerID + '_lockedSignAgain' ).each( function() {
			jQuery( this ).click( function( e ) {
				e.preventDefault();
				jQuery( '#' + containerID + '_signature_image' ).hide();
				jQuery( '#' + containerID + '_Container' ).show();
			} );
		});

		// Hide the status box so that our Locked Reset button display left-aligned.
		jQuery( '#' + containerID + '_status' ).hide();

		$this.data( 'ratio', height / width );
		$this.data( 'original-width', width );

		setTimeout(function () { gformSignatureResize( $this ); }, 0 );
	} );
}

jQuery( window ).on( 'gform_post_render', function( event, form_id ) {
	const parent = jQuery( '#gform_' + form_id );
	gformSignatureInit( parent );
} );

jQuery( document ).ready( function( $ ) {

	var windowWidth = window.innerWidth

	$( window ).on( 'resize', function() {

		// Check width has actually changed as mobile devices have a variety of situations
		// in which they can trigger the resize event such as when scrolling. When this happens
		// it creates issues with the signature field functionality based on our customizations
		// to have this field be responsive.
		if ( window.innerWidth === windowWidth ) {
			return;
		}

		windowWidth = window.innerWidth;

		jQuery( '.gfield_signature_container' ).each( function() {
			var $this = jQuery( this );
			setTimeout(function () { gformSignatureResize( $this ); }, 200 );
		} );

	} );

	gform.addAction( 'gform_post_conditional_logic_field_action', function ( formId, action, targetId, defaultValues, isInit ) {
		var $this = $( targetId ).find( '.gfield_signature_container' );
		if ( ! isInit && action === 'show' && $this.length ) {
			gformSignatureResize( $this );
		}
	} );

} );
