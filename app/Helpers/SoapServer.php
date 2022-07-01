<?php

namespace App\Helpers;

ini_set('soap.wsdl_cache_enabled', false);

class SoapServer
{

    function bookYear($book)
    {
        // list of the books
        $_books=[
            ['name'=>'test 1','year'=>2011],
            ['name'=>'test 2','year'=>2012],
            ['name'=>'test 3','year'=>2013],
        ];
        // search book by name
        foreach($_books as $bk)
            if($bk['name']==$book->name)
                return $bk['year']; // book found

        return 0; // book not found
    }



}
