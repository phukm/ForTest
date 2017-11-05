#!/bin/bash

thisScript=$0
thisDir=$(realpath $(dirname $thisScript))
logFile=$(realpath "$thisDir/..")/data/cronlog/$(basename $thisScript | cut -d'.' -f1).log
year=$1
kai=$2

cd $thisDir

php index.php eiken dummy-eiken $year $kai | tee -a $logFile
