

jQuery(document).ready(function() {
	jQuery.each(ConsultaPublicaReport.actions, function(index, value) {
		jQuery('<option>').val(index).text(value.label).appendTo("select[name='action']");
		jQuery('<option>').val(index).text(value.label).appendTo("select[name='action2']");
	});
	
});


