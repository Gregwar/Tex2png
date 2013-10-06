<?php

require('../autoload.php');

use Gregwar\Tex2png\Tex2png;

echo "Generating cache file...\n";

echo Tex2png::create('\sum_{i = 0}^{i = n} \frac{i}{2}')
    ->generate()
    ->getFile(), "\n";
