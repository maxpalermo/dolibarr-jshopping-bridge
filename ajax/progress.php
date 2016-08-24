<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');


$i=0;
While($i!=100)
{
    $i++;
    usleep(200000);
    print "id: $i\n";
    print "event: progress\n";
    print "data: {\"progress\": \"$i\", \"time\": \"". date("d/m/Y H:i:s") . "\"}\n\n";
    ob_flush();
    flush();
}
