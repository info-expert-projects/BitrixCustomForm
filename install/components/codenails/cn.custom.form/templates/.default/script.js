if (window.frameCacheVars !== undefined) {
	BX.addCustomEvent("onFrameDataReceived", function (json) {
		cnCustomFormInit();
	});
}
else {
	jQuery(document).ready(function ($) {
		cnCustomFormInit();
	});
}

function cnCustomFormInit() {
	// Дефолтные настройки magnificpopup
	$.extend(true, $.magnificPopup.defaults, {
		tClose: 'Закрыть (Esc)',
		tLoading: 'Загрузка...',		
		ajax: {
			tError: 'Контент не загружен.'
		}
	});

	// Ajax модальные окна
	$('.btn-ajax').magnificPopup({
		type: 'ajax',
		focus: 'input'
	});
}