#!/bin/bash

currentWorkingDir=$(pwd);
startTime=$(date +%s)
folderToConvert=$1
pdfFolder=$(echo $folderToConvert | sed -e 's/htmlTemplate/pdfTemplate/g')
mkdir -p "$pdfFolder"

# Find existing html file at the given directory
cd "$folderToConvert"
htmlFiles="$(find -type f -name '*.html')"

convertBatchScript="/usr/local/bin/wkhtmltopdf -L 0 -B 0 -R 0 -T 0 -q -s A4 --zoom 0.4"
convertBatchScript="$convertBatchScript $htmlFiles $pdfFolder/all.pdf"

# Do generate all html files into one PDF file
$convertBatchScript

endTime=$(date +%s)
echo "Time to process: "$(expr $endTime - $startTime)" sec"
cd "$currentWorkingDir"