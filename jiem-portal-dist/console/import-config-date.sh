#!/bin/bash

thisScript=$0
thisDir=$(realpath $(dirname $thisScript))
logFile=$(realpath "$thisDir/..")/data/cronlog/$(basename $thisScript | cut -d'.' -f1).log
fileName=$1

cd $thisDir

php index.php import-config-date $fileName | tee -a $logFile
