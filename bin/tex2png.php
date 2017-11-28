#!/usr/bin/env php
<?php

require(__DIR__.'/../autoload.php');

use Gregwar\Tex2png\Tex2png;

/**
 * This is a simple command-line tool using the lib
 *
 * One can invoke it with:
 * php tex2png.php out.png
 *
 * And type the forumla on stdin (ctrl+D to end)
 *
 * This will output the given to out.png, and input on stdin
 */

$args = $_SERVER['argv'];

if (count($args) != 2) {
    $stderr = fopen('php://stderr', 'w');
    fprintf($stderr, "Usage: php tex2png.php output.png\n");
} else {
    $formula = trim(file_get_contents('php://stdin'));
    $output = $args[1];

    echo "Generating $output...\n";

    Tex2png::create($formula, 500)
        ->saveTo($output)
        ->generate();
}
