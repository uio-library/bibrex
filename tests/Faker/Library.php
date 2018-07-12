<?php

namespace Tests\Faker;

use Faker\Provider\Base;

class Library extends Base
{
    public function userBarcode()
    {
        $firstPart = $this->numberBetween(2, 4);
        $lastPart = 10 - $firstPart;
        $id = self::lexify(str_repeat('?', $firstPart)) . self::randomNumber($lastPart, true);
        return $this->generator->parse($id);
    }
}
