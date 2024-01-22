import $ from 'jquery';
import { observer } from 'peepso';
import { rest_url as REST_URL, file as fileData } from 'peepsodata';
import Dropzone from './dropzone';

class PostboxFile {
	constructor($postbox) {
		this.$postbox = $postbox;
		this.$postboxTab = this.$postbox.$posttabs;
		this.$postboxStatusTextarea = this.$postbox.$textarea;
		this.$postboxStatus = this.$postboxStatusTextarea.closest('.ps-postbox-status');
		this.$postboxFile = this.$postbox.find('.ps-postbox-tabs [data-tab-id=file]');
		this.$postboxFileUpload = this.$postboxFile.find('.ps-js-file-upload');
		this.$postboxFilePreview = this.$postboxFile.find('.ps-js-file-preview');
		this.$postboxFileShortcut = this.$postbox.find('#file-post');

		this.$postboxTab.on('peepso_posttabs_show-file', () => this.show());
		this.$postboxTab.on('peepso_posttabs_cancel-file', () => this.cancel());
		this.$postboxTab.on('peepso_posttabs_submit-file', () => this.post());
		this.$postboxFileUpload.on('click', () => this.upload());
		this.$postboxFileShortcut.on('click', () => {
			this.$postboxTab.find('[data-tab=file]').click();
		});

		this.dropzone = new Dropzone(this.$postboxFilePreview.find('.ps-js-file-container'), {
			uploadUrl: `${REST_URL}${fileData.uploadUrl}`,
			uploadFileTypes: fileData.uploadFileTypes
		});

		this.dropzone.on('added', () => {
			this.showPreview();
		});

		this.dropzone.on('uploaded', () => {
			this.$postbox.on_change();
		});

		this.dropzone.on('empty', () => {
			this.showButton();
			this.$postbox.on_change();
		});

		observer.addAction(
			'postbox_type_set',
			($postbox, type) => {
				if ($postbox === this.$postbox && type === 'file') {
					this.$postboxFileShortcut.trigger('click');
				}
			},
			10,
			2
		);

		observer.addFilter('peepso_postbox_can_submit', flags => this.canSubmit(flags), 20, 1);
	}

	show() {
		// Update placeholder text.
		let $text = this.$postboxStatusTextarea;
		$text.data('orig-placeholder', $text.attr('placeholder'));
		$text.attr('placeholder', fileData.texts.postboxPlaceholder);

		this.showButton();

		this.$postboxFile.show();
		this.$postbox.on_change();
	}

	showButton() {
		this.$postboxFilePreview.hide();
		this.$postboxFileUpload.show();
	}

	showPreview() {
		this.$postboxFileUpload.hide();
		this.$postboxFilePreview.show();
	}

	upload() {
		this.dropzone.upload();
	}

	cancel() {
		// Reset placeholder text.
		let $text = this.$postboxStatusTextarea;
		$text.attr('placeholder', $text.data('orig-placeholder'));

		this.dropzone.reset();
		this.$postboxFile.hide();
		this.$postbox.on_change();
	}

	post() {
		let filterName = 'postbox_req_' + this.$postbox.guid;

		observer.addFilter(filterName, this.postSetRequest, 10, 1, this);
		this.$postbox.save_post();
		observer.removeFilter(filterName, this.postSetRequest, 10);

		observer.doAction('file_upload_added', this.dropzone.getFiles());
		this.dropzone.reset();
	}

	postSetRequest(data) {
		return $.extend(data, {
			files: this.dropzone.getFiles().map(file => file.name),
			type: 'files'
		});
	}

	canSubmit(flags) {
		if ('file' === this.$postboxTab.current_tab_id) {
			flags.hard.push(!!this.dropzone.getFiles().length);
		}

		return flags;
	}
}

// Initialize class on main postbox initialization.
observer.addAction('peepso_postbox_addons', addons => {
	let wrapper = {
		init() {},
		set_postbox($postbox) {
			if ($postbox.find('#file-post').length) {
				new PostboxFile($postbox);
			}
		}
	};

	addons.push(wrapper);
	return addons;
});
