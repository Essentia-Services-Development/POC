<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php if(!empty($import_comments)):?>
    <?php foreach ($import_comments as $key => $comment): ?>
        <div class="helpful-review rh-cartbox">
            <div class="quote-top"><i class="rhicon rhi-quote-left"></i></div>
            <div class="quote-bottom"><i class="rhicon rhi-quote-right"></i></div>
            <div class="user-review-ae-comment">
                <span><?php echo strip_tags($comment['comment']); ?></span>
            </div>
            <?php if (!empty($comment['date'])): ?>
                <span class="helpful-date"><strong class="font120"><?php echo (isset($comment['name'])) ? $comment['name'] : '';?></strong> - <?php echo gmdate("F j, Y", $comment['date']); ?></span>
            <?php endif ;?>
        </div>
    <?php endforeach; ?>
<?php endif ;?>