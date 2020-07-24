<?php

$output = shell_exec('php -S localhost:9090 -t public -d display_errors=1');
exit($output);