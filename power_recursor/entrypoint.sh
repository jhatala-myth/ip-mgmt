#!/usr/bin/env sh

chown recursor: /etc/pdns/recursor.conf

# RUN Service
pdns_recursor \
	--loglevel=${PDNS_LOG_LEVEL:-0} \
	--api-key=${PDNS_API_KEY} \
	--local-address="0.0.0.0" \
	--local-port=53 \
	--webserver=yes \
	--webserver-address=${RECURSOR_WEB_IP:-"0.0.0.0"} \
	--webserver-port=${RECURSOR_WEB_PORT:-"8082"} \
	--webserver-allow-from=${PDNS_WEBSERVER_ALLOWED_FROM:-"127.0.0.1,::1"} \
	--webserver-password=${PDNS_WEBSERVER_PASSWORD:-""} 
