<?php

error_reporting(-1);

chdir(dirname(__FILE__));

try {
} catch ( \Exception $e ) {
	echo $e->getMessage() . "\n";

	exit(1);
}

exit(0);
