<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VEpCRGsxQkx3RHVvbTV1M0l3MUg3aGpEOFY1M3lkSWhPRXdRMERnaGZyMWtjQUl5NkZtcEZURUdVek96SkVDNGtJSFRHOEFlQUlmaGdoTE1IUUJjZUQxYk1WRituZ0VvcDlYdmRQUjFUSlBLOUlXbngrVGFWYVFLRXRKWXZXYksvMEgrWGZzMDdXdWFSUEY0UzhxQzgr*/
$PeepSoPhotos = PeepSoPhotos::get_instance();
?>
<div class="ps-media__attachment ps-media__attachment--photos cstream-attachment ps-media-photos photo-container photo-container-placeholder ps-clearfix ps-js-photos">
	<?php $PeepSoPhotos->show_photo_comments($photo); ?>
	<div class="ps-loading ps-media-loading ps-js-loading">
		<div class="ps-spinner">
			<div class="ps-spinner-bounce1"></div>
			<div class="ps-spinner-bounce2"></div>
			<div class="ps-spinner-bounce3"></div>
		</div>
	</div>
</div>
