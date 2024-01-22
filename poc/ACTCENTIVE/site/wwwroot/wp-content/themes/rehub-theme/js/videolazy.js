/*Lazy load video*/
const lazyvid = document.getElementsByClassName('rh_lazy_load_video');
if(lazyvid.length){
    function updateVideo() {                
        let id = this.dataset.videoid;
        let width = this.dataset.width;
        let height = this.dataset.height;
        let hoster = this.dataset.hoster;
        this.classList.add("video-container");
        this.classList.remove("rh_videothumb_link");
        this.classList.remove("cursorpointer");
        if(hoster=='vimeo'){
            this.innerHTML = 
                    '<iframe src="//player.vimeo.com/video/' 
                    + id 
                    + '?autoplay=1&autopause=0" width="'+width+'" height="'+height+'" frameborder="0" allowfullscreen></iframe>';
        }else if(hoster=='youtube'){
            this.innerHTML = 
                '<iframe src="//www.youtube.com/embed/' 
                + id 
                + '?modestbranding=1&autoplay=1" width="'+width+'" height="'+height+'" frameborder="0" allowfullscreen></iframe>';
        }
    }  
    for (i = 0; i < lazyvid.length; i++) {
        lazyvid[i].addEventListener("click", updateVideo);
    }
} 