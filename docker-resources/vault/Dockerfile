FROM hashicorp/vault:1.15.2



ENV VAULT_ADDR='http://127.0.0.1:8200'

ADD ./config /vault/config
ADD ./policies /vault/policies

RUN apk add bash

ADD vaultIni.sh /

RUN chmod 755 vaultIni.sh


ENTRYPOINT  /bin/bash vaultIni.sh

