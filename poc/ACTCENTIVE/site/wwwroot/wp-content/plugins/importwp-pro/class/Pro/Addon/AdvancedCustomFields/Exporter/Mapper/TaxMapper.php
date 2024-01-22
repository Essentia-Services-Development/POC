<?php

namespace ImportWP\Pro\Addon\AdvancedCustomFields\Exporter\Mapper;

class TaxMapper extends Mapper
{
    /**
     * @param \ImportWP\EventHandler $event_handler
     */
    public function __construct($event_handler)
    {
        parent::__construct($event_handler, 'taxonomy');
        $this->acf_type = 'term';
    }
}
