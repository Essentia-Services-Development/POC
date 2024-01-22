<?php


class PeepSoWidgetTutorGroups extends WP_Widget
{
    public function __construct( $id = NULL, $name = NULL, $args= NULL ) {

        $id     = ( NULL !== $id )  ? $id   : 'PeepSoWidgetTutorGroups';
        $name   = ( NULL !== $name )? $name : __('PeepSo TutorLMS: Courses &amp; Groups integration', 'peepsotutorlms');
        $args   = ( NULL !== $args )? $args : array('description' => __('Displays information about PeepSo Groups assigned to a TutorLMS Course (on a Course page) or TutorLMS Courses assigned to a PeepSo Group (on a Group page).', 'peepsotutorlms'),);

        parent::__construct(
            $id,
            $name,
            $args
        );
    }

    public function widget( $args, $instance ) {

        if(!class_exists('PeepSoGroupsPlugin')) {
            return;
        }

        $instance['title_cg']    = isset($instance['title_cg']) ? strip_tags($instance['title_cg']) : '';
        $instance['title_gc']    = isset($instance['title_gc']) ? strip_tags($instance['title_gc']) : '';

        /**
         * If we are in a PeepSo Group context and LearnDash is active
         */
        $PeepSoGroupsShortcode = PeepSoGroupsShortcode::get_instance();
        $group_id = $PeepSoGroupsShortcode->group_id;

        if (!empty($group_id) && defined('TUTOR_VERSION')) {

            $PeepSoTutorCourseGroups = new PeepSoTutorCourseGroups();
            $courses = $PeepSoTutorCourseGroups->get_courses_by_group($group_id);

            $instance['courses'] = $courses;

            $instance = apply_filters('peepso_widget_instance', $instance);
            if(count($instance['courses'])) {
                PeepSoTemplate::exec_template('widgets', 'tutor-group-courses', array('args' => $args, 'instance' => $instance));
            }
        }

    }

    public function form( $instance ) {

        $instance['fields'] = array(
            // general
            'limit'     => FALSE,
            'title'     => FALSE,

            // peepso
            'integrated'   => FALSE,
            'position'  => FALSE,
            'hideempty' => FALSE,
        );

        if (!isset($instance['title_cg'])) {
            $instance['title_cg'] = __('Groups related to this Course', 'peepsotutorlms');
        }

        if (!isset($instance['title_gc'])) {
            $instance['title_gc'] = __('Courses related to this Group', 'peepsotutorlms');
        }

        $this->instance = $instance;

        $settings =  apply_filters('peepso_widget_form', array('html'=>'', 'that'=>$this,'instance'=>$instance));

        ob_start();
        $title_gc = !empty($instance['title_gc']) ? $instance['title_gc'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title_gc'); ?>">
                <?php echo __('Title (Group Page)', 'peepsotutorlms'); ?>
                <input type="text" value="<?php echo $title_gc;?>" class="widefat" id="<?php echo $this->get_field_id('title_gc'); ?>"
                        name="<?php echo $this->get_field_name('title_gc'); ?>">
            </label>

            <small>
                <?php echo __('Title when on PeepSo Group', 'peepsotutorlms');?>
            </small>
        </p>

        <?php
        $settings['html'] .= ob_get_clean();
        echo $settings['html'];
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title_gc']    = isset($new_instance['title_gc']) ? strip_tags($new_instance['title_gc']) : '';

        return $instance;
    }
}

// EOF