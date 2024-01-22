<div style="padding-bottom:15px;">
<?php

foreach($notifications as $notification) {
    echo PeepSoTemplate::exec_template('notifications','email-preview', $notification);
}

?>
</div>
