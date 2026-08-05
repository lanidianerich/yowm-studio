#!/bin/bash
# Builds yowm-studio-<version>.zip from the source folder, ready to upload to WordPress.
cd "$(dirname "$0")"
VERSION=$(grep -m1 "Version:" yowm-studio/yowm-studio.php | sed 's/.*Version:[[:space:]]*//' | tr -d '[:space:]\r')
ZIP="yowm-studio-${VERSION}.zip"
rm -f "$ZIP"
find yowm-studio -name '.DS_Store' -delete 2>/dev/null
zip -rq "$ZIP" yowm-studio
echo "Built $ZIP"
