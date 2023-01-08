FROM alpine:latest

RUN addgroup -g 1001 pdns
RUN adduser  -u 1001 -G pdns -D pdns

# Install PowerDNS & MySQL Backend
RUN apk --update --no-cache add pdns pdns-backend-mysql && \
    rm -rf /var/cache/apk/*

# Create Directory
RUN mkdir /pdns && \
    chmod 755 -R /pdns && \
    chown -R pdns:pdns /pdns

# Copy Configuration
ADD ./entrypoint.sh /

RUN mkdir -p /var/empty/var/run/ && \
    chmod +x /entrypoint.sh

# Ports
EXPOSE 53/udp 53/tcp 8081/tcp

# Service Start
CMD ["/entrypoint.sh"]
