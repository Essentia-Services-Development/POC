<?php
if(class_exists('PeepSoMaintenanceFactory')) {
    class PeepSoMaintenanceHashtags extends PeepSoMaintenanceFactory
    {
        public static function processHashtags()
        {
            return PeepSo::get_instance()->hashtags_build_hashtags(PeepSo::get_option('hashtags_post_count_batch_size', 5));
        }

        public static function processPosts()
        {
            return PeepSo::get_instance()->hashtags_build_posts(PeepSo::get_option('hashtags_post_count_batch_size', 5));
        }
    }
}