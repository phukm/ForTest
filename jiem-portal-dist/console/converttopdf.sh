#!/bin/bash

startTime=$(date +%s)

limitConcurent=10
WAITALL_DELAY=1

waitall() { # PID...
	## Wait for children to exit and indicate whether all exited with 0 status.
	local errors=0
	while :; do
		for pid in "$@"; do
			shift
			if kill -0 "$pid" 2>/dev/null; then
				set -- "$@" "$pid"
			elif wait "$pid"; then
				echo "$pid exited with zero exit status."
			else
				echo "$pid exited with non-zero exit status."
				((++errors))
			fi
		done
		(("$#" > 0)) || break
		# TODO: how to interrupt this sleep when a child terminates?
		sleep ${WAITALL_DELAY:-1}
	done
	((errors == 0))
}


currentWorkingDir=$(pwd);
scriptDir=$(dirname $0);
scriptDir=$(realpath $scriptDir);
folderToConvert=$1

if [ ! -d "$folderToConvert" ]; then
	# There is no class exists
	exit 1;
fi

htmlFiles="$(find $folderToConvert -type f -name '*.html')"
htmlFileCount=$(echo $htmlFiles | wc -w)
filesPerBatch=$(expr $htmlFileCount '/' $limitConcurent '+' 1)

# If class has no pupil
if [ 0 -eq $htmlFileCount ]; then
	# There is no pupil exists
	exit 1;
fi


pids=""
#executeScript='./'$(basename $0 | sed -r 's/process(.*)/\1/g')
convertBatchScript="$scriptDir/converttopdfbatch.sh"
convertAllScript="$scriptDir/mergeall.sh"
convertBatchProcess="$convertBatchScript"
i=0
for htmlFile in $htmlFiles; do
	convertBatchProcess="$convertBatchProcess $htmlFile"
	((++i))

	batchOk=$((i%filesPerBatch))
	if [ 0 -eq $batchOk ] || [ $i -ge $htmlFileCount ] && [ 0 -lt $i ]; then
		/usr/bin/bash $convertBatchProcess &
		pids="$pids $!"
		convertBatchProcess="$convertBatchScript"
	fi
done

# Generate all pupil html files to one PDF file
$convertAllScript "$folderToConvert" &
pids="$pids $!"

waitall $pids

# Clean all HTML files
rm -f $htmlFiles

pdfFolder=$(echo $folderToConvert | sed -e 's/htmlTemplate/pdfTemplate/g')
#cd "$pdfFolder/../../"
#/usr/local/bin/aws s3 sync . "s3://dantai${APP_ENV}"

classId=$(basename $pdfFolder)
scheduleId=$(basename `realpath $pdfFolder/../`)
/usr/local/bin/aws s3 sync $pdfFolder "s3://dantai${APP_ENV}/$scheduleId/$classId"


# Clean generated files
find $folderToConvert -type f -name '*.pdf' -exec rm -f {} \;
#rm -Rf $folderToConvert/*.pdf

endTime=$(date +%s)
echo "Time to process: "$(expr $endTime - $startTime)" sec"
cd "$currentWorkingDir"

exit 0;