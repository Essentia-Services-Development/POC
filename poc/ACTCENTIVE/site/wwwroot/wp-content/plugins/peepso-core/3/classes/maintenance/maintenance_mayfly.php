<?php
if(class_exists('PeepSoMaintenanceFactory')) {
	class PeepSo3_Maintenance_Mayfly extends PeepSoMaintenanceFactory {
		public static function deleteExpired() {
			return PeepSo3_Mayfly::clr();
		}
	}
}

// EOF