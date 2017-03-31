## CR Search DB
This module provides a preconfigured search DB.
By the default should use database as server because not all developers has solr
and also production won't be reinstalled.

## CR Search architecture
|type|name|comment|
|--- | --- | --- | --- | --- |
|view|search|Shows the page and filter block|
|search server|database|Provides default config. for database|
|search index|cr_content|Provides default config. for indexed content|
