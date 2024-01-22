<?php

if ($files) {
    foreach ($files as $file) {
        $data = PeepSoFileUploads::prepare_for_display($file);

        PeepSoTemplate::exec_template('file', 'single-file', $data);
    }
}