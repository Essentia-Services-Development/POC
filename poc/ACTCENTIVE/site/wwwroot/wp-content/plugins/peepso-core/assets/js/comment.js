(function ($, peepso, factory) {
	/**
	 * PsComment global instance.
	 * @name peepso.comment
	 * @type {PsComment}
	 */
	peepso.comment = new (factory($, peepso, peepso.modules))();
})(jQuery, peepso, function ($, peepso, modules) {
	/**
	 * Handle commenting.
	 * @class PsComment
	 */
	function PsComment() {
		this.init();
	}

	/**
	 * Triggers "human-friendly" ajax calls for a specific comment.
	 *
	 * @param {HTMLInputElement} hidden
	 */

	peepso.npm.objectAssign(
		PsComment.prototype,
		/** @lends PsComment.prototype */ {
			/**
			 * Initialize commenting.
			 */
			init: function () {
				this.ajax = {};

				// reveal comment on single activity view which ids are defined in the url hash
				// for example `#comment.00.11.22.33` will be translated as follow:
				//   00 = post's act_id
				//   22 = comment's post_id
				//   11 = comment's act_id
				//   33 = reply's act_id (optional, if you want to show reply)
				$(
					$.proxy(function () {
						var hash = window.location.hash || '',
							// Check for `#comment.00.11.22.33` pattern in URL.
							// Also accept `#comment=00.11.22.33` pattern as fallback for older URLs.
							reComment = /[#&]comment(?:\.|=)(\d+)\.(\d+)(?:\.(\d+)(?:\.(\d+))?)?/,
							data = hash.match(reComment);

						if (data && data[2]) {
							this.whenLoaded(data[1]).done(
								$.proxy(function () {
									this.reveal(data[1], data[2], data[3], data[4]);
								}, this)
							);
						}
					}, this)
				);

				peepso.observer.addAction(
					'post_loaded',
					function (element) {
						var $post = $(element);
						var $textarea = $post.find('textarea[name=comment]');

						// Initialize autosize.
						$textarea.ps_autosize();

						// Initialize droppable elements.
						$textarea.each(function () {
							var textarea = this;
							peepso.elements.droppable(textarea, {
								dropped: function (files) {
									peepso.observer.doAction(
										'commentbox_drop_files',
										textarea,
										files
									);
								}
							});
						});

						// Handle comment link.
						$post.on('click', '.ps-comment-time .activity-post-age', function (e) {
							$(e.currentTarget)
								.children('a')
								.each(function () {
									var $link = $(this);
									if ($link.children('.ps-js-autotime').length) {
										var destHref = $link.attr('href');
										var currHref = location.href.replace(location.hash, '');
										if (0 === destHref.indexOf(currHref)) {
											window.location = destHref;
											window.location.reload();
										}
									}
								});
						});
					},
					10,
					1
				);

				peepso.observer.addFilter('peepso_activity', function ($fragment) {
					var $comments = $fragment.find('.ps-js-comments'),
						commentsOpen = +$comments.data('comments-open');

					if (!commentsOpen) {
						$comments.find('.ps-js-btn-reply').hide();
					}

					return $fragment;
				});

				peepso.hooks.addAction('comment_added', 'comments', function (elem) {
					var $comment = $(elem),
						$comments = $comment.closest('.ps-js-comments'),
						commentsOpen = +$comments.data('comments-open');

					if (!commentsOpen) {
						$comments.find('.ps-js-btn-reply').hide();
					}
				});
			},

			/**
			 * Watch if parent activity is already loaded.
			 * @param {number} id
			 * @return {jQuery.Deferred}
			 */
			whenLoaded: function (id) {
				return $.Deferred(function (defer) {
					var maxLoops = 60,
						countLoops = 0,
						timer;

					// Watch post availability.
					timer = setInterval(function () {
						if ($('.ps-js-comment-container--' + id).length) {
							clearInterval(timer);
							defer.resolve();
						} else if (countLoops++ > maxLoops) {
							clearInterval(timer);
							defer.reject();
						}
					}, 1000);
				});
			},

			/**
			 * TODO: docblock
			 */
			add: function () {},

			/**
			 * TODO: docblock
			 */
			edit: function () {},

			/**
			 * Reply to a comment.
			 */
			reply: function (act_id, post_id, elem, data) {
				var $comment,
					$btn,
					$container,
					$textarea,
					nested,
					parentID = '#comment-item-' + post_id;

				if (elem) {
					$comment = $(elem).closest(parentID);
				} else {
					$comment = $(parentID);
				}

				$btn = $comment.find('.actaction-reply');
				nested = $btn.closest('.ps-comments').hasClass('ps-comments--nested');

				if (nested) {
					$container = $btn.closest('.ps-comments').children('.ps-comment-reply');
					$textarea = $container.find('textarea');
				} else {
					$container = $btn
						.closest('.ps-comment')
						.next('.ps-comments--nested')
						.children('.ps-comment-reply');
					$textarea = $container.find('textarea');
				}

				if ($container.not(':visible')) {
					$container.show();
				}

				$textarea.focus();

				data = data || {};
				peepso.observer.applyFilters(
					'comment.reply',
					$textarea,
					$.extend({}, data, { act_id: act_id, post_id: post_id })
				);

				$textarea
					.off('keyup.peepso')
					.on('keyup.peepso', function (e) {
						e.stopPropagation();
						activity.update_beautifier(e.target);
					})
					.trigger('keyup.peepso');
			},

			/**
			 * TODO: docblock
			 */
			show_previous: function (act_id, elem) {
				var $ct,
					$more,
					$loading,
					parentID = '.ps-js-comment-container--' + act_id;

				if (elem) {
					$ct = $(elem).closest(parentID);
				} else {
					$ct = $(parentID);
				}

				$more = $ct.find('.ps-js-comment-more').eq(0);
				$loading = $more.find('.ps-js-loading');

				function getPrevious(callback) {
					var $first = $ct.children('.cstream-comment:first');

					$loading.show();
					peepso.postJson(
						'activity.show_previous_comments',
						{
							act_id: act_id,
							uid: peepsodata.currentuserid,
							first: $first.data('comment-id')
						},
						function (json) {
							// Filter posts.
							var $wrapper = jQuery('<div />').append(json.data.html);
							$wrapper = peepso.observer.applyFilters('peepso_activity', $wrapper);
							var html = peepso.observer.applyFilters(
								'peepso_activity_content',
								$wrapper.html()
							);

							// Manually fix problem with WP Embed as described here:
							// https://core.trac.wordpress.org/ticket/34971
							html = html.replace(
								/\/embed\/(#\?secret=[a-zA-Z0-9]+)?"/g,
								'/?embed=true$1"'
							);

							// #5206 Add an extra identifier for WP Embed loaded in the activity stream.
							html = html.replace(/\/\?embed=true([#"])/g, '/?embed=true&peepso=1$1');

							var $html = $(html);

							$loading.hide();
							if ($first.length == 0) {
								$first = $ct.children('.ps-comment-more');
								$first.after($html);
							} else {
								$first.before($html);
							}

							$html.each(function () {
								if (1 === this.nodeType && $(this).hasClass('ps-js-comment-item')) {
									peepso.hooks.doAction('comment_added', this);
								}
							});

							if (json.data.comments_remain > 0) {
								$more.find('a').html(json.data.comments_remain_caption);
							} else {
								$more.remove();
							}
							$(document).trigger('ps_comment_added');
							callback();
						}
					);
				}

				return $.Deferred(function (defer) {
					getPrevious(function () {
						defer.resolve();
					});
				});
			},

			/**
			 * TODO: docblock
			 */
			show_all: function (act_id) {
				var $ct = $('.ps-js-comment-container--' + act_id),
					$more = $ct.children('.ps-js-comment-more'),
					$loading = $more.find('.ps-js-loading'),
					$first = $ct.children('.cstream-comment:first'),
					first = $first.data('comment-id');

				function getPrevious(callback) {
					$loading.show();
					peepso.postJson(
						'activity.show_previous_comments',
						{
							act_id: act_id,
							uid: peepsodata.currentuserid,
							all: 1,
							first: first
						},
						function (json) {
							// Filter posts.
							var $wrapper = jQuery('<div />').append(json.data.html);
							$wrapper = peepso.observer.applyFilters('peepso_activity', $wrapper);
							var html = peepso.observer.applyFilters(
								'peepso_activity_content',
								$wrapper.html()
							);

							// Manually fix problem with WP Embed as described here:
							// https://core.trac.wordpress.org/ticket/34971
							html = html.replace(
								/\/embed\/(#\?secret=[a-zA-Z0-9]+)?"/g,
								'/?embed=true$1"'
							);

							// #5206 Add an extra identifier for WP Embed loaded in the activity stream.
							html = html.replace(/\/\?embed=true([#"])/g, '/?embed=true&peepso=1$1');

							var $html = $(html);

							$loading.hide();

							// There can be more than one of the same comment groups on a page.
							$ct.each(function (index) {
								var $ct = $(this),
									$first = $ct.children('.cstream-comment:first'),
									$content = $html;

								// Clone elements for subsequent comment groups so that they don't
								// get accidentally detached from the first comment group.
								if (index > 0) {
									$content = $html.clone();
								}

								if ($first.length == 0) {
									$first = $ct.children('.ps-comment-more');
									$first.after($content);
								} else {
									$first.before($content);
								}
							});

							$html.each(function () {
								if (1 === this.nodeType && $(this).hasClass('ps-js-comment-item')) {
									peepso.hooks.doAction('comment_added', this);
								}
							});

							if (json.data.comments_remain > 0) {
								$more.find('a').html(json.data.comments_remain_caption);
								getPrevious(callback);
							} else {
								$more.remove();
								$(document).trigger('ps_comment_added');
								callback();
							}
						}
					);
				}

				return $.Deferred(function (defer) {
					getPrevious(function () {
						defer.resolve();
					});
				});
			},

			/**
			 * TODO: docblock
			 */
			reveal_comment: function (container_id, comment_id) {
				return $.Deferred(
					$.proxy(function (defer) {
						var $comment = $('#comment-item-' + comment_id);
						if ($comment.length) {
							defer.resolve();
						} else {
							this.show_all(container_id).done(
								$.proxy(function () {
									defer.resolve();
								}, this)
							);
						}
					}, this)
				);
			},

			/**
			 * TODO: docblock
			 */
			reveal: function (post_act_id, comment_post_id, comment_act_id, reply_act_id) {
				// hightligh and scroll to particular comment
				function highlight($comment) {
					var color = peepso.getLinkColor(),
						scrollTop =
							$comment.offset().top -
							($(window).height() - $comment.outerHeight()) / 2;

					$comment.css({ backgroundColor: color });
					$comment.css({ transition: 'background-color 3s ease' });

					$('html, body')
						.delay(50)
						.animate({ scrollTop: scrollTop }, 500, function () {
							$comment.css({ backgroundColor: '' });
						});
				}

				this.reveal_comment(post_act_id, comment_post_id).done(
					$.proxy(function () {
						var $comment;

						if (!reply_act_id) {
							$comment = $('#comment-item-' + comment_post_id);
							highlight($comment);
						} else {
							this.reveal_comment(comment_act_id, reply_act_id).done(function () {
								$comment = $('#comment-item-' + reply_act_id);
								highlight($comment);
							});
						}
					}, this)
				);
			}
		}
	);

	// Branding hook.
	if (+peepsodata.show_powered_by) {
		peepso.observer.addAction('show_branding', function () {
			$(document)
				.off('focus.ps-branding blur.ps-branding')
				.on('focus.ps-branding', '.ps-comments__input[name=comment]', function () {
					let $container = $(this).closest('.ps-comments__input-wrapper');
					let $branding = $(peepsodata.powered_by);
					if (!$container.children(`.${$branding.attr('class')}`).length) {
						$branding.appendTo($container);
					}

					$branding.not('style').show();
				})
				.on('blur.ps-branding', '.ps-comments__input[name=comment]', function () {
					let $container = $(this).closest('.ps-comments__input-wrapper');
					let $branding = $(peepsodata.powered_by);
					$branding = $container.children(`.${$branding.attr('class')}`);
					$branding.not('style').hide();
				});
		});
	}

	return PsComment;
});
