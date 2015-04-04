<?php

error_reporting(-1);

ini_set('display_errors', 'on');

chdir(dirname(__FILE__));

$client = new MongoClient(getenv('MONGO_CONN_STR'));

$db = $client->selectDB(getenv('MONGO_DB_NAME'));

if ( !empty($_POST) ) {
	if ( isset($_POST['json']) ) {
		$json = json_decode($_POST['json']);

		$db->raw->insert($json);

		exit('OK');
	}
} else {
	// Get the ten oldest unprocessed domains
	$cursor = $db->domains
		->find(['processed_at' => null], ['domain' => true])
		->limit(10)
		->sort(['_id' => 1]);

	$domains = [];

	foreach ( $cursor as $doc ) {
		$domains[] = $doc['domain'];
	}

	if ( $domains ) {
		$batch = new MongoUpdateBatch($db->domains);

		foreach ( $domains as $domain ) {
			$batch->add([
				'q' => ['domain' => $domain],
				'u' => ['$set'   => ['processed_at' => new MongoDate]]
				]);
		}

		//$batch->execute();
	}

	exit(implode("\n", $domains));
}
