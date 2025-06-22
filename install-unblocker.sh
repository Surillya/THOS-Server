#!/bin/bash
set -e

XPI_SOURCE="/usr/thos/thos-unblocker@surillya.com.xpi"

FF_EXT_DIR="/usr/lib/firefox/browser/extensions"

AUTOCONFIG_PREF_DIR="/usr/lib/firefox/defaults/pref"
AUTOCONFIG_FILE="$AUTOCONFIG_PREF_DIR/autoconfig.js"
AUTOCONFIG_SCRIPT="/usr/lib/firefox/thos.cfg"

echo "Installing THOS Unblocker extension..."

if [[ ! -f "$XPI_SOURCE" ]]; then
    echo "ERROR: XPI file not found at $XPI_SOURCE"
    exit 1
fi

sudo mkdir -p "$FF_EXT_DIR"

sudo cp "$XPI_SOURCE" "$FF_EXT_DIR/thos-unblocker@surillya.com.xpi"

echo "Copied extension to $FF_EXT_DIR"

echo "Configuring Firefox to allow unsigned extensions..."

sudo mkdir -p "$AUTOCONFIG_PREF_DIR"

sudo tee "$AUTOCONFIG_SCRIPT" > /dev/null <<EOF
//
lockPref("xpinstall.signatures.required", false);
lockPref("extensions.autoDisableScopes", 0);
lockPref("extensions.enabledScopes", 15);
EOF

sudo tee "$AUTOCONFIG_FILE" > /dev/null <<EOF
pref("general.config.filename", "thos.cfg");
pref("general.config.obscure_value", 0);
EOF

echo "Autoconfig for unsigned extensions set up."

echo "THOS Unblocker installation complete! Restart Firefox to activate."
