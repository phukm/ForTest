#!/bin/bash

source $HOME/.bash_profile

thisScript=$0
thisDir=$(realpath $(dirname $thisScript))
logFile=$(realpath "$thisDir/..")/data/cronlog/$(basename $thisScript | cut -d'.' -f1).log

cd $thisDir

php index.php call-api send-order-to-econtext >> $logFile 2>&1