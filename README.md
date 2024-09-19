# ip-mgmt

IP management with phpIPAM + PowerDNS + registration of DHCP client names in PowerDNS (with MySQL backend)

![flow](/ip_mgmt.drawio.png)#gh-light-mode-only

```bash
DOCKER_BUILDKIT=1 docker image build --no-cache  --network host --force-rm -t $(basename $(pwd) | tr '[:upper:]' '[:lower:]') -f Dockerfile .
```
