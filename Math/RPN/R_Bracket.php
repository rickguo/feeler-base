<?php

namespace Feeler\Base\Math\RPN;

class R_Bracket extends Bracket
{
    public function __construct($value)
    {
        parent::__construct("r_bracket", $value);
    }
}