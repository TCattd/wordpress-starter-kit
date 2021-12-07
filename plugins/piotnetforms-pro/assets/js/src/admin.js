// import * as pdfjsLib from "pdfjs-dist";
// import './lib/pdf';
//import * as pdfWorker from "pdfjs-dist/build/pdf.worker.min.js";

//pdfjsLib.GlobalWorkerOptions.workerSrc = '../wp-content/plugins/piotnetforms-pro/assets/js/minify/pdf.worker.js';

//console.log(pdfjsLib.GlobalWorkerOptions.workerSrc);
jQuery(document).ready(function( $ ) {
	$('[data-piotnetforms-dropdown-trigger]').click( function(e) {
	    e.preventDefault();
	    $(this).closest('[data-piotnetforms-dropdown]').find('[data-piotnetforms-dropdown-content]').toggle();
	}); 
	//Upload Font
	$('#piotnetforms-pdf-upload-font').click(function(){
        var image_upload = wp.media({
			title: 'Add Font',
			button: {
				text: 'Insert Font'
			},
			multiple: false
		}).on('select', function() {
			var attachment = image_upload.state().get('selection').first().toJSON();
			jQuery('#piotnetforms-pdf-font-url').val(attachment.url);
		}).open();
		return false;
	});
	$('#piotnetforms-pdf-remove-font').click(function(){
		jQuery('#piotnetforms-pdf-font-url').val('');
		return false;
	});
});
