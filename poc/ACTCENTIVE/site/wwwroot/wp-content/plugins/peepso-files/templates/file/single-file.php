<div class="ps-file-item-wrapper ps-js-file" data-id="<?php echo $id; ?>">
  <div class="ps-file-item-content">
    <div class="ps-file-item-content__icon ps-file-item-content__icon--<?php echo strtolower($extension);?>">
      <div class="ps-file-item-content__icon-image">
        <?php echo $extension; ?>
      </div>
    </div>

    <div class="ps-file-item-content__details">
      <div class="ps-file-item-content__name" title="<?php echo $name; ?>"><?php echo $name; ?></div>
      <div class="ps-file-item-content__size"><?php echo $size; ?></div>
    </div>
  </div>
  <div class="ps-file-item-action">
    <a class="ps-tip ps-tip--arrow"
      aria-label="<?php echo __('Download', 'peepsofileuploads'); ?>"
      data-id="<?php echo $id; ?>"
      href="<?php echo $download_link; ?>"><i class="gcis gci-download"></i>
    </a>
    <?php if ($can_delete): ?>
    <a class="ps-tip ps-tip--arrow ps-js-file-delete"
      aria-label="<?php echo __('Delete', 'peepsofileuploads'); ?>" href="#">
      <i class="gcis gci-trash"></i>
    </a>
    <?php endif; ?>
  </div>
</div>
