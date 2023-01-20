FROM phpipam/phpipam-www:latest
RUN apk update && apk --no-cache add bash tzdata curl tcpdump && \
    rm -rf /var/cache/apk/*
ENV TZ=UTC

ENTRYPOINT [ "/sbin/tini", "--" ]
CMD [ "/bin/sh", "-c", "/start_apache2" ]
