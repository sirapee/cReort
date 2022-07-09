<?php

namespace App\Models;

use JetBrains\PhpStorm\ArrayShape;

class Coverage
{
    #[ArrayShape(['sol' => "string", 'region' => "string", 'bank' => "string"])]
    public static function allCoverage(): array
    {
        //Todo Response based on role
        return [
            ['key' => 'sol', 'value' => 'Single Branch'],
            ['key' => 'region', 'value' => 'A Region'],
            ['key' => 'bank', 'value' => 'Bank Wide']
        ];
    }

}
