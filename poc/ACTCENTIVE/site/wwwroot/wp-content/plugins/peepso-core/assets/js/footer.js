import $ from 'jquery';
import { observer } from 'peepso';
import { show_powered_by, powered_by } from 'peepsodata';

if (+show_powered_by) {
	observer.addAction('show_branding', () => {
		let $footer = $('#peepso-wrap');
		$footer = observer.applyFilters('get_footer_container', $footer);
		if (!$footer.length) {
			return;
		}

		let $branding = $(powered_by);
		if (!$footer.children(`.${$branding.attr('class')}`).length) {
			$branding.appendTo($footer);
		}
	});
}
