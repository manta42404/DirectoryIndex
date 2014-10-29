<?php

return array(

    // Basic settings
    'hide_dot_files'     => true,
    'list_folders_first' => true,
    'list_sort_order'    => 'natcasesort', // asort, arsort, ksort, krsort, natcasesort, natsort, shuffle
    'theme_name'         => 'dark', // dark OR light
    'time_format'        => 'Y-m-d H:i', // consult the PHP manual: http://php.net/manual/function.date.php
 
    // Hidden and forbidden files
    'forbidden' => array(
        '*.php*',
    ),

);
