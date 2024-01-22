<?php

class PeepSoMaintenanceDebug {

    private static $_instance = NULL;
    private $elapsed;
    private $debug;
    private $separator_top=     "┌───────────────────────────────────────────────────┬────────────────────────────────────────────────────┬───────┬────────────┐\n";
    private $separator=         "├───────────────────────────────────────────────────┼────────────────────────────────────────────────────┼───────┼────────────┤\n";
    private $separator_bottom=  "└───────────────────────────────────────────────────┴────────────────────────────────────────────────────┴───────┴────────────┘\n";
    public $precision;

    private function __construct() {
        PeepSoCron::initialize();
        $this->debug=array();
        $this->elapsed=0;
        $this->precision = 10;

        add_action(PeepSo::CRON_MAINTENANCE_EVENT, array(&$this, 'debug_summary'), 9999);
    }

    public static function get_instance() {
        if (NULL === self::$_instance)
            self::$_instance = new self();
        return (self::$_instance);
    }
    public function debug($method, $start, $stop, $count = 0) {

        $elapsed = $stop - $start;

        $this->elapsed+=$elapsed;
        $this->debug[]=array('m'=>$method,'e'=>$elapsed, 'c' => intval($count));
    }

    public function debug_summary() {
        if(isset($_GET['peepso_process_maintenance'])) {

            if(isset($_GET['formatted'])) { echo "<pre>\n";};

            echo $this->separator_top;
            $this->line('Elapsed', 'Class::Method', 'Count');

            $all_items = 0;

            if(count($this->debug)) {

                foreach($this->debug as $k=>$d) {
                    $this->line($d['e'], $d['m'], $d['c']);
                    $all_items += $d['c'];
                }
            }

            $this->line($this->elapsed,"Summary::" . count($this->debug) . " items", $all_items, 'separator_bottom');
        }
    }

    private function line($e, $l, $c, $s='separator') {

        // Format Count
        $fc = '';
        $len = 5-strlen($c);

        for($i=1;$i<=$len;$i++) {
            $fc.=' ';
        }
        $fc .= $c;

        // Format Elapsed
        $fe = '';
        if(is_numeric($e)) {
            $e = round($e,8);
        }

        $len = 10 - strlen($e);
        $fe = $e;
        for($i=1;$i<=$len;$i++) {
            $fe.=" ";
        }


        // Format Label
        $l = explode('::', $l);

        $class = $l[0];
        $method = $l[1];

        $fl = $class;
        $len = 50 - strlen($class);
        for($i=1;$i<=$len;$i++) {
            $fl.=" ";
        }

        $fl.="│ ".$method;
        $len = 50 - strlen($method);
        for($i=1;$i<=$len;$i++) {
            $fl.=" ";
        }


        echo sprintf("│ %s │ %s │ %s │\n", $fl, $fc ,$fe);
        echo $this->$s;
    }

}