<?php

class PeepSoError
{
	public function __construct($err_msg)
	{
        if (!PeepSo::get_option_new('system_enable_logging')) {
            return (FALSE);
        }

        $err_msg = maybe_serialize($err_msg);

        $message = $err_msg."\n";

        $peepso_dir = PeepSo::get_option('site_peepso_dir', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'peepso', TRUE);

        $message = current_time('Y-m-d H:i:s ', 1) . $message;

        $file = get_option('peepso_debug_file');
        if (!$file) {
            $file = md5(microtime() . $_SERVER['HTTP_HOST']);
            update_option('peepso_debug_file', $file);
        }

        error_log ( "\n".$message, 3, $peepso_dir.'/'.$file.'.log');
	}
}

// EOF