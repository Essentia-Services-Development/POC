import $ from 'jquery';
import _ from 'underscore';
import { browser, hooks, observer, util } from 'peepso';
import { supportsType } from './video';

const SUPPORT_WEBM = supportsType('webm') === 'probably';
const AUTOPLAY = window.peepsovideosdata && +peepsovideosdata.autoplay;

/**
 * Initialize video thumbnail on activity stream.
 *
 * @param {jQuery} $video
 */
const initVideo = $video => {
	let $thumbnail = $video.find('.ps-video-thumbnail'),
		$play = $thumbnail.find('.ps-js-media-play'),
		$img = $thumbnail.find('img'),
		previewStill = $img.attr('src'),
		previewGif = $img.data('animated'),
		previewWebm = $img.data('animated-webm'),
		$preview;

	$thumbnail
		.on('mouseenter', () => {
			if (previewWebm && SUPPORT_WEBM) {
				if ($preview) {
					$img.hide();
					$preview.show();
					$preview.get(0).play();
				} else {
					$preview = $(`<video src="${previewWebm}" />`).css({ maxWidth: '100%' });
					$preview.get(0).addEventListener('loadeddata', () => {
						$img.hide();
						$preview.insertAfter($img);
						$preview.show();
						$preview.get(0).play();
					});
				}
			} else if (previewGif) {
				$img.parent().css('background-image', `url('${previewGif}')`);
			}
		})
		.on('mouseleave', () => {
			if (previewWebm && SUPPORT_WEBM) {
				if ($preview) {
					$preview.hide();
					$preview.get(0).pause();
					$img.show();
				}
			} else if (previewGif) {
				$img.parent().css('background-image', `url('${previewStill}')`);
			}
		});

	// Handle play button.
	$play.on('click', () => {
		// Preview is available.
		let $content = $video.children('script');
		if ($content.length) {
			$video.html($content.text());
			return;
		}

		// Preview is unavailable.
		let $wpVideo = $video.find('.wp-video video');
		if ($wpVideo.length) {
			$thumbnail.remove();
			$wpVideo[0].controls = true;
			$wpVideo[0].play();
		}
	});
};

// Initialize video on every activity added to the stream.
$(document).on(
	'ps_activitystream_loaded ps_activitystream_append',
	_.throttle(() => {
		let $activities = $('.ps-js-activity').not('[data-video-init]');
		$activities.each(function () {
			let $activity = $(this).attr('data-video-init', ''),
				$video = $activity.find('.ps-js-video');

			if ($video.length) {
				initVideo($video);
			}
		});
	}, 3000)
);

// Fix audio unplayable issue on iOS and Safari.
let ua = navigator.userAgent;
let isSafari = ua.indexOf('Safari') > -1 && ua.indexOf('Chrome') === -1;
if (browser.isIOS() || isSafari) {
	observer.addFilter(
		'peepso_activity',
		function ($posts) {
			return $posts.each(function () {
				let $post = $(this),
					$audio = $post.find('audio.wp-audio-shortcode');

				if ($audio.length) {
					let $source = $audio.find('source');
					if ($source.length) {
						$audio.attr('src', $source.attr('src'));
						$source.remove();
					}
				}
			});
		},
		10,
		1
	);
}

// Injects required YouTube parameters.
// enablejsapi=1&origin=http://example.com
observer.addFilter(
	'peepso_activity',
	$posts =>
		$posts.map(function () {
			if (AUTOPLAY && this.nodeType === 1) {
				let attachment = this.querySelector('.ps-js-activity-attachments');
				if (attachment) {
					let video = attachment.querySelector('iframe[src*="youtube.com/embed"]');
					if (video) {
						let src = video.getAttribute('src');
						let id = src.match(/\/embed\/([^?#\/]+)/)[1];
						if (src.indexOf('enablejsapi') === -1) {
							src += `${src.indexOf('?') === -1 ? '?' : '&'}enablejsapi=1&origin=${
								location.protocol
							}//${location.hostname}`;
							video.setAttribute('src', src);
							video.setAttribute('id', `ps-js-yt-${id}`);
						}
					}

					video = attachment.querySelector('iframe[src*="dailymotion.com/embed"]');
					if (video) {
						let src = video.getAttribute('src');
						let id = src.match(/\/embed\/video\/([^?#\/]+)/)[1];
						if (id) {
							src += `${src.indexOf('?') === -1 ? '?' : '&'}api=postMessage`;
							video.setAttribute('src', src);
							video.setAttribute('id', `ps-js-dailymotion-${id}`);
						}
					}
				}
			}

			return this;
		}),
	10,
	1
);

// YouTube Iframe API loader.
// https://developers.google.com/youtube/iframe_api_reference#Getting_Started
let isYouTubeAPILoaded;
function loadYouTubeAPI() {
	return new Promise((resolve, reject) => {
		if (!isYouTubeAPILoaded) {
			isYouTubeAPILoaded = 'progress';

			window.onYouTubeIframeAPIReady_old = window.onYouTubeIframeAPIReady;
			window.onYouTubeIframeAPIReady = function () {
				window.onYouTubeIframeAPIReady = window.onYouTubeIframeAPIReady_old;
				delete window.onYouTubeIframeAPIReady_old;
				isYouTubeAPILoaded = true;
				resolve(YT);
			};

			let tag = document.createElement('script');
			tag.src = 'https://www.youtube.com/iframe_api';
			let firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
		} else if ('progress' === isYouTubeAPILoaded) {
			let counter = 0;
			let timer = setInterval(function () {
				if (isYouTubeAPILoaded !== 'progress' || ++counter >= 30) {
					clearInterval(timer);
					'undefined' !== typeof YT ? resolve(YT) : reject();
				}
			}, 1000);
		} else {
			'undefined' !== typeof YT ? resolve(YT) : reject();
		}
	});
}

// Vimeo API loader.
let isVimeoAPILoaded;
function loadVimeoAPI() {
	return new Promise((resolve, reject) => {
		if (!isVimeoAPILoaded) {
			isVimeoAPILoaded = 'progress';

			let tag = document.createElement('script');
			tag.src = 'https://player.vimeo.com/api/player.js';
			let firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

			let counter = 0;
			let timer = setInterval(function () {
				if ('undefined' !== typeof Vimeo || ++counter >= 30) {
					clearInterval(timer);
					isVimeoAPILoaded = true;
					'undefined' !== typeof Vimeo ? resolve(Vimeo) : reject();
				}
			}, 1000);
		} else if ('progress' === isVimeoAPILoaded) {
			let counter = 0;
			let timer = setInterval(function () {
				if (isVimeoAPILoaded !== 'progress' || ++counter >= 30) {
					clearInterval(timer);
					'undefined' !== typeof Vimeo ? resolve(Vimeo) : reject();
				}
			}, 1000);
		} else {
			'undefined' !== typeof Vimeo ? resolve(Vimeo) : reject();
		}
	});
}

// Handle video autoplay on document scroll event.
$(function () {
	// Skip if feature is disabled.
	if (!AUTOPLAY) {
		return;
	}

	// Skip if activitystream is not available on the current page.
	let activitystream = document.getElementById('ps-activitystream');
	if (!activitystream) {
		return;
	}

	let ytPlayers = {};
	let vimeoPlayers = {};

	function pauseVideos(videos) {
		videos.forEach(video => {
			let type = video.tagName.toLowerCase();
			if ('iframe' === type) {
				if (video.src.indexOf('youtube.com/embed') > -1) {
					loadYouTubeAPI().then(YT => {
						if (!ytPlayers[video.id]) {
							ytPlayers[video.id] = new YT.Player(video.id, {
								events: { onReady: e => e.target.pauseVideo() }
							});
						} else {
							ytPlayers[video.id].pauseVideo();
						}
					});
				} else if (video.src.indexOf('player.vimeo.com') > -1) {
					loadVimeoAPI().then(Vimeo => {
						if (!video.id) {
							video.id = `ps-js-vimeo-${video.src.match(/video\/(\d+)/)[1]}`;
						}
						if (!vimeoPlayers[video.id]) {
							vimeoPlayers[video.id] = new Vimeo.Player(video);
							vimeoPlayers[video.id].pause();
						} else {
							vimeoPlayers[video.id].pause();
						}
					});
				} else if (video.src.indexOf('dailymotion.com/embed') > -1) {
					// https://stackoverflow.com/questions/26174793/dailymotion-stop-video-from-iframe
					video.contentWindow.postMessage('pause', '*');
				}
			} else if ('video' === type) {
				video.pause();
			}
		});
	}

	function playVideos(videos) {
		videos.forEach(video => {
			let type = video.tagName.toLowerCase();
			let wrapper = video.closest('.ps-media--iframe');
			if ('iframe' === type) {
				if (video.src.indexOf('youtube.com/embed') > -1) {
					loadYouTubeAPI().then(YT => {
						if (!ytPlayers[video.id]) {
							ytPlayers[video.id] = new YT.Player(video.id, {
								events: { onReady: e => e.target.playVideo() }
							});
						} else {
							ytPlayers[video.id].playVideo();
						}
					});
				} else if (video.src.indexOf('player.vimeo.com') > -1) {
					loadVimeoAPI().then(Vimeo => {
						if (!video.id) {
							video.id = `ps-js-vimeo-${video.src.match(/video\/(\d+)/)[1]}`;
						}
						if (!vimeoPlayers[video.id]) {
							vimeoPlayers[video.id] = new Vimeo.Player(video);
							vimeoPlayers[video.id].play();
						} else {
							vimeoPlayers[video.id].play();
						}
					});
				} else if (video.src.indexOf('dailymotion.com/embed') > -1) {
					// https://stackoverflow.com/questions/26174793/dailymotion-stop-video-from-iframe
					video.contentWindow.postMessage('play', '*');
				}
			} else if ('video' === type) {
				let btnPlay = wrapper.querySelector('.ps-js-media-play');
				if (btnPlay) {
					$(btnPlay).trigger('click');
				} else {
					video.play();
				}
			} else if ('div' === type) {
				let btnPlay = wrapper.querySelector('.ps-js-media-play');
				if (btnPlay) {
					$(btnPlay).trigger('click');
				}
			}
		});
	}

	let autoplayVideo = _.debounce(function () {
		let activities = activitystream.querySelectorAll('.ps-js-activity');
		let videos = [...activities]
			.map(el => {
				let attachment = el.querySelector('.ps-js-activity-attachments');
				if (attachment) {
					let video =
						attachment.querySelector('.ps-js-video .ps-media__video-thumb') ||
						attachment.querySelector('.wp-video video') ||
						attachment.querySelector('iframe[src*="youtube.com/embed"]') ||
						attachment.querySelector('iframe[src*="player.vimeo.com"]') ||
						attachment.querySelector('iframe[src*="dailymotion.com/embed"]');

					return video;
				}

				return null;
			})
			.filter(el => el);

		let visibleVideo;
		for (let i = 0; i < videos.length; i++) {
			if (util.isElementInViewport(videos[i])) {
				// Do not autoplay sensitive videos.
				let atth = videos[i].closest('.ps-js-activity-attachments');
				let nsfw = atth && atth.classList.contains('ps-post__attachments--nsfw');
				if (!nsfw) {
					visibleVideo = videos[i];
				}
			}
		}

		if (visibleVideo) {
			pauseVideos(videos.filter(el => el !== visibleVideo));
			playVideos([visibleVideo]);
		} else {
			pauseVideos(videos.filter(el => !util.isElementPartlyInViewport(el)));
		}
	}, 300);

	document.addEventListener('scroll', autoplayVideo);
	hooks.addAction('nsfw_reveal', 'video_autoplay', autoplayVideo);
});
