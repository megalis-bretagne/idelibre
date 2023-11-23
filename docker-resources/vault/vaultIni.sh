#!/bin/bash

set -m

echo "Staring Vault..."
vault server -config=/vault/config/vault.json &



until vault --version
do
  echo "Try again"
  sleep 5
done


echo "Unseal"
vault operator unseal "${VAULT_UNSEAL_KEY_1}"
fg