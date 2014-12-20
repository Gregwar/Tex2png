<?php

require('../autoload.php');

use Gregwar\Tex2png\Tex2png;

$args = $_SERVER['argv'];

if (count($args) != 3) {
    $stderr = fopen('php://stderr', 'w');
    fprintf($stderr, "Usage: php tex2png.php formula output.png\n");
} else {
    $formula = $args[1];
    $output = $args[2];

    echo "Generating $output...\n";

    Tex2png::create($formula)
        ->saveTo($output)
        ->generate();
}
