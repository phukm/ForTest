#!/bin/bash

source $HOME/.bash_profile

thisScript=$0
thisDir=$(realpath $(dirname $thisScript))

cd $thisDir

php index.php cli receive-payment-from-econtext