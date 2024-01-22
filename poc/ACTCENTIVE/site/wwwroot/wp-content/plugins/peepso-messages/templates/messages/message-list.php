<?php

$PeepSoMessages = PeepSoMessages::get_instance();
$html = '';

while ($message = $PeepSoMessages->get_next_message()) {
	ob_start();
	$PeepSoMessages->show_message($message);
	$html .= ob_get_clean();
}

if (empty($html)) {
	$html = '<div class="ps-posts__empty" style="display:block">' . __('No messages found.', 'msgso') . '</div>';
}

echo $html;
