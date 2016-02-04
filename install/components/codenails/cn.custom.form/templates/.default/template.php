<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

$this->setFrameMode(true);
$errors = false;
?>
<?if ($arResult['POST_SUCCESS']) {
	$modalTitle = 'Готово!';
} else {
	$modalTitle = 'Заказать звонок';
}
if ($arResult['ERRORS']) {
	$modalTitle = 'Произошла ошибка';
	$errText = array();
	$errors .= '<div class="cn-modal-info cn-modal-error"><ul class="cn-modal-errors">';

	// Обрабатываем ошибки
	if ($arResult['ERRORS']['NAME']) {
		switch ($arResult['ERRORS']['NAME']) {
			case 'WRONG':
				$errText[] = '<li>Некорректный адрес почты.</li>';
				break;

			case 'EMPTY':
				$errText[] = '<li>Вы не указали email.</li>';
				break;

			default:
				$errText[] = '<li>Проверьте правильность ввода данных</li>';
				break;
		}
	}

	if ($arResult['ERRORS']['IBLOCK'] == 'IBLOCK_ADD_ERROR') {
		$errText[] = '<li>Ошибка добавления, попробуйте ещё раз.</li>';
	}

	if ($arResult['ERRORS']['MAIL'] == 'SEND_EMAIL_ERROR') {
		$errText[] = '<li>Ошибка отсылки email. <br> Но не переживайте, сообщение сохранено на сайте и мы его обязательно прочтём.</li>';
	}

	$errors .= implode('', $errText);

	$errors .= '</ul></div>';
}

?>
<?=$arParams['FORM']['HTML_OPEN']?>
	<div class="cn-modal col-4">
		<div class="mfp-close cn-modal-close">&times;</div>

		<div class="cn-modal-header">
			<?=$modalTitle?>
		</div> <!-- .cn-modal-header -->
		<div class="cn-modal-content clearfix">
			<?if ($arResult['POST_SUCCESS']): ?>
				<div class="cn-modal-info cn-modal-success">
					Спасибо за заявку!
				</div>
			<?else: ?>
				<?=$errors?>
				<?foreach ($arResult['FIELDS'] as $key => $field): ?>
					<?switch ($field['TYPE']) {
						case 'radio':
						case 'checkbox':
							if (is_array($field['HTML'])) {
								echo "<p>";
								foreach ($field['HTML'] as $key => $value) {
									echo $value['TAG'] . '<label for="' . $value['FOR'] . '"><span></span>' . $value['LABEL'] . '</label>';
								}
								echo "</p>";
							} else {
								echo "<p>";
								echo $field['HTML'] . '<label for="' . $field['FOR'] . '"><span></span>' . $field['LABEL'] . '</label>';
								echo "</p>";
							}
							break;

						case 'hidden':
							echo $field['HTML'];
							break;

						case 'select':
							echo '<p>' . $field['HTML'] . '</p>';
							break;

						default:
							echo '<p>' . $field['HTML'] . '</p>';
							break;
					}?>
				<?endforeach?>
				<button class="btn ladda-button" type="submit" name="submit_<?=$arParams['FORM_CODE']?>" data-style="zoom-out"><span class="ladda-label">Отправить</span></button>
			<?endif?>
		</div> <!-- .cn-modal-content -->

	</div> <!-- .cn-modal -->
	<?foreach ($arResult['HIDDEN_FIELDS'] as $key => $field) {
	echo $field['HTML'];
}?>
<?=$arParams['FORM']['HTML_CLOSE']?> <!-- #<?=$arParams['FORM']['FORM_ID']?> -->
