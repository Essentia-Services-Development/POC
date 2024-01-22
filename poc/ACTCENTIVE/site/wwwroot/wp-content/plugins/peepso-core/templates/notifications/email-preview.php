<a href="<?php echo $url;?>">

    <div style="clear:both">

        <?php if(strlen($image)) { ?>
        <img src="<?php echo $image;?>" style="float:left; padding:5px;height:64px;">
        <?php } ?>

        <div style="clear:right;">

            <strong><?php echo $message;?></strong><br/>

            <?php echo $age;?><br/>

            <i><?php echo $preview;?></i>

        </div>

     </div>
</a>