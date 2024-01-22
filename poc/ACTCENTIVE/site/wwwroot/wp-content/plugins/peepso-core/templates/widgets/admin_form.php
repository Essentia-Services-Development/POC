<style type="text/css">
	.widget_integrated_description {
		color: #666666;
		font-size:11px;
		font-style: italic;
		text-align:justify;
	}
</style>
<?php
#$instance   = $widget['instance'];  // widget settings array
#$that       = $widget['that'];      // the object itself

// general
if(!isset($instance['fields']['section_general']) || TRUE === $instance['fields']['section_general'])
{
	echo '<h3>' . __('General settings', 'peepso-core') . '</h3>';
}

// general.title
if(isset($instance['fields']['title']) && TRUE === $instance['fields']['title'])
{
	$title = !empty($instance['title']) ? $instance['title'] : '';
	?>
	<p>
		<label for="<?php echo $that->get_field_id('title'); ?>"><?php echo __('Title:'); ?></label>
		<input class="widefat" id="<?php echo $that->get_field_id('title'); ?>"
			   name="<?php echo $that->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
	</p>


	<?php
}


// general.limit
if(isset($instance['fields']['limit']) && TRUE === $instance['fields']['limit'])
{
	$limit = ! empty( $instance['limit'] ) ? $instance['limit'] : 12;
	?>
	<p>
		<label for="<?php echo $that->get_field_id( 'limit' ); ?>"><?php echo __( 'Limit:', 'peepso-core'); ?></label>
		<select class="widefat" id="<?php echo $that->get_field_id( 'limit' ); ?>" name="<?php echo $that->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
			<?php
			$options = array();
			for ($i = 1; $i <= 100; $i++) {
				if ($i <= 10 || $i % 2 == 0) {
					$options[] = $i;
				}
			}

			if(!empty($instance['fields']['limit_options'])) {
			    $options = $instance['fields']['limit_options'];
            }

			foreach($options as $option)
			{
				?>
				<option value="<?php echo $option;?>" <?php if($option==$limit) echo " selected ";?> ><?php echo $option;?></option>
				<?php
			}
			?>
		</select>
	</p>
	<?php
}

// Hide empty
if (isset($instance['fields']['hideempty']) && TRUE == $instance['fields']['hideempty'])
{
	$hideempty = !empty($instance['hideempty']) ? $instance['hideempty'] : 0;
	?>
	<p>
		<input <?php if (1 === $hideempty) echo ' checked="checked" ';?> value="1" type="checkbox" class="ace ace-switch ace-switch-2"
																		 name="<?php echo $that->get_field_name('hideempty');?>"
																		 id="<?php echo $that->get_field_id('hideempty');?>">
		<label class="lbl" for="<?php echo $that->get_field_id('hideempty'); ?>">
			<?php echo __('Hide when empty', 'peepso-core'); ?>
		</label>
	</p>
	<?php
}


// Show online members count
if (isset($instance['fields']['totalonline']) && TRUE == $instance['fields']['totalonline'])
{
    $hideempty = !empty($instance['totalonline']) ? $instance['totalonline'] : 0;
    ?>
    <p>
        <input <?php if (1 === $hideempty) echo ' checked="checked" ';?> value="1" type="checkbox" class="ace ace-switch ace-switch-2"
                                                                         name="<?php echo $that->get_field_name('totalonline');?>"
                                                                         id="<?php echo $that->get_field_id('totalonline');?>">
        <label class="lbl" for="<?php echo $that->get_field_id('totalonline'); ?>">
            <?php echo __('Show total online members count', 'peepso-core'); ?>
        </label>
    </p>
    <?php
}

// Show total members count
if (isset($instance['fields']['totalmember']) && TRUE == $instance['fields']['totalmember'])
{
    $hideempty = !empty($instance['totalmember']) ? $instance['totalmember'] : 0;
    ?>
    <p>
        <input <?php if (1 === $hideempty) echo ' checked="checked" ';?> value="1" type="checkbox" class="ace ace-switch ace-switch-2"
                                                                         name="<?php echo $that->get_field_name('totalmember');?>"
                                                                         id="<?php echo $that->get_field_id('totalmember');?>">
        <label class="lbl" for="<?php echo $that->get_field_id('totalmember'); ?>">
            <?php echo __('Show total members count', 'peepso-core'); ?>
        </label>
    </p>
    <?php
}