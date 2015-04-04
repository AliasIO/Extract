db.createCollection('domains')

db.domains.createIndex( { domain: 1, month: -1 }, { unique: true } )
db.domains.createIndex( { processed_at: -1 } )

db.createCollection('raw')
