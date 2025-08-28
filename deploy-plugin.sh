#!/bin/bash

# Konfiguration
SERVER="ftp.sclgvca5959.universe.wf"
USER="germantax@germanroadtax.sclgvca5959.universe.wf"
PASS="dein-passwort"
REMOTE_PATH="/home2/sc1gvca5959/germanroadtax.sc1gvca5959.universe.wf/wp-content/plugins"
LOCAL_PATH="/Users/mano/Desktop/kfz-tax-calculator-version-1.5"

# Plugin auf den Server kopieren (rekursiv) mit lftp
lftp -u "$USER","$PASS" $SERVER <<EOF
mirror -R --delete --verbose $LOCAL_PATH $REMOTE_PATH
EOF

echo "Plugin wurde per FTP (lftp) hochgeladen!"