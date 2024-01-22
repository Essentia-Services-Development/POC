<?php

if(!class_exists('PeepSo3_Developer_Tools_Page')) {
    require_once(dirname(__FILE__) . '/devtools_page.php');
    //new PeepSoError('Autoload issue: PeepSo3_Developer_Tools_Page not found ' . __FILE__);
}


class PeepSo3_Developer_Tools_Page_PeepSo_Log extends PeepSo3_Developer_Tools_Page
{
    public function __construct()
    {
        $this->title 		= 'PeepSo Log';
        $this->description	= __('Preview PeepSo logs in real time', 'peepso_debug');
    }

    public function page()
    {
        $this->page_start('peepso_log');

        $hash = md5(time());
        if(class_exists('PeepSo')) {
            ?>

            <script>
                jQuery(function ($) {
                    function getTail() {
                        $.get('admin-ajax.php?action=peepso_log&hash=<?php echo $hash;?>').done(function (data) {
                            var $tail = $('#tail').append(data),
                                $btn = $('#peepso_log_auto_scroll');
                            if ($btn[0].checked) {
                                $tail[0].scrollTop = $tail[0].scrollHeight;
                            }
                        }).always(function () {
                            setTimeout(getTail, 1000);
                        });
                    }

                    getTail();
                });
            </script>
            <pre id="tail" style="width:100%;height:800px;overflow:scroll"></pre>
            <?php
        } else {
            echo __('PeepSo needs to be installed and activated', 'peepso_debug');
        }
        $this->page_end();
    }

    public function page_data()
    {
        return;
    }

    public static function peepso_developer_tools_buttons($buttons)
    {
	    unset($buttons['reload']);
	    unset($buttons['export']);
        $buttons['autoscroll']='<form><input type="checkbox" id="peepso_log_auto_scroll" checked /> ' .  __('Auto scroll','peepso_debug').'</form>';
        return $buttons;
    }
}

// EOF