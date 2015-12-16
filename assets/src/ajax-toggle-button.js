$(document).ready(function() {

	var ajaxButtons = $('.widget-ajax-button');

	ajaxButtons.click(function(event) {
		event.preventDefault();

		var btn = $(this);
		var boolFormat = JSON.parse(btn.attr('data-boolean-format'));
		var ajaxUrl = btn.attr('href');
		var ajaxMethod = btn.attr('data-ajax-method');
		var ajaxData = JSON.parse(btn.attr('data-ajax-params'));
		ajaxData.value = btn.attr('data-current-value') == 1 ? 0 : 1;

		$.ajax({
			url: ajaxUrl,
			method: ajaxMethod,
			data: ajaxData,
			success: function(data, textStatus, jqXHR) {
				var newIndex = parseInt(data);
				btn.attr('data-current-value', newIndex);
				var newVal = boolFormat[newIndex];

				var span = btn.find('span');
				if (span != null) {
					span.html(newVal);
				} else {
					btn.html(newVal);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log('status: ' + textStatus + ', error: ' + errorThrown);
			}
		});

		return false;
	});

});
