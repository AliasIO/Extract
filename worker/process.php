<?php

error_reporting(-1);

ini_set('display_errors', 'on');

chdir(dirname(__FILE__));

try {
	$dbh = new PDO('sqlite::memory:');

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$dbh->exec(file_get_contents('db/schema.sql'));

	// Capture data
	$host = null;

	while ( $line = fgets(STDIN) ) {
		if ( preg_match('/^([a-z]+) (.+)$/', $line, $match) && isset($match[2]) ) {
			$key   = $match[1];
			$value = $match[2];

			if ( $key == 'url' ) {
				$url = parse_url($value) ?: [];

				$host = isset($url['host']) ? $url['host'] : null;

				continue;
			}

			if ( $host ) {
				$sth = $dbh->prepare('
					INSERT OR IGNORE INTO data (
						host,
						key,
						value
					) VALUES (
						:host,
						:key,
						:value
					)
					');

				$sth->bindParam('host',  $host,  PDO::PARAM_STR);
				$sth->bindParam('key',   $key,   PDO::PARAM_STR);
				$sth->bindParam('value', $value, PDO::PARAM_STR);

				$sth->execute();
			}
		}
	}

	// Preprocess data
	$sth = $dbh->prepare('SELECT DISTINCT host FROM data');

	$sth->execute();

	$hosts = $sth->fetchAll(PDO::FETCH_COLUMN, 0);

	$sth = $dbh->prepare('SELECT DISTINCT key FROM data');

	$sth->execute();

	$keys = $sth->fetchAll(PDO::FETCH_COLUMN, 0);

	foreach ( $hosts as $host ) {
		$data = [];

		foreach ( $keys as $key ){
			$data[$key] = [];
		}

		$sth = $dbh->prepare('
			SELECT
				key,
				value
			FROM data
			WHERE
				host = :host
			');

		$sth->bindParam('host', $host, PDO::PARAM_STR);

		$sth->execute();

		$results = $sth->fetchAll(PDO::FETCH_OBJ);

		foreach ( $results as $result ) {
			$json = @json_decode($result->value, true);

			if ( $json ) {
				$data[$result->key] = array_merge($data[$result->key], $json);
			}
		}

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
} catch ( \Exception $e ) {
	echo $e->getMessage() . "\n";

	exit(1);
}

exit(0);
