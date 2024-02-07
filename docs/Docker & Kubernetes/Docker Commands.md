Certainly! Below is a comprehensive tutorial written in Markdown (.md) format, detailing the Docker `run` command parameters. This guide is designed to help both beginners and advanced users understand how to use the `run` command effectively in Docker.

---

# Docker Run Command: A Comprehensive Guide

The Docker `run` command is one of the most fundamental commands in Docker, used to start a new container from an image. It comes with a plethora of options that allow you to specify how the container should operate. This guide covers all the Docker `run` command parameters, explaining each one's purpose and usage.

## Syntax

```sh
docker run [OPTIONS] IMAGE [COMMAND] [ARG...]
```

- **IMAGE** specifies the Docker image to use for the container.
- **COMMAND** and **ARG** allow you to override the default command and arguments defined in the image.

## Common Options

### `-d`, `--detach`

Run the container in detached mode (in the background).

### `--name`

Assign a name to the container.

### `-p`, `--publish`

Publish a container's port(s) to the host.

Format: `[hostPort:]containerPort`

### `-v`, `--volume`

Mount a volume. Can be used to persist data.

Format: `[hostSrc:]containerDest`

### `--rm`

Automatically remove the container when it exits.

### `-e`, `--env`

Set an environment variable. Can be used multiple times.

Format: `KEY=value`

### `--env-file`

Read in a file of environment variables.

### `-it`

Run container in interactive mode with a tty.

Equivalent to `--interactive --tty`.

## Network Settings

### `--network`

Connect the container to a specific network.

### `--link`

Add link to another container (deprecated in favor of `--network`).

### `--hostname`

Set the container's hostname.

## Resource Management

### `--memory`, `-m`

Limit memory usage.

Format: `[number][unit]` (e.g., `500m`, `2g`)

### `--cpu-shares`, `--cpu-quota`, `--cpus`

Limit CPU usage.

## Advanced Options

### `--entrypoint`

Override the default entrypoint of the image.

### `--user`, `-u`

Set the username or UID to use when running the container.

### `--read-only`

Mount the container's root filesystem as read only.

### `--restart`

Set the restart policy for the container.

Possible values: `no`, `on-failure`, `always`, `unless-stopped`

### `--privileged`

Give extended privileges to the container.

### `--device`

Add a host device to the container.

### `--label`

Add metadata to the container.

Format: `key=value`

## Example Usage

```sh
docker run -d --name my_container -p 80:80 --restart always my_image
```

This command runs a container named `my_container` from the `my_image` image in detached mode, maps port 80 inside the container to port 80 on the host, and sets the container to always restart unless it is explicitly stopped.

## Conclusion

The Docker `run` command is incredibly versatile, offering numerous parameters to customize the runtime environment of your containers. Understanding these options allows you to leverage Docker's full potential in your development and deployment workflows.

---

This guide aims to provide a thorough understanding of the Docker `run` command's parameters. For more detailed information and advanced usage examples, refer to the Docker official documentation.
