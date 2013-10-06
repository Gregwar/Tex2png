<?php

require('../autoload.php');

use Gregwar\Tex2png\Tex2png;

echo "Generating sum.png...\n";

Tex2png::create('\sum_{i = 0}^{i = n} \frac{i}{2}')
    ->saveTo('sum.png')
    ->generate();
