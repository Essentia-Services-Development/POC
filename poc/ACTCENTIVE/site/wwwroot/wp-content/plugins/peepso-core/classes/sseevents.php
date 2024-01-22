<?php

class PeepSoSSEEvents {
    public static function trigger($event, $user_id = NULL) {

        return TRUE; //SSE is not working yet

        if(!$user_id) {
            $user_id = get_current_user_id();
        }

        $ds = DIRECTORY_SEPARATOR;

        $dir = PeepSo::get_peepso_dir()  . 'sse' . $ds . 'events' . $ds . $user_id . $ds;

        $tokens = @scandir($dir);

        if(is_array($tokens) && count($tokens)) {
            foreach($tokens as $token) {
                if(in_array($token, array('.','..'))) {
                    continue;
                }
                $file = $dir.$token.$ds.$event;
                #echo "$file\n";
                $h=fopen($file, 'w');
                fwrite($h, '1');
            }
        }
    }
}