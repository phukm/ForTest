#!/bin/bash

source $HOME/.bash_profile

thisScript=$0
thisDir=$(realpath $(dirname $thisScript))
logFile=$(realpath "$thisDir/..")/data/cronlog/$(basename $thisScript | cut -d'.' -f1).log

cd $thisDir

threadCount=$(/usr/bin/pgrep -f "./automapping.sh $APP_ENV" | wc -l)
if [ $threadCount -le 0 ]; then
    /usr/bin/bash ./automapping.sh $APP_ENV >> $logFile 2>&1 &
fi
