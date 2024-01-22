<div class="ps-postbox__files">
	<div class="ps-postbox__files-inner">
		<div class="ps-postbox__files-fetched"></div>
		<div class="ps-postbox__files-upload ps-postbox-input ps-inputbox">
			<div class="ps-postbox__files-info ps-js-file-upload">
				<div class="ps-postbox__files-message">
					<i class="gcir gci-file"></i>
					<?php echo __('Click here to start uploading files', 'peepsofileuploads'); ?>
				</div>
				<div class="ps-postbox__files-limits">
					<?php
					// check maximum upload size
					$max_upload_size = PeepSo::get_option_new('fileuploads_max_upload_size');

					// use WP max upload size if it is smaller than PeepSo max upload size
					$wp_max_size = max(wp_max_upload_size(), 0);
					$wp_max_size /= pow(1024, 2);
					if ($wp_max_size < $max_upload_size || empty($max_upload_size)) {
						$max_upload_size = $wp_max_size;
					}

					echo sprintf(__('Max file size: %1$sMB', 'peepsofileuploads'), $max_upload_size);
					?>
				</div>
				<div class="ps-postbox__files-limits">
					<?php
						$filetypes = PeepSo::get_option_new('fileuploads_allowed_filetype');
						if ($filetypes) {
							$filetypes = strtoupper(implode(', ', array_map('trim', explode(PHP_EOL, $filetypes))));

							echo sprintf(__('Allowed file types: %s', 'peepsofileuploads'), $filetypes);
						}
					?>
				</div>
			</div>
			<div class="ps-postbox__files-preview ps-js-file-preview">
				<div class="ps-postbox__file-container ps-js-file-container">
					<div class="ps-postbox__file-previews ps-js-previews">
						<div class="ps-postbox__file-items ps-js-items"></div>
						<div class="ps-postbox__file-add ps-js-btn-add">
							<i class="gcir gci-plus-square"></i>
							<span><?php echo __('Drag your files here or browse', 'peepsofileuploads'); ?></span>
						</div>
					</div>
					<!-- Preview template -->
					<script type="text/template" data-name="preview-item">
						<div class="ps-postbox__file-item ps-js-item" data-id="{{= data.id }}" data-name="">
							<div class="ps-postbox__file-inner">
								<div class="ps-postbox__file-content">
									<i class="gcis gci-file"></i>
									<div class="ps-postbox__file-content-details">
										<span title="{{= data.name }}" {{= data.error ? 'class="ps-postbox__file-item-alert"' : '' }}>
											{{= data.name }}
										</span>
										{{ if (data.error) { }}
										<div class="ps-postbox__file-item-alert-text">{{= data.error }}</div>
										{{ } }}
										{{ if (!data.error) { }}
										<div class="ps-postbox__file-item-progress ps-js-progressbar">
											<div class="ps-postbox__file-item-bar ps-js-progress" style="width:0"></div>
										</div>
										<div class="ps-postbox__file-item-completed"><?php echo __('Completed', 'peepsofileuploads'); ?> <i class="gci gcis gci-check"></i></div>
										{{ } }}
									</div>
								</div>
								<div class="ps-postbox__file-remove ps-js-remove ps-tip ps-tip--arrow" aria-label="<?php echo __('Delete', 'peepsofileuploads'); ?>" style="cursor:pointer">
									<i class="gcis gci-times"></i>
								</div>
							</div>
						</div>
					</script>
				</div>
			</div>
		</div>
	</div>
</div>
