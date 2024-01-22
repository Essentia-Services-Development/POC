<?php
if(class_exists('PeepSoMaintenanceFactory')) {
    class PeepSo3_Maintenance_Pin_Until extends PeepSoMaintenanceFactory
    {
        public static function unpinExpired()
        {
            $count = 0;
            global $wpdb;

            $sql="SELECT * FROM {$wpdb->postmeta} WHERE meta_key='peepso_pinned_until' and meta_value<".time();

            $metas = $wpdb->get_results($sql);

            if ($metas) {
                foreach ($metas as $meta) {
                    delete_post_meta($meta->post_id, 'peepso_pinned_until');
                    delete_post_meta($meta->post_id, 'peepso_pinned');
                    delete_post_meta($meta->post_id, 'peepso_pinned_by');
                    $count++;
                }
            }

            return $count;
        }
    }
}