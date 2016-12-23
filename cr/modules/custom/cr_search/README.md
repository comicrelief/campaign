## CR Search
This module provides a preconfigured search.
By the default should use database as server because not all developers has solr
and also production won't be reinstalled.

## CR Search architecture
|type|name|comment|
|--- | --- | --- | --- | --- |
|view|search|Shows the page and filter block|
|view display|article.search_index|Provides default display|
|view display|landing.search_index|Provides default display|
|search server|database|Provides default config. for database|
|search index|cr_content|Provides default config. for indexed content|
