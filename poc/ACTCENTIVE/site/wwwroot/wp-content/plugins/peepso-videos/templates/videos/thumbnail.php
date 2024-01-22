<div class="cstream-attachment" style="display: none;"><div id="peepso-video-player-<?php echo $id; ?>"><?php echo $content; ?></div></div>

<div class="video-thumbnail ex1">
    <a href="#" onclick="<?php echo isset($onclick) ? $onclick : ''; ?>; return false;" data-post-id="<?php echo $id; ?>">
        <div class="image">
        	<img src="<?php echo $thumbnail; ?>" alt="" />
	        <span class="play">
	            <span></span>
	        </span>
        </div>
    </a>
</div>
