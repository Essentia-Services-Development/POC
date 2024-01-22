<?php

namespace ImportWP\Pro\Addon\AdvancedCustomFields\Exporter\Mapper;

class PostMapper extends Mapper
{
    /**
     * @param \ImportWP\EventHandler $event_handler
     */
    public function __construct($event_handler)
    {
        parent::__construct($event_handler, 'post_type');
        $this->acf_type = 'post';
    }
}
