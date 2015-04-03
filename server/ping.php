<?php

error_reporting(-1);

ini_set('display_errors', 'on');

chdir(dirname(__FILE__));

if ( isset($_POST) && isset($_POST['domains'] ) ) {
	$domains = json_decode($_POST['domains']);

	// Validate
	$domains = array_filter($domains, function($domain) {
		$parsed = parse_url('fake://' . $domain);

		if ( isset($parsed['host']) && $domain === $parsed['host'] && strpos(trim($domain, '.'), '.') !== false ) {
			return true;
		}

		return false;
	});

	$domains = array_unique($domains);

	if ( $domains ) {
		$mongoClient = new MongoClient(getenv('EXTRACT_SERVER_MONGO_DB_HOST'));

		$collection = $mongoClient->selectCollection(getenv('EXTRACT_SERVER_MONGO_DB_NAME'), 'domains');

		$batch = new MongoUpdateBatch($collection);

		foreach ( $domains as $domain ) {
			$batch->add(array(
				'upsert' => true,
				'q'      => array(
					'domain' => $domain,
					'month'  => date('Y-m')
					),
				'u'      => array(
					'$set' => array(
						'domain'       => $domain,
						'month'        => date('Y-m'),
						'processed_at' => null
						),
					'$inc' => array(
						'hits' => 1
						)
					)
				));
		}

		$batch->execute();
	}
}
