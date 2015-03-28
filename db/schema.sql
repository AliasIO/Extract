CREATE TABLE data (
	host  TEXT,
	key   TEXT,
	value TEXT
);

CREATE INDEX        host     ON data ( host );
CREATE UNIQUE INDEX host_key ON data ( host, key );
