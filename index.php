<?php

namespace Extract;

error_reporting(-1);

chdir(dirname(__FILE__));

require 'vendor/autoload.php';

try {
	$dbh = new \PDO('sqlite::memory:');

	$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

	$dbh->exec(file_get_contents('db/schema.sql'));

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

				$sth->bindParam('host',  $host,  \PDO::PARAM_STR);
				$sth->bindParam('key',   $key,   \PDO::PARAM_STR);
				$sth->bindParam('value', $value, \PDO::PARAM_STR);

				$sth->execute();
			}
		}
	}


	$sth = $dbh->prepare('SELECT * FROM data');
	$sth->execute();
	var_dump($sth->fetchAll());
	exit;


	$sth = $dbh->prepare('SELECT DISTINCT host FROM data');

	$sth->execute();

	$hosts = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);

	$sth = $dbh->prepare('SELECT DISTINCT key FROM data');

	$sth->execute();

	$keys = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);

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

		$sth->bindParam('host', $host, \PDO::PARAM_STR);

		$sth->execute();

		$results = $sth->fetchAll(\PDO::FETCH_OBJ);

		foreach ( $results as $result ) {
			$json = @json_decode($result->value, true);

			if ( $json ) {
				$data[$result->key] = array_merge($data[$result->key], $json);
			}
		}

		var_dump($data);
	}
} catch ( \Exception $e ) {
	echo $e->getMessage() . "\n";

	exit(1);
}

exit(0);
