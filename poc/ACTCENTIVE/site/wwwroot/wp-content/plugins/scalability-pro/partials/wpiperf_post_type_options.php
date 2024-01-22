<div class="wpiperf_image_sizes">
    <h2>
        <?php echo $post_type_label; ?>
        <button type="button" class="wpiperf_remove_post_type secondary">Remove</button>
    </h2>
    <input type="hidden" class="post_type_name" name="wpiperf_settings[selected_post_types][]" value="<?php echo $post_type_name; ?>" />
    <p>Check the image sizes you wish to keep.</p>
    <?php
    $image_sizes = get_intermediate_image_sizes();
    foreach ($image_sizes as $image_size) {
        $image_size_name = $image_size;
        $size_info = array(
            'width' => intval(get_option("{$image_size}_size_w")),
            'height' => intval(get_option("{$image_size}_size_h")),
            'crop' => get_option("{$image_size}_crop") ? true : false,
        );
        ?>
        <div class="enabled_sizes">
            <input type="checkbox" name="wpiperf_settings[image_sizes][<?php echo $post_type_name; ?>][<?php echo $image_size_name; ?>]" value="1" <?php if (array_key_exists('image_sizes', $options) && array_key_exists($post_type_name, $options['image_sizes']) && array_key_exists($image_size_name, $options['image_sizes'][$post_type_name])) checked($options['image_sizes'][$post_type_name][$image_size_name], 1); ?> />
            <label for="wpiperf_settings[image_sizes][<?php echo $post_type_name; ?>][<?php echo $image_size_name; ?>]"><?php echo $image_size; ?> (<?php echo $size_info['width'] . 'px X ' . $size_info['height'] . 'px '; if ($size_info['crop']) _e('Cropped', 'scalability-pro'); ?>)</label>
        </div>
    <?php } ?>
</div>