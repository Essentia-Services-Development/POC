<?php
        if (count($albums) > 0) {
            foreach ($albums as $album) {
                # code...
                $album->num_photo   = $photos_album_model->get_num_photos_by_album($user->get_id(), $album->pho_album_id, 0);
                $cover              = $photos_album_model->get_album_photo($user->get_id(), $album->pho_album_id, 0, 1, 'desc', 0);
                if(count($cover)>0){
                    $album->cover_photo       = $cover[0];
                }

				// album title
				$title = (0 === intval($album->pho_system_album)) ? $album->pho_album_name : __($album->pho_album_name, 'peepso-core');

				// default thumbnail
				$pho_thumb = PeepSo::get_asset('images/album/default.png');

				// if a custom thumb exists
				if(isset($album->cover_photo->pho_thumbs['l'])) {
					$pho_thumb = $album->cover_photo->pho_thumbs['l'];
				}

                ?>
        <div class="block">
            <a href="../photos/<?php echo $album->pho_album_id; ?>.htm"><img src="<?php echo $pho_thumb;?>" title="<?php echo $title ?>" /></a>
            <div>
               <a href="../photos/<?php echo $album->pho_album_id; ?>.htm"><?php echo $title;?> - <?php echo sprintf(_n( '%s photo', '%s photos', $album->num_photo, 'peepso-core' ), $album->num_photo); ?></a>
               <div class="meta"><?php echo $album->pho_created?></div>
               <!-- <div class="comment">
                  <span class="user">Kangmas Suprayogi</span>prewed euy, jadi ieu mah (y)
                  <div class="meta">Monday, February 4, 2013 at 1:47pm UTC+07</div>
               </div> -->
            </div>
       </div>

                <?php
            }
        }
?>
