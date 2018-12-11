<?php

include "Redis.php";

if(M_Redis::connect()){
    M_Redis::str_set('key1','value1');
}

echo M_Redis::str_get('key1');