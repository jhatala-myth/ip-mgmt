#!/usr/bin/env sh

# RUN Service
pdns_server \
	--loglevel=${PDNS_LOG_LEVEL:-0} \
	--api=yes \
	--api-key=${PDNS_API_KEY} \
	--local-address="0.0.0.0" \
	--local-port=53 \
	--launch=gmysql \
	--gmysql-host=${PDNS_MYSQL_HOST} \
	--gmysql-port=${PDNS_MYSQL_PORT} \
	--gmysql-dbname=${PDNS_MYSQL_DB} \
	--gmysql-user=${PDNS_MYSQL_USER} \
	--gmysql-password=${PDNS_MYSQL_PASSWORD} \
	--gmysql-dnssec=${PDNS_MYSQL_DNSSEC}
	--webserver=yes \
	--webserver-address=${PDNS_WEBSERVER_IP:-"0.0.0.0"} \
	--webserver-port=${PDNS_WEBSERVER_PORT:-"8081"} \
	--webserver-allow-from=${PDNS_WEBSERVER_ALLOWED_FROM:-"127.0.0.1,::1"} \
	--webserver-password=${PDNS_WEBSERVER_PASSWORD:-""} 
