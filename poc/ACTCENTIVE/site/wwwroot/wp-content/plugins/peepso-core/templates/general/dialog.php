<?php

	$admin_screen = defined('DOING_AJAX') && DOING_AJAX ? false : is_admin();
	$btn_class = $admin_screen ? 'pa-btn' : 'ps-btn';

?>

<div class="ps-modal__wrapper">
	<?php if ($admin_screen) { ?>
	<div class="ps-modal__disabler"></div>
	<?php } ?>
	<div class="ps-modal__container">
		<div class="ps-modal {{= data.opts.wide ? 'ps-modal--wide' : '' }}">
			<div class="ps-modal__inner">
				<div class="ps-modal__header ps-js-header">
					<div class="ps-modal__title ps-js-title">{{= data.opts.title }}</div>
					<a href="#" class="ps-modal__close ps-js-close"><i class="gcis gci-times"></i></a>
				</div>
				<div class="ps-modal__body ps-js-body">
					<div class="ps-modal__content">{{= data.html }}</div>
				</div>
				{{ if (data.opts.actions) { }}
				<div class="ps-modal__footer ps-js-footer">
					<div class="ps-modal__actions">
						{{ var actions = data.opts.actions; for (var i = 0; i < actions.length; i++) { }}
						<button class="<?php echo $btn_class; ?> <?php echo $btn_class; ?>--sm {{= actions[i].primary ? '<?php echo $btn_class; ?>--action' : '' }} {{= actions[i].class || '' }}"
								{{ if (actions[i].iconHover) { }}
								data-mouseover-icon="{{= actions[i].iconHover }}"
								{{ } }}
						>
							{{ if (actions[i].icon) { }}
							<i class="{{= actions[i].icon }}"></i>
							{{ } }}
							{{= actions[i].label }}
							{{ if (actions[i].loading) { }}
							<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>"
								class="ps-js-loading" alt="loading" style="padding-left:5px; display:none" />
							{{ } }}
						</button>
						{{ } }}
					</div>
				</div>
				{{ } }}
			</div>
		</div>
	</div>
</div>
