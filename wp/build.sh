#!/bin/sh
# Builds dist/myconsult.zip: the theme PHP/JSON in wp/ plus the prototype's css/, js/ and assets/.
set -e
cd "$(dirname "$0")/.."
tmp=$(mktemp -d) && mkdir "$tmp/myconsult"
rsync -a --exclude build.sh wp/ "$tmp/myconsult/"
rsync -a css js "$tmp/myconsult/"
rsync -a --exclude myconsult-orb-untextured.glb assets "$tmp/myconsult/"
mkdir -p dist && rm -f dist/myconsult.zip
(cd "$tmp" && zip -qr - myconsult) > dist/myconsult.zip
rm -rf "$tmp"
echo "dist/myconsult.zip ($(du -h dist/myconsult.zip | cut -f1))"
