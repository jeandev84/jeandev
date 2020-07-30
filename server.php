<?php

echo "Server Run : http://localhost:9090\n";
echo shell_exec('php -S localhost:9090 -t public -d display_errors=1') . "\n";
exec('exit');

