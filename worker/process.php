<?php

error_reporting(-1);

ini_set('display_errors', 'on');

chdir(dirname(__FILE__));

try {
	$url  = isset($argv[1]) ? parse_url($argv[1])   : [];
	$meta = isset($argv[2]) ? json_decode($argv[2]) : null;

	$domain = isset($url['scheme']) && isset($url['host']) ? $url['scheme'] . '://' . $url['host'] : null;

	if ( !$domain ) {
		throw new Exception('Argument invalid');
	}

	// Capture data
	$data = [];

	while ( $line = fgets(STDIN) ) {
		$json = json_decode($line, true);

		if ( $json && is_array($json) ) {
			foreach ( $json as $key => $value ) {
				if ( !isset($data[$key]) ) {
					$data[$key] = $value;
				} else {
					$data[$key] = array_merge_recursive_distinct($data[$key], $value);
				}
			}
		}
	}

	if ( $data ) {
		$data['website'] = $domain;

		if ( $meta && is_array($meta) ) {
			foreach ( $meta as $key => $value ) {
				$data['key'] = $value;
			}
		}

		ksort($data);

		// Send to server
		$stream = stream_context_create([
			'http' => [
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query(['json' => json_encode($data)]),
				]
			]);

		echo file_get_contents(getenv('SERVER_URL') . '/worker', false, $stream) . "\n";
	}
} catch ( Exception $e ) {
	echo $e->getMessage() . "\n";

	exit(1);
}

exit(0);

function array_merge_recursive_distinct(array &$array1, array &$array2)
{
	$merged = $array1;

	foreach ( $array2 as $key => &$value ) {
		if ( is_array($value) && isset($merged[$key]) && is_array($merged[$key]) ) {
			$merged[$key] = array_merge_recursive_distinct($merged[$key], $value);
		} else {
			$merged[$key] = $value;
		}
	}

	return $merged;
}
