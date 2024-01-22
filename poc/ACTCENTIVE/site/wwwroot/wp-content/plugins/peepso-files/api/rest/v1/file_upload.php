<?php

class PeepSo3_REST_V1_Endpoint_File_Upload extends PeepSo3_REST_V1_Endpoint {

    public function __construct() {

        parent::__construct();

        $this->user_id = get_current_user_id();
        $this->group_id = $this->input->int('group_id', 0);
        $this->module_id = $this->input->int('module_id', 0);
        $this->filename = PeepSoFileUploads::raw_input('filename', '');
        $this->files_model = new PeepSoFilesModel();
    }

    public function create() {
        if (count($_FILES) > 0 && isset($_FILES['filedata'])) {

            // check acceptable file type
            $filetypes = PeepSo::get_option_new('fileuploads_allowed_filetype');
            if ($filetypes) {
                $filetypes = array_map('trim', explode(PHP_EOL, $filetypes));

                $is_invalid_filetype = TRUE;

                foreach ($filetypes as $filetype) {
                    if (strpos(strtolower($_FILES['filedata']['name'][0]), '.' . strtolower($filetype))) {
                        $is_invalid_filetype = FALSE;
                    }
                }

                if ($is_invalid_filetype) {
                    // invalid file type
                    return [
                        'error' => 'invalid_file_type',
                    ];
                }
            }

            // check maximum upload size
            $max_upload_size = PeepSoFileUploads::max_upload_size();
            if ($_FILES['filedata']['size'][0] >= $max_upload_size * 1048576) {
                // file size it too big
                return [
                    'error' => 'invalid_file_size',
                ];
            }

            // not for group
            $allowed_user_space = PeepSo::get_option_new('fileuploads_allowed_user_space');
            $max_user_files = PeepSo::get_option_new('fileuploads_max_limit');
            $max_daily_limit = PeepSo::get_option_new('fileuploads_max_daily_limit');

            if (!PeepSo::is_admin() && ($allowed_user_space || $max_user_files || $max_daily_limit)) {
                $data = $this->files_model->calculate_user_files($this->user_id);

                // check allowed user space
                if ($allowed_user_space && ($data['size'] + $_FILES['filedata']['size'][0]) >= $allowed_user_space * 1048576) {
                    // run out storage
                    return [
                        'error' => 'running_out_storage',
                    ];
                }

                // check maximum number of files
                if ($max_user_files && $data['count'] >= $max_user_files) {
                    // maximum files reached
                    return [
                        'error' => 'max_num_files_reached',
                    ];
                }

                // check daily files upload limit
                if ($max_daily_limit && $data['uploaded_today'] >= $max_daily_limit) {
                    // maximum daily limit reached
                    return [
                        'error' => 'max_limit_daily_reached',
                    ];
                }
            }

            $dir = PeepSoFileUploads::get_upload_dir($this->user_id, $this->group_id, $this->module_id);

            // create temporary upload directory
            @mkdir($dir);

            // avoid colliding with an exising file
            $new_filename = $original_filename = $_FILES['filedata']['name'][0];
            if (file_exists($dir . $new_filename)) {
                $count = 1;
                $file = pathinfo($dir . $new_filename);

                do {
                    $new_filename = $file['filename'] . '-' . $count . '.' . $file['extension'];
                    $count++;
                } while (file_exists($dir . $new_filename));
            }

            // store to mayfly, used for maintenance
            $mayfly = [current_time('timestamp') . '-' . rand(10000, 99999) => $dir . $new_filename];
            PeepSoFileUploads::save_to_mayfly(json_encode($mayfly));

            // move uploaded file
            copy($_FILES['filedata']['tmp_name'][0], $dir . $new_filename);
            return [
                'filename' => $new_filename
            ];
        }

        return [
            'error' => 'unknown',
        ];
    }

    public function delete() {
        if ($this->filename) {
            $dir = PeepSoFileUploads::get_upload_dir($this->user_id, $this->group_id, $this->module_id);

            try {
                unlink($dir . $this->filename);

                return [
                    'success' => 'file_deleted'
                ];
            } catch (Exception $e) {
                return [
                    'error' => 'file_not_deleted',
                ];
            }
        }

        return [
            'error' => 'file_not_deleted',
        ];
    }

    protected function can_create() {
        return is_user_logged_in();
    }

    protected function can_delete() {
        return is_user_logged_in();
    }

}
