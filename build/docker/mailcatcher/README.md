# mailcatcher

Small mailcatcher image

## Usage

```sh
$ docker run -d -p 1080:1080 --name mailcatcher greencape/mailcatcher
```

Link the container to another container and use the mailcatcher SMTP port `1025` via a ENV variable like `$MAILCATCHER_PORT_1025_TCP_ADDR`.
