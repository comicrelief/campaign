<?php
if (json_decode(file_get_contents($argv[1]))) echo $argv[1] . "is good\n";
else echo $argv[1] . "is bad\n";
