<?php if(strlen($header)) { ?>
    <h2><?php echo $header;?></h2>
<?php } ?>


<?php
$args = [];
$args['no_cover'] = $no_cover;

PeepSoTemplate::exec_template('general', 'register-panel', $args);?>

<?php if(strlen($header_comments)) { ?>
    <h3><?php echo $header_comments; ?></h3>
<?php } ?>

<!--{peepso_comments}-->