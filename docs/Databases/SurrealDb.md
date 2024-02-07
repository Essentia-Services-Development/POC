# Surreal Db


## Running in Docker

Docker can be used to manage and run SurrealDB database instances without the need to install any command-line tools. 
The SurrealDB docker container contains the full command-line tools for importing and exporting data from a running server, or for running a server itself.

### Creating a new container

The script create a new image pulling the latest version

```PowerShell
docker run --gpus all --pull always -p 8000:8000 --name SurrealDb -v /surrealdb:/databases surrealdb/surrealdb:latest start --log trace --auth --user root --pass root file:///databases/ideaenginehub.db
```

## Here's the breakdown of the run command:

The -v option creates a bind mount volume, mapping the host directory /surrealdb to /databases inside the container. Ensure that the host directory exists and has the appropriate permissions.
The database file path is now file:///databases/ideaenginehub.db. This path now correctly points to the /databases directory inside the container, which is mapped to /surrealdb on the host machine.
With this command, when the SurrealDB starts, it should recognize the /databases directory inside the container as the place to store the ideaenginehub.db database file. Since this directory is mapped to the host's /surrealdb directory, the data will persist between container restarts.

Make sure the host's /surrealdb directory is writable by the Docker daemon. If it doesn't exist, you need to create it before running the command, or Docker will create it for you but it might not have the correct permissions if Docker is running as a non-root user.
