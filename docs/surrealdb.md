# Surreal Db


## Running in Docker

Docker can be used to manage and run SurrealDB database instances without the need to install any command-line tools. 
The SurrealDB docker container contains the full command-line tools for importing and exporting data from a running server, or for running a server itself.

### Creating a new container

The script create a new image pulling the latest version

``` PowerShell
docker run --name idea_engine_surrealdb --pull always -p 8000:8000 -v /idea_engine_surrealdb_data:/idea_engine_surrealdb_data surrealdb/surrealdb:latest start --log trace --auth --user root --pass root file:idea_engine_surrealdb_data/idea_engine_surrealdb.db![image](https://github.com/App-Abacus-Limited/IDEA-Engine-AI/assets/31509362/e2c9ecb0-cfc7-41db-ad67-3474b0950998)
```
