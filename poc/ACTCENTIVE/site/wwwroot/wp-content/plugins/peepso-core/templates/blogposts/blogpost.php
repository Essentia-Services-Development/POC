<div class="ps-blogposts__post">
	<div class="ps-blogposts__post-inside">
		<div class="ps-blogposts__post-body">

			<!-- Featured image -->
			<?php
			$image_position = "";
			$image_size = "medium";

			if(PeepSo::get_option('blogposts_profile_featured_image_position') == "left") {
				$image_position = "ps-blogposts__post-image--left";
			}

			if(PeepSo::get_option('blogposts_profile_featured_image_position') == "right") {
				$image_position = "ps-blogposts__post-image--right";
			}

			if(PeepSo::get_option('blogposts_profile_featured_image_position') == "top") {
				$image_position = "ps-blogposts__post-image--top";
				$image_size = "large";
			}

			if(PeepSo::get_option('blogposts_profile_featured_image_enable') && (has_post_thumbnail($post) || PeepSo::get_option('blogposts_profile_featured_image_enable_if_empty'))) : ?>
				<div style="background-image: url('<?php echo get_the_post_thumbnail_url($post, $image_size);?>');" class="ps-blogposts__post-image <?php echo $image_position; ?>">
					<a href="<?php echo get_permalink($post);?>"></a>
				</div>
			<?php endif; ?>

			<!-- Post title -->
			<h2 class="ps-blogposts__post-title">
				<a title="<?php echo get_the_title($post);?>" href="<?php echo get_permalink($post);?>">
					<?php echo get_the_title($post);?>
				</a>
			</h2>

			<!-- Post meta -->
			<div class="ps-blogposts__post-meta">
				<?php echo get_the_date('',$post);?>
			</div>

			<!-- Post content -->
			<?php if(FALSE !== $post_content):?>
				<div class="ps-blogposts__post-content">
					<?php echo $post_content; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
