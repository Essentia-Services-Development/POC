d„zd<?php exit; ?>a:6:{s:10:"last_error";s:0:"";s:10:"last_query";s:671:"SELECT *, CASE 
WHEN `type` = 3 THEN 0
WHEN `type` = 4 THEN 1
WHEN `type` = 7 THEN 2
WHEN `type` = 6 THEN 3
WHEN `type` = 5 THEN 4
WHEN `type` = 9 THEN 5
WHEN `type` = 8 THEN 6
WHEN `type` = 2 THEN 7
WHEN `type` = 1 THEN 8
ELSE 9999
END AS `typeSort`, CASE 
WHEN `type` = 3 THEN `parameters`
WHEN `type` = 4 THEN `parameters`
WHEN `type` = 1 THEN `IP`
WHEN `type` = 9 THEN `IP`
WHEN `type` = 5 THEN `IP`
WHEN `type` = 6 THEN `IP`
WHEN `type` = 7 THEN `IP`
WHEN `type` = 2 THEN `IP`
WHEN `type` = 8 THEN `IP`
ELSE 9999
END AS `detailSort`
 FROM `wp_wfBlocks7` WHERE `type` IN (3) AND (`expiration` = 0 OR `expiration` > UNIX_TIMESTAMP()) ORDER BY `typeSort` ASC, `id` DESC";s:11:"last_result";a:0:{}s:8:"col_info";a:11:{i:0;O:8:"stdClass":13:{s:4:"name";s:2:"id";s:7:"orgname";s:2:"id";s:5:"table";s:12:"wp_wfBlocks7";s:8:"orgtable";s:12:"wp_wfblocks7";s:3:"def";s:0:"";s:2:"db";s:32:"act-wp-linux-nginx-production-db";s:7:"catalog";s:3:"def";s:10:"max_length";i:0;s:6:"length";i:20;s:9:"charsetnr";i:63;s:5:"flags";i:49699;s:4:"type";i:8;s:8:"decimals";i:0;}i:1;O:8:"stdClass":13:{s:4:"name";s:4:"type";s:7:"orgname";s:4:"type";s:5:"table";s:12:"wp_wfBlocks7";s:8:"orgtable";s:12:"wp_wfblocks7";s:3:"def";s:0:"";s:2:"db";s:32:"act-wp-linux-nginx-production-db";s:7:"catalog";s:3:"def";s:10:"max_length";i:0;s:6:"length";i:10;s:9:"charsetnr";i:63;s:5:"flags";i:49193;s:4:"type";i:3;s:8:"decimals";i:0;}i:2;O:8:"stdClass":13:{s:4:"name";s:2:"IP";s:7:"orgname";s:2:"IP";s:5:"table";s:12:"wp_wfBlocks7";s:8:"orgtable";s:12:"wp_wfblocks7";s:3:"def";s:0:"";s:2:"db";s:32:"act-wp-linux-nginx-production-db";s:7:"catalog";s:3:"def";s:10:"max_length";i:0;s:6:"length";i:16;s:9:"charsetnr";i:63;s:5:"flags";i:16521;s:4:"type";i:254;s:8:"decimals";i:0;}i:3;O:8:"stdClass":13:{s:4:"name";s:11:"blockedTime";s:7:"orgname";s:11:"blockedTime";s:5:"table";s:12:"wp_wfBlocks7";s:8:"orgtable";s:12:"wp_wfblocks7";s:3:"def";s:0:"";s:2:"db";s:32:"act-wp-linux-nginx-production-db";s:7:"catalog";s:3:"def";s:10:"max_length";i:0;s:6:"length";i:20;s:9:"charsetnr";i:63;s:5:"flags";i:36865;s:4:"type";i:8;s:8:"decimals";i:0;}i:4;O:8:"stdClass":13:{s:4:"name";s:6:"reason";s:7:"orgname";s:6:"reason";s:5:"table";s:12:"wp_wfBlocks7";s:8:"orgtable";s:12:"wp_wfblocks7";s:3:"def";s:0:"";s:2:"db";s:32:"act-wp-linux-nginx-production-db";s:7:"catalog";s:3:"def";s:10:"max_length";i:0;s:6:"length";i:1020;s:9:"charsetnr";i:246;s:5:"flags";i:4097;s:4:"type";i:253;s:8:"decimals";i:0;}i:5;O:8:"stdClass":13:{s:4:"name";s:11:"lastAttempt";s:7:"orgname";s:11:"lastAttempt";s:5:"table";s:12:"wp_wfBlocks7";s:8:"orgtable";s:12:"wp_wfblocks7";s:3:"def";s:0:"";s:2:"db";s:32:"act-wp-linux-nginx-production-db";s:7:"catalog";s:3:"def";s:10:"max_length";i:0;s:6:"length";i:10;s:9:"charsetnr";i:63;s:5:"flags";i:32800;s:4:"type";i:3;s:8:"decimals";i:0;}i:6;O:8:"stdClass":13:{s:4:"name";s:11:"blockedHits";s:7:"orgname";s:11:"blockedHits";s:5:"table";s:12:"wp_wfBlocks7";s:8:"orgtable";s:12:"wp_wfblocks7";s:3:"def";s:0:"";s:2:"db";s:32:"act-wp-linux-nginx-production-db";s:7:"catalog";s:3:"def";s:10:"max_length";i:0;s:6:"length";i:10;s:9:"charsetnr";i:63;s:5:"flags";i:32800;s:4:"type";i:3;s:8:"decimals";i:0;}i:7;O:8:"stdClass":13:{s:4:"name";s:10:"expiration";s:7:"orgname";s:10:"expiration";s:5:"table";s:12:"wp_wfBlocks7";s:8:"orgtable";s:12:"wp_wfblocks7";s:3:"def";s:0:"";s:2:"db";s:32:"act-wp-linux-nginx-production-db";s:7:"catalog";s:3:"def";s:10:"max_length";i:0;s:6:"length";i:20;s:9:"charsetnr";i:63;s:5:"flags";i:49193;s:4:"type";i:8;s:8:"decimals";i:0;}i:8;O:8:"stdClass":13:{s:4:"name";s:10:"parameters";s:7:"orgname";s:10:"parameters";s:5:"table";s:12:"wp_wfBlocks7";s:8:"orgtable";s:12:"wp_wfblocks7";s:3:"def";s:0:"";s:2:"db";s:32:"act-wp-linux-nginx-production-db";s:7:"catalog";s:3:"def";s:10:"max_length";i:0;s:6:"length";i:262140;s:9:"charsetnr";i:246;s:5:"flags";i:16;s:4:"type";i:252;s:8:"decimals";i:0;}i:9;O:8:"stdClass":13:{s:4:"name";s:8:"typeSort";s:7:"orgname";s:0:"";s:5:"table";s:0:"";s:8:"orgtable";s:0:"";s:3:"def";s:0:"";s:2:"db";s:0:"";s:7:"catalog";s:3:"def";s:10:"max_length";i:0;s:6:"length";i:4;s:9:"charsetnr";i:63;s:5:"flags";i:32897;s:4:"type";i:8;s:8:"decimals";i:0;}i:10;O:8:"stdClass":13:{s:4:"name";s:10:"detailSort";s:7:"orgname";s:0:"";s:5:"table";s:0:"";s:8:"orgtable";s:0:"";s:3:"def";s:0:"";s:2:"db";s:0:"";s:7:"catalog";s:3:"def";s:10:"max_length";i:0;s:6:"length";i:65535;s:9:"charsetnr";i:63;s:5:"flags";i:128;s:4:"type";i:252;s:8:"decimals";i:31;}}s:8:"num_rows";i:0;s:10:"return_val";i:0;}