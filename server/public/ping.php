<?php

error_reporting(-1);

ini_set('display_errors', 'on');

chdir(dirname(__FILE__));

if ( isset($_POST) && isset($_POST['domains'] ) ) {
	$domains = json_decode($_POST['domains']);

	// Validate
	$domains = array_filter($domains, function($domain) {
		$parsed = parse_url($domain);

		return
			isset($parsed['scheme']) && ( $parsed['scheme'] === 'http' || $parsed['scheme'] === 'https' ) &&
			isset($parsed['host']) && strpos(trim($parsed['host'], '.'), '.') !== false && !filter_var($parsed['host'], FILTER_VALIDATE_IP) &&
			$domain === $parsed['scheme'] . '://' . $parsed['host'];
	});

	$domains = array_unique($domains);

	if ( $domains ) {
		$client = new MongoClient(getenv('MONGO_CONN_STR'));

		$collection = $client->selectCollection(getenv('MONGO_DB_NAME'), 'domains');

		$batch = new MongoUpdateBatch($collection);

		foreach ( $domains as $domain ) {
			$batch->add([
				'upsert' => true,
				'q'      => [
					'domain' => $domain,
					'month'  => (int) date('ym')
					],
				'u'      => [
					'$set' => [
						'domain'       => $domain,
						'month'        => (int) date('ym'),
						'processed_at' => null
						],
					'$inc' => [
						'hits' => 1
						]
					]
				]);
		}

		$batch->execute();
	}
}
