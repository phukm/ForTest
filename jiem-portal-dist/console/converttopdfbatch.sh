#!/bin/bash

for htmlFile; do
	pdfFile="$(echo $htmlFile | sed -e 's/\.html$/.pdf/g' | sed -e 's/htmlTemplate/pdfTemplate/g')"
	pdfDir=$(dirname $pdfFile)
	mkdir -p $pdfDir
	/usr/local/bin/wkhtmltopdf -L 0 -B 0 -R 0 -T 0 -q --zoom 0.4 -s A4 $htmlFile $pdfFile
done
