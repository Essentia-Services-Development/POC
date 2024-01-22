<?php

/**
* @package ZephyrProjectManager
*/

namespace ZephyrProjectManager\Core;

if ( !defined( 'ABSPATH' ) ) {
	die;
}

use \DateTime;
use ZephyrProjectManager\Zephyr;
use ZephyrProjectManager\Api\Emails;
use ZephyrProjectManager\Core\Tasks;
use ZephyrProjectManager\Core\Members;
use ZephyrProjectManager\Core\Utillities;
use ZephyrProjectManager\ZephyrProjectManager;

class Message {
	public $id;
	public $content;
	private $timeSent;
	private $type;
	private $userId;
	private $created;
	private $subject;
	private $parent_id;
	private $subject_id;

	public function __construct($args) {
		$this->id = property_exists($args, 'id') ? $args->id : '-1';
		$this->content = property_exists($args, 'message') ? maybe_unserialize( $args->message ) : '';
		$this->userId = property_exists($args, 'user_id') ? maybe_unserialize( $args->user_id ) : '';
		$this->subject = property_exists($args, 'subject') ? $args->subject : 'task';

		$datetime1 = zpm_get_datetime(date('Y-m-d H:i:s'));
		$datetime2 = zpm_get_datetime($args->date_created);
		$this->timeSent = '';

		if ($datetime1->format('m-d') == $datetime2->format('m-d')) {
			// Was sent today
			$this->timeSent = $datetime2->format('H:i');
		} else {
			// Was sent earlier than today
			$this->timeSent = $datetime2->format('H:i m/d');
		}

		$this->type = maybe_unserialize($args->type);
	}

	public function getUrl($id){
		return wp_get_attachment_url();
	}

	public function getType() {
		return maybe_unserialize( $this->type );
	}

	public function isType( $type ) {
		if ($this->getType() == $type) {
			return true;
		} else {
			return false;
		}
	}

	public function getMentions() {
		$mentionRegex = '/@\[[^\]]*\]\((.*?)\)/i'; // mention regrex to get all @texts
		if (preg_match_all($mentionRegex, $this->content, $matches)) {
			foreach ($matches[1] as $key => $match) {
				$userId = str_replace('user:', '', $match);
				$userData = Members::get_member($userId);

				if (!empty($userData)) {
					$matchSearch = $matches[0][$key];
					$userInfoHtml = '<span class="zpm-message__mention-info"><img class="zpm-mention-info__avatar" src="' . $userData['avatar'] . '">' . $userData['name'] . ' (' . $userData['email'] . ')' . '</span>';
					$matchReplace = '<span class="zpm-message__mention">@' . $userData['name'] . ' ' . $userInfoHtml . '</span>';
					$this->content = str_replace($matchSearch, $matchReplace, $this->content);
				}
			}
		}
		return $this->content;
	}

	public function sendMentionEmails() {
		$mentionRegex = '/@\[[^\]]*\]\((.*?)\)/i'; // mention regrex to get all @texts
		if (preg_match_all($mentionRegex, $this->content, $matches)) {
			foreach ($matches[1] as $key => $match) {
				$userId = str_replace('user:', '', $match);
				$userData = Members::get_member($userId);
				$subject = __( 'You have been mentioned in a comment', 'zephyr-project-manager' );
				$matchSearch = $matches[0][$key];
				$matchReplace = '<span class="zpm-message__mention">@' . $userData['name'] . '</span>';
				$content = str_replace($matchSearch, $matchReplace, $this->content);
				$message = sprintf( __( 'You have been mentioned in a comment: %s', 'zephyr-project-manager' ), $content );

				Emails::send_email($userData['email'], $subject, $message);
			}
		}
		return $this->content;
	}

	public function html() {
		$this->getMentions();
		$currentUser = wp_get_current_user();
		$user = Members::get_member($this->userId);
		$commentAttachments = $this->subject == 'project' ? Projects::get_comment_attachments($this->id) : Tasks::get_comment_attachments($this->id);
		$isMine = $this->userId == get_current_user_id() ? true : false;
		$custom_classes = $isMine ? 'zpm-my-message' : '';
		$attachmentTypes = zpm_get_attachment_types();

		ob_start();

		// If not file
		if (!in_array($this->type, $attachmentTypes)) : ?>

			<div data-zpm-comment-id="<?php echo esc_attr($this->id); ?>" class="zpm_comment <?php echo esc_attr($custom_classes); ?>">
				<div class="zpm-comment-bubble">
					<span class="zpm_comment_user_image">
						<span class="zpm_comment_user_avatar" style="background-image: url(<?php echo esc_url($user['avatar']); ?>)"></span>
					</span>

					<?php if ($this->userId == $currentUser->ID || current_user_can('zpm_delete_other_comments')) : ?>
						<span class="zpm_delete_comment fa fa-trash"></span>
						<span class="zpm-edit-message fa fa-edit"></span>
					<?php endif; ?>


					<span class="zpm_comment_user_text">
						<span class="zpm_comment_from"><?php echo esc_html($user['name']); ?></span>
						<span class="zpm_comment_time_diff"><?php echo esc_html($this->timeSent); ?></span>
						<p class="zpm_comment_content"><?php echo wp_kses_post($this->content); ?></p>

						<?php if (!empty($commentAttachments)) : ?>
							<ul class="zpm_comment_attachments"><p><?php _e( 'Attachments', 'zephyr-project-manager' ); ?>:</p>
								<?php foreach ($commentAttachments as $attachment) : ?>
									<?php
										$attachmentId = unserialize( $attachment->message );
										$attachmentUrl = !wp_http_validate_url( $attachmentId ) ? wp_get_attachment_url( $attachmentId ) : $attachmentId;
										$isImage = wp_attachment_is_image( $attachmentId );
										if (!$isImage) {
											if (strpos($attachmentId, '.png') > 0 || strpos($attachmentId, '.jpg') > 0) {
												$isImage = true;
											}
										}
										$attachmentName = Utillities::getFileName($attachment);
										$attachmentPreviewIcon = Utillities::getAttachmentIcon($attachment);
									?>
									<?php if ($isImage) : ?>
										<!-- Image -->
										<li class="zpm_comment_attachment" data-attachment="<?php esc_attr_e($attachment->id); ?>"><a class="zpm_link" href="<?php echo esc_url($attachmentUrl); ?>" download><img class="zpm-image-attachment-preview" src="<?php echo esc_url($attachmentUrl); ?>"></a></li>
									<?php else: ?>
										<?php if (!empty($attachmentPreviewIcon)): ?>
											<!-- Attachment -->
											<div class="zpm-message-file-preview">
												<img src="<?php echo esc_url($attachmentPreviewIcon) ?>" class="zpm-file-preview-icon" />
												<li class="zpm_comment_attachment" data-attachment="<?php esc_attr_e($attachment->id); ?>"><a class="zpm_link" href="<?php echo esc_url($attachmentUrl); ?>" download><?php echo esc_html($attachmentName); ?></a></li>
											</div>
										<?php else: ?>
											<li class="zpm_comment_attachment" data-attachment="<?php esc_attr_e($attachment->id); ?>"><a class="zpm_link" href="<?php echo esc_url($attachmentUrl); ?>" download><?php echo esc_html($attachmentName); ?></a></li>
										<?php endif; ?>
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</span>
				</div>
			</div>
		<?php endif; ?>
		<?php
		$html = ob_get_clean();
		return $html;
	}

	public function getContent() {
		return $this->content;
	}
}