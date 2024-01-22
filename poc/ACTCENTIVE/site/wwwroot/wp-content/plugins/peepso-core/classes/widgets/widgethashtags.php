<?php


class PeepSoWidgetHashtags extends WP_Widget
{

    /**
     * Set up the widget name etc
     *
     * Last modified: July 29 2015
     * Last reviewed: July 29 2015
     * Review status: OK
     */
    public function __construct( $id = NULL, $name = NULL, $args= NULL ) {

        $id     = ( NULL !== $id )  ? $id   : 'PeepSoWidgethashtags';
        $name   = ( NULL !== $name )? $name : __('PeepSo Hashtags', 'peepso-core');
        $args   = ( NULL !== $args )? $args : array('description' => __('PeepSo Hashtags Widget', 'peepso-core'),);

        parent::__construct(
            $id,
            $name,
            $args
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     * @return void
     *
     * Last modified: July 29 2015
     * Last reviewed: July 29 2015
     * Review status: @TODO
     */
    public function widget( $args, $instance ) {

        // Additional shared adjustments
        $instance = apply_filters('peepso_widget_instance', $instance);

        $instance['template'] = 'hashtags.tpl';

        if(!isset($instance['sortby'])) {
            $instance['sortby']=0;
        }

        if(!isset($instance['sortorder'])) {
            $instance['sortorder']=0;
        }

        if(!isset($instance['displaystyle'])) {
            $instance['displaystyle']=0;
        }

        if(!array_key_exists('limit', $instance)) {
            $instance['limit'] = 12;
        }

        if(!array_key_exists('minsize', $instance)) {
            $instance['minsize'] = 0;
        }

        PeepSoTemplate::exec_template( 'widgets', $instance['template'], array( 'args'=>$args, 'instance' => $instance ) );
    }

    /**
     * Outputs the admin options form
     *
     * @param array $instance The widget options
     */
    public function form( $instance ) {

        $limit_options = array();

        for($i=1; $i<=100; $i++) {
            $limit_options[]=$i;
        }

        $instance['fields'] = array(
            // general
            'limit'         => TRUE,
            'limit_options' => $limit_options,
            'title'         => TRUE,

            // peepso
            'integrated'    => FALSE,
            'position'      => FALSE,
            'hideempty'     => FALSE,
        );

        if (!isset($instance['title'])) {
            $instance['title'] = __('Community Hashtags', 'peepso-core');
        }

        $this->instance = $instance;

        $settings =  apply_filters('peepso_widget_form', array('html'=>'', 'that'=>$this,'instance'=>$instance));
        echo $settings['html'];

        $minsize = !empty($instance['minsize']) ? $instance['minsize'] : 0;
        $sortby = !empty($instance['sortby']) ? $instance['sortby'] : 0;
        $sortorder = !empty($instance['sortorder']) ? $instance['sortorder'] : 0;
        $displaystyle= !empty($instance['displaystyle']) ? $instance['displaystyle'] : 0;
        ?>

        <p>
            <select name="<?php echo $this->get_field_name('displaystyle');?>" id="<?php echo $this->get_field_id('displaystyle');?>">
                <option value="0" <?php if (0 === $displaystyle ) { echo ' selected '; }?>><?php echo __('Cloud','peepso-core');?></option>
                <option value="1" <?php if (1 === $displaystyle ) { echo ' selected '; }?>><?php echo __('List','peepso-core');?></option>
                <option value="2" <?php if (2 === $displaystyle ) { echo ' selected '; }?>><?php echo __('Mixed','peepso-core');?></option>
            </select>

            <select name="<?php echo $this->get_field_name('sortby');?>" id="<?php echo $this->get_field_id('sortby');?>">
                <option value="0" <?php if (0 === $sortby ) { echo ' selected '; }?>><?php echo __('Sorted by name','peepso-core');?></option>
                <option value="1" <?php if (1 === $sortby ) { echo ' selected '; }?>><?php echo __('Sorted by size','peepso-core');?></option>
            </select>

            <select name="<?php echo $this->get_field_name('sortorder');?>" id="<?php echo $this->get_field_id('sortorder');?>">
                <option value="0" <?php if (0 === $sortorder ) { echo ' selected '; }?>><?php echo __('&uarr;','peepso-core');?></option>
                <option value="1" <?php if (1 === $sortorder ) { echo ' selected '; }?>><?php echo __('&darr;','peepso-core');?></option>
            </select>


            <label for="<?php echo $this->get_field_id('minsize');?>"><?php echo __('Minimum post count','peepso-core');?>:</label>
            <select name="<?php echo $this->get_field_name('minsize');?>" id="<?php echo $this->get_field_id('minsize');?>">
                <?php for($i=0; $i<=100; $i++) { ?>
                    <option value="<?php echo $i;?>>" <?php if ($i === $minsize ) { echo ' selected '; }?>><?php echo $i;?></option>
                <?php } ?>
            </select>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title']       = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['limit']       = isset($new_instance['limit']) ? (int) $new_instance['limit'] : 12;

        $instance['minsize']      = isset($new_instance['minsize']) ? (int) $new_instance['minsize'] : 0;

        $instance['sortby']      = isset($new_instance['sortby']) ? (int) $new_instance['sortby'] : 0;
        $instance['sortorder']   = isset($new_instance['sortorder']) ? (int) $new_instance['sortorder'] : 0;
        $instance['displaystyle']   = isset($new_instance['displaystyle']) ? (int) $new_instance['displaystyle'] : 0;

        return $instance;
    }
}

// EOF