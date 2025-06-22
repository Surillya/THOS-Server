#!/bin/bash
set -e

# Location inside your repo where the .xpi is stored
XPI_SOURCE="./thos-unblocker@surillya.com.xpi"

# Firefox global extension directory
FF_EXT_DIR="/usr/lib/firefox/browser/extensions"

# Firefox autoconfig files (for signature disable)
AUTOCONFIG_PREF_DIR="/usr/lib/firefox/defaults/pref"
AUTOCONFIG_FILE="$AUTOCONFIG_PREF_DIR/autoconfig.js"
AUTOCONFIG_SCRIPT="/usr/lib/firefox/thos.cfg"

echo "Installing THOS Unblocker extension..."

if [[ ! -f "$XPI_SOURCE" ]]; then
    echo "ERROR: XPI file not found at $XPI_SOURCE"
    exit 1
fi

# Create extensions dir if missing
sudo mkdir -p "$FF_EXT_DIR"

# Copy .xpi file system-wide
sudo cp "$XPI_SOURCE" "$FF_EXT_DIR/thos-unblocker@surillya.com.xpi"

echo "Copied extension to $FF_EXT_DIR"

# Optional: disable signature requirement (dev mode)
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
