<?php if (isset($title)) : ?>
<table style="border-collapse: collapse;width:100%;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #ffffff;" align="center">
  <tbody>
    <tr>
      <td style='padding: 0;text-align: left;vertical-align: top;color: #60666d;font-size: 14px;line-height: 21px;font-family: "Open Sans",sans-serif;width:100%;'><div><p style="margin-top: 0;margin-bottom: 0;font-size: 18px;line-height: 26px;"><span style="color:#333"><strong><?php echo $title; ?></strong></span></p></div></td>
      <td></td>
    </tr>
  </tbody>
</table>
<?php endif; ?>

<table style="border-collapse: collapse;width:100%;margin-bottom:10px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #ffffff;" align="center">
  <tbody>
    <tr>
	<?php if (isset($user_avatar)) : ?>
      <td style='padding: 0;text-align: left;vertical-align: top;width:70px;color: #60666d;font-size: 14px;line-height: 21px;font-family: "Open Sans",sans-serif;'>
        <div style="padding-top: 20px;">
          <a href="<?php echo $activity_url; ?>" target="_blank"><img src="<?php echo $user_avatar; ?>" style="border:none;max-width:none;" width="70" alt=""></a>
        </div>
      </td>
	  <?php endif; ?>
      <td style='padding: 0;text-align: left;vertical-align: top;color: #60666d;font-size: 14px;line-height: 21px;font-family: "Open Sans",sans-serif;'>
  
        <div style="margin-left: 20px;margin-right: 20px;margin-top: 15px;margin-bottom: 24px;">
          <p style="margin-top: 0;margin-bottom: 0;"><a style="color:#00B0FF;text-decoration:none;display:inline-block;" href="<?php echo $activity_url; ?>" target="_blank"><?php echo $user_name; ?></a> <span style="color:#60666d;display:inline-block;"><?php echo __('shared a post', 'peepso-email-digest'); ?></span>
          </p>
          <p style="margin-top: 5px;margin-bottom: 0;">
            <?php 
            echo $post_content;

            if (isset($files)) {
              echo '<ul>';
              foreach ($files as $file) {
                echo '<li><a href="' . $file['download_link'] . '">' . $file['name'] . '</li>';
              }
              echo '</ul>';
            }
            ?>
            <a style="color:#00B0FF;text-decoration:none;" href="<?php echo $activity_url; ?>" target="_blank"><?php echo __('Read more', 'peepso-email-digest'); ?></a>
          </p>
          <p style="margin-top: 5px;margin-bottom: 0;"><a style="color:#00B0FF;text-decoration:none;display:inline-block" href="<?php echo $activity_url; ?>" target="_blank"><?php echo __('Like', 'peepso-email-digest'); ?></a> <span style="color:#b9b9b9;display:inline-block">|</span> <a style="color:#00B0FF;text-decoration:none;display:inline-block" href="<?php echo $activity_url; ?>" target="_blank"><?php echo __('Comment', 'peepso-email-digest'); ?></a>
          </p>
        </div>
  
      </td>
    </tr>
  </tbody>
</table>
