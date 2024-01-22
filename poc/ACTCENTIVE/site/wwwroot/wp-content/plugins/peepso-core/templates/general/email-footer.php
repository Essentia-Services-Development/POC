<?php
$i18n = __('This email was sent to {currentuserfullname} ({useremail}).', 'peepso-core');
$message = 'This email was sent to {currentuserfullname} ({useremail}).';

$message = PeepSo3_MultiLang__($message,'peepso-core', $user_id);
?>
<p style="margin:0; color: #80848a; font-size: 12px;"><?php echo $message; ?></p>

<?php
$i18n = __('If you do not wish to receive these emails from {sitename}, you can <a href="{unsubscribeurl}">manage your preferences</a> here.', 'peepso-core');
$message = 'If you do not wish to receive these emails from {sitename}, you can <a href="{unsubscribeurl}">manage your preferences</a> here.';

$message = PeepSo3_MultiLang__($message,'peepso-core', $user_id);
?>
<p style="margin:0; margin-top:10px; color: #62676e; font-size: 12px;"><?php echo $message; ?></p>

<?php
$i18n = __('Copyright (c) {year} {sitename}', 'peepso-core');
$message = 'Copyright (c) {year} {sitename}';

$message = PeepSo3_MultiLang__($message,'peepso-core', $user_id);
?>
<p style="margin:0; margin-top:10px; color: #62676e; font-size: 12px;"><?php echo $message; ?></p>