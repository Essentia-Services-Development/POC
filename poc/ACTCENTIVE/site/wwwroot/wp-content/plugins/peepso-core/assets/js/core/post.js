import { ajax, dialog, hooks } from 'peepso';
import { currentuserid as LOGIN_USER_ID, activity as activityData } from 'peepsodata';

const TEMPLATE_REPOST = activityData && activityData.template_repost;
const TEMPLATE_REPORT = activityData && activityData.template_report;

/** Post class. */
class Post {
	/**
	 * Create a Post instance.
	 *
	 * @param {number} postId
	 * @param {number} actId
	 */
	constructor(postId, actId) {
		this.postId = postId;
		this.actId = actId;
	}

	/**
	 * Show dialog to repost a post.
	 * @todo Implement this function.
	 */
	doRepost() {
		let popup = dialog(TEMPLATE_REPOST).show();

		popup.$el.on('click', '.ps-js-cancel', () => popup.hide());
		popup.$el.on('click', '.ps-js-submit', () => {});
	}

	/**
	 * Repost a post.
	 * @todo Implement this function.
	 *
	 * @returns {JQueryDeferred}
	 */
	repost() {}

	/**
	 * Show dialog to report a post.
	 */
	doReport() {
		let popup = dialog(TEMPLATE_REPORT).show();

		popup.$el.on('click', '.ps-js-cancel', () => popup.hide());
		popup.$el.on('click', '.ps-js-submit', () => {
			let $reason = popup.$el.find('.ps-js-report-type option:selected'),
				$description = popup.$el.find('.ps-js-report-desc textarea'),
				$error = popup.$el.find('.ps-js-report-error'),
				reason = $reason.val(),
				description = $description.val().trim();

			if (!reason) {
				$error.html(popup.opts.text_select_reason).show();
			} else if ($reason.data('need-reason') && !description) {
				$error.html(popup.opts.text_fill_description).show();
			} else {
				$error.hide();
				this.report(reason, description).then(function(json) {
					let title = popup.title();
					popup.hide();

					if (json.notices) {
						dialog(json.notices, { title })
							.show()
							.autohide();
					}
				});
			}
		});
	}

	/**
	 * Report a post.
	 *
	 * @param {string} reason
	 * @param {string} [description]
	 * @returns {JQueryDeferred}
	 */
	report(reason, description) {
		let params = {
			uid: LOGIN_USER_ID,
			act_id: this.actId,
			reason: reason,
			reason_desc: description
		};

		return ajax.post('activity.report', params).done(json => {
			if (json.success) {
				hooks.doAction('post_reported', { actId: this.actId, postId: this.postId });
			}
		});
	}
}

export default function user(...args) {
	return new Post(...args);
}
