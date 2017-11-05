#!/bin/bash

source $HOME/.bash_profile

thisScript=$0
thisDir=$(realpath $(dirname $thisScript))
logFile=$(realpath "$thisDir/..")/data/cronlog/$(basename $thisScript | cut -d'.' -f1).log

cd $thisDir

queueUrl=`echo $(/usr/local/bin/aws sqs get-queue-url --queue-name DantaiNotifyPutS3${APP_ENV}) | sed 's@^\(.*"\)\(http[^"]*\)\(".*\)$@\2@g'`
echo "QueueUrl: ${queueUrl}"
sqsContent=`echo $(/usr/local/bin/aws sqs receive-message --queue-url ${queueUrl} --output=json)`
#echo ${sqsContent}

filename=`echo ${sqsContent} | sed 's@^\(.*zip-upload/\)\(.*zip\)\(.*\)$@\2@g'`
receiptHandle=`echo ${sqsContent} | sed 's@^\(.*"ReceiptHandle": "\)\(.*\)\(", "MD5OfBody":.*\)$@\2@g'`
#echo $receiptHandle;
if [[ $filename = "" || $receiptHandle = "" ]]; then
 echo "Empty zip file: $(date +%Y-%m-%d:%H:%M:%S)"
 exit 0;
fi

echo "File: ${filename}"
#echo ${receiptHandle}

type=`echo ${filename} | sed 's@^\(.*\)_\(.*\)_\(.*\)_\(.*\)\(.zip\)$@\1_\2@g'`
echo "Type: ${type}"
year=`echo ${filename} | sed 's@^\(.*\)_\(.*\)_\(.*\)_\(.*\)\(.zip\)$@\3@g'`
echo "Year: ${year}"
kai=`echo ${filename} | sed 's@^\(.*\)_\(.*\)_\(.*\)_\(.*\)\(.zip\)$@\4@g'`
echo "Kai: ${kai}"

/usr/local/bin/aws sqs delete-message --queue-url ${queueUrl} --receipt-handle ${receiptHandle}

/usr/bin/bash ./downloadfilezipfroms3.sh $filename $year $kai $type | tee -a $logFile


