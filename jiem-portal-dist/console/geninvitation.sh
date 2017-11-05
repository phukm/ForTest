#!/bin/bash

thisScript=$0
thisDir=$(realpath $(dirname $thisScript))
logFile=$(realpath "$thisDir/..")/data/cronlog/$(basename $thisScript | cut -d'.' -f1).log

cd $thisDir

# In call times
idleThreshold=2
# In number
maxThreads=5
increaseThread(){
    # Number of current threads since now
    currentThreads=$(/usr/bin/pgrep -f "$thisScript $APP_ENV" | wc -l)
    if [ $currentThreads -lt $maxThreads ]; then
        /usr/bin/bash $thisScript $APP_ENV >> $logFile 2>&1 &
        echo 'New Thread increased: '$!
    fi
    return 0
}

idleCount=0
# Infinite loop and monitor then
while true ; do
    php index.php cli generate-invitation-letter
    returnCode=$?
    
    # Try to increase threads when see current thread is busy
    if [ $returnCode -ne 1 ]; then
        increaseThread
    else
        if [ $idleCount -lt $idleThreshold ]; then
            ((++idleCount))
        else
            # Break current loop and end this thread
            break;
        fi
    fi
    
done;
