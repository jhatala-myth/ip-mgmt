FROM alpine:latest

RUN addgroup -g 1001 recursor
RUN adduser  -u 1001 -G recursor -D recursor

# Install PowerDNS
RUN apk --update --no-cache add bash tzdata curl tcpdump pdns-recursor && \
    rm -rf /var/cache/apk/*
ENV TZ=UTC
RUN mkdir -p /etc/pdns/api.d \
  && chown -R recursor: /etc/pdns/api.d \
  && mkdir -p /var/run/pdns-recursor \
  && chown -R recursor: /var/run/pdns-recursor

ADD entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh 

EXPOSE 53/tcp 53/udp 8081
CMD [ "/entrypoint.sh" ]
