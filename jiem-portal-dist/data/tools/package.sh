#!/bin/bash

# TODO: Implementation of exporting from Git

thisScript=$0
thisDir=$(realpath $(dirname $thisScript))
distDir=$(realpath "$thisDir/../../../dist/")
buildDir=$(realpath "$distDir/../build/");

echo '[info]    Clean up'
rm -Rf "${buildDir}"

if [ ! -d "$distDir" ]; then
    mkdir -p "$distDir"
fi

# Packaging source
#git archive --format=tar HEAD | gzip > "$distDir/jiem-portal-dist.tar.gz"

git checkout-index --prefix="${buildDir}/" -af

cd "${buildDir}"

# Execute code review scripts
# This will create output log files for code review report purpose
echo "[info] Executing code review scripts"
# ./phpmd
# ./phpcs
# ./phpunit

echo "[info]    Creating package file"
tar czf "$distDir/jiem-portal-dist.tar.gz" *
echo "[info]    Package created at '$distDir/jiem-portal-dist.tar.gz'"

cp "$thisDir/deploy.sh" "$distDir/deploy.sh"
chmod +x "$distDir/deploy.sh"