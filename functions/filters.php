<?php

add_filter('fs_price_format','fs_price_format',10,3);
function  fs_price_format($price,$delimiter='.',$thousands_separator=' '){
    $price=number_format($price,2,$delimiter,$thousands_separator);
return $price;
}

