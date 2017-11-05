#!/bin/bash

thisScript=$0
thisDir=$(realpath $(dirname $thisScript))

appEnv=$APP_ENV
if [ "" == "$appEnv" ]; then
	appEnv='production';
fi

svrFunc=$SVR_FUNC
if [ "" == "$svrFunc" ]; then
    svrFunc='worker';
fi

deployFile="$thisDir/jiem-portal-dist.tar.gz"


if [ "$svrFunc" == "worker" ]; then

    echo '[info]    Deploying worker package....'
    deployDir="$HOME/source${appEnv}/"
    mkdir -p "$deployDir"

    cd "$deployDir"
    tar xzf "$deployFile"
    mkdir -p data/DoctrineORMModule/Proxy
    rm -f data/cache/config/*
    
    chmod +x console/*.sh
    
    kill -9 $(/usr/bin/pgrep -fu $USER ./geninvitation.sh)
    kill -9 $(/usr/bin/pgrep -fu $USER ./sendcombinifiles.sh)
    
    $deployDir/doctrine-module orm:clear-cache:metadata > /dev/null
    $deployDir/doctrine-module orm:clear-cache:query > /dev/null
    $deployDir/doctrine-module orm:clear-cache:result > /dev/null
    $deployDir/doctrine-module orm:schema-tool:update --force
    
    echo '[notice]  Please remeber to dump master data to database each time of deploy process'
    echo '          mysql jiemdp'${appEnv}' -f < '$deployDir'/data/patches/*/*.sql'

else

    echo '[info]    Deploying webapp package....'
    deployDir="/var/www/dantai${appEnv}/"
    sudo mkdir -p "$deployDir"

    cd "$deployDir"
    sudo tar xzf "$deployFile"
    sudo mkdir -p data/DoctrineORMModule/Proxy
    sudo chown -Rf root:root *
    sudo chown -Rf apache:apache data
    sudo chcon -R -t public_content_rw_t data/
    sudo rm -f data/cache/config/*
fi

echo '[info]    Deploy package done!'