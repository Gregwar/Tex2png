<?php

require_once('../Tex2png.php');

use Gregwar\Tex2png\Tex2png;

echo "Generating sum.png...\n";

Tex2png::create('\sum_{i}^{i+1} \frac{i}{2}')
    ->saveTo('sum.png')
    ->generate();
