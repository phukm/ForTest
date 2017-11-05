#!/bin/bash
thisScript=$0

thisDir=$(realpath $(dirname $thisScript))
runDir=$(realpath "$thisDir/../..")
configPath=$(realpath "$thisDir/..")/phpunit-ci.xml

if [ ! -f "$configPath" ]; then
    cat /dev/null > $configPath

    echo "<phpunit bootstrap=\"./phpunit/bootstrap.php\" colors=\"true\">" >> $configPath 2>&1
    echo "    <testsuites>" >> $configPath  2>&1
    echo "        <testsuite name=\"JIEM DP Test Suite\">" >> $configPath 2>&1
    for DIR in "$runDir"/module/*; do
            if [ -d "$DIR" ]; then
                    if [ -f "$DIR"/tests/phpunit.xml ]; then
    echo "            <directory>$DIR/tests/</directory>" >> $configPath 2>&1
                    fi
            fi
    done
    echo "        </testsuite>" >> $configPath 2>&1
    echo "    </testsuites>" >> $configPath 2>&1
    echo "</phpunit>" >> $configPath 2>&1
fi

cd $runDir
./phpunit -c $configPath
