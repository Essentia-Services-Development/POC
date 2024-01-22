var videos = document.getElementsByClassName('rhpb-video-element');
for (var video of videos) {
    var type = video.dataset.provider,
        isOverlay= video.dataset.overlay,
        isLightbox = video.dataset.lightbox,
        overlay = video.nextSibling,
        player;
    if( isOverlay === "false" ){
        switch(type){
            case 'video':
                const body = document.body;
                body.addEventListener("mouseover", getHostedVideo( video ), {once:true});
                body.addEventListener("touchmove", getHostedVideo( video ), {once:true});
                body.addEventListener("scroll", getHostedVideo( video ), {once:true});
                body.addEventListener("keydown", getHostedVideo( video ), {once:true});
                //getHostedVideo( video );
                break;
            case 'youtube':
                getYoutubeVideo( video );
                break;
            case 'vimeo':
                getVimeoVideo( video );
                break;
        }
    } else {
        overlay.onclick = function(){
            var video = this.previousSibling,
                type = this.dataset.type,
                isLightbox = this.dataset.lightbox,
                overlay = this;
            if( isLightbox !== "true" ){
                switch(type){
                    case 'video':
                        var el = getHostedVideo( video );
                        el.play();
                        break;
                    case 'youtube':
                        getYoutubeVideo( video );
                        break;
                    case 'vimeo':
                        getVimeoVideo( video );
                        break;
                }
                this.remove();
            } else {
                SimpleLightbox.open({
                    content: video,
                    elementClass: 'rhpb-video-popup',
                    beforeClose: function(e){
                        overlay.parentNode.insertBefore(video, overlay);
                    },
                });
                switch(type){
                    case 'video':
                        var el = getHostedVideo( video );
                        el.play();
                        break;
                    case 'youtube':
                        getYoutubeVideo( video );
                        break;
                    case 'vimeo':
                        getVimeoVideo( video );
                        break;
                }
            }
        }
    }
}

function getHostedVideo( video ) {
    var el = document.createElement("video");
    el.setAttribute('class', 'rhpb-video-element');
    el.setAttribute('src', video.dataset.src);
    el.setAttribute('poster', video.dataset.poster);
    el.autoplay = video.dataset.autoplay === "true" ? true : false;
    el.playsInline = video.dataset.playsinline === "true" ? true : false;
    el.controls = video.dataset.controls === "true" ? true : false;
    el.loop = video.dataset.loop === "true" ? true : false;
    el.muted = video.dataset.mute === "true" ? true : false;
    video.replaceWith(el);
    if(video.dataset.autoplay === "true"){
        el.play();
    }
    return el;
}
function getVideoIDFromURL(url, regex) {
    var videoIDParts = url.match(regex);
    return videoIDParts && videoIDParts[1];
}
function getYoutubeRegex() {
    return /^(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?vi?=|(?:embed|v|vi|user)\/))([^?&"'>]+)/;
}
function getYoutubeVideo( video ) {
    var src = video.dataset.src,
        videoID = getVideoIDFromURL(src, getYoutubeRegex());
    var url = '//www.youtube.com/embed/'
        + videoID
        + "?autoplay=" + (video.dataset.autoplay === "true" ? "1" : "0")
        + '&loop=' + (video.dataset.loop === "true" ? "1" : "0")
        + '&playsinline=' + (video.dataset.playsinline === "true" ? "1" : "0")
        + '&controls=' + (video.dataset.controls === "true" ? "1" : "0")
        + '&modestbranding=' + (video.dataset.modestbranding === "true" ? "1" : "0")
        + '&rel=' + (video.dataset.rel === "true" ? "1" : "0")
        + '&mute=' + (video.dataset.mute === "true" ? "1" : "0")
        + (video.dataset.start && video.dataset.loop === "false" ? "&start=" + video.dataset.start : "")
        + (video.dataset.end && video.dataset.loop === "false" ? "&end=" + video.dataset.end : "");
    video.setAttribute('src', url);
}
function getVimeoRegex() {
    return /^(?:https?:\/\/)?(?:www|player\.)?(?:vimeo\.com\/)?(?:video\/|external\/)?(\d+)([^.?&#"'>]?)/;
}
function getVimeoVideo( video ) {
    var src = video.dataset.src,
        videoID = getVideoIDFromURL(src, getVimeoRegex());
    var options = {
        id: videoID,
        autoplay: video.dataset.autoplay === "true" ? 1 : 0,
        loop: video.dataset.loop === "true" ? 1 : 0,
        playsinline: video.dataset.playsinline === "true" ? 1 : 0,
        muted: video.dataset.mute === "true" ? 1 : 0,
        controls: video.dataset.controls === "true" ? 1 : 0,
        title: video.dataset.title === "true" ? 1 : 0,
        portrait: video.dataset.portrait === "true" ? 1 : 0,
        byline: video.dataset.byline === "true" ? 1 : 0,
    };
    player = new Vimeo.Player(video, options);
    ! isNaN(video.dataset.start) && player.setCurrentTime( parseInt(video.dataset.start) );
}