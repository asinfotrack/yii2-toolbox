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

				//text content
				var span = btn.find('span');
				if (span != null) {
					span.html(newVal);
				} else {
					btn.html(newVal);
				}

				//css class
				if (newIndex == 1) {
					btn.removeClass(btn.attr('data-class-off'));
					btn.addClass(btn.attr('data-class-on'));
				} else {
					btn.removeClass(btn.attr('data-class-on'));
					btn.addClass(btn.attr('data-class-off'));
				}

				//event
				var eventSuccess = btn.attr('data-event-success');
				if (eventSuccess != null) {
					$(window).trigger(eventSuccess, [btn, JSON.parse(btn.attr('data-ajax-params')), newIndex == 1]);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log('status: ' + textStatus + ', error: ' + errorThrown);

				var eventError = btn.attr('data-event-error');
				if (eventError != null) {
					$(window).trigger(event, [btn, JSON.parse(btn.attr('data-ajax-params')), textStatus, errorThrown]);
				}
			}
		});

		return false;
	});

});
