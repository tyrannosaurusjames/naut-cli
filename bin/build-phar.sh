#!/bin/bash

set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

BASE_DIR=$(realpath "$SCRIPT_DIR/../")
DIST_DIR="$BASE_DIR/dist"
BUILD_DIR="$DIST_DIR/build"

if [ -d "$DIST_DIR" ]; then
    rm -rf "$DIST_DIR"
fi

mkdir $DIST_DIR
mkdir $BUILD_DIR

cp $BASE_DIR/composer.* $BUILD_DIR

composer install -d $BUILD_DIR --no-dev --prefer-dist --no-suggest
cp -R "$BASE_DIR/src" "$BASE_DIR/bin/naut-cli.php" "$BUILD_DIR"

php $BASE_DIR/bin/build-phar.php
chmod +x "$DIST_DIR/naut-cli.phar"

rm -rf $BUILD_DIR
