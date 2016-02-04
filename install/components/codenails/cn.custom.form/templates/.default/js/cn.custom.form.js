var laddaLoad;

$(document)
	// Аякс отправка формы с эффектами
	.on('submit', '[data-ajax-submit]', function () {
		var $this = $(this),
			options = {
				beforeSubmit: processStart,
				success: processDone
			};

		$this.ajaxSubmit(options);

		return false;
	})
	.on('submit', '[data-ladda-submit]', function (e) {
		e.preventDefault();

		var $this = $(this),
			progress = 0,
			laddaLoad = $this.find('.ladda-button').ladda();
		laddaLoad.ladda('start');

		var interval = setInterval(function () {
			progress = Math.min(progress + Math.random() * 0.2, 1);
			laddaLoad.ladda('setProgress', progress);

			if (progress === 1) {
				laddaLoad.ladda('stop');
				clearInterval(interval);
				$this.removeAttr('data-ladda-submit');
				$this.find('.ladda-button').trigger('click');
			}
		}, 100);
	})
	.on('click', '[data-fake-form] input, [data-fake-form] textarea', function () {
		var $this = $(this),
			$fakeForm = $this.closest('[data-fake-form]'),
			formProp = $fakeForm.data('fakeForm');

		$fakeForm.removeAttr('data-fake-form').wrap('<form ' + formProp + ' />');
		$this.focus();
	})
	.on('click', '[data-cn-form-name]', function () {
		var $this = $(this),
			$prev = $this.prev('input'),
			$clone = $prev.clone();

		$clone.prop({
			value: '',
			name: $this.data('cnFormName'),
		});
		$this.before($clone);
		$prev.after('<br>');
	});


// pre-submit callback
function processStart(formData, jqForm) {
	laddaLoad = jqForm.find('.ladda-button').ladda();
	laddaLoad.ladda('start');

	return true;
}

// post-submit callback
function processDone(responseText, statusText, xhr, $form) {

	var $responseText = $(responseText),
		responseResult = ($responseText.is('form')) ? $responseText.html() : responseText;

	var progress = 0;
	var interval = setInterval(function () {
		progress = Math.min(progress + Math.random() * 0.2, 1);
		laddaLoad.ladda('setProgress', progress);

		if (progress === 1) {
			laddaLoad.ladda('stop');
			clearInterval(interval);

			// Тут что-то делаем с пришедшими данными
			if (statusText == 'success') {
				$form.html(responseResult);
			}
		}
	}, 100);
}