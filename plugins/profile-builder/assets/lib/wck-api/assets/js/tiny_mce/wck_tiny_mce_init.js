function wckInitTinyMCE( element ){ 
	tinyMCE.init({
		// General options
		mode : "specific_textareas",
		theme : "advanced",
		editor_selector : element,
		add_form_submit_trigger : false,
		width: "100%",

		// Theme options
		theme_advanced_buttons1:"bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink",
		theme_advanced_buttons2:"formatselect,underline,justifyfull,forecolor,|,pastetext,pasteword,removeformat,|,charmap,|,outdent,indent,|,undo,redo,code",
		theme_advanced_buttons3:"",
		theme_advanced_buttons4:"",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		// Skin options
		skin : "wp_theme",
		language : "en",
		spellchecker_languages : "+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv"
		
	});
}