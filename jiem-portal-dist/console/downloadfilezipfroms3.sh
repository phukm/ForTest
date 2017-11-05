#!/bin/bash
echo "Begin Process At: $(date +%Y-%m-%d:%H:%M:%S)"
START=$(date +%s);
source $HOME/.bash_profile

filename=$1
year=$2
kai=$3
type=$4
ec2DirZipFile="$HOME/zip-upload"
s3DirZipFile="s3://dantai${APP_ENV}/zip-upload"
s3DirDownload_4s5s_credentials="s3://dantai${APP_ENV}/eikenId/${year}/${kai}"
ec2Download_4s5s_credentials="$HOME/zip-upload/eikenId/${year}/${kai}"

if [[ $filename = "" || $year = "" || $kai = "" || $type = "" ]]; then
 echo "Wrong format S3 zip file. Format must be 4s5s_credentials_[YEAR_[KAI].zip";
 exit 0;
fi
if  [[ $type != "4s5s_credentials" ]]; then
  echo "Parameter [Type] must be 4s5s_credentials";
  exit 0;
fi

if ! [[ "$year" =~ ^[0-9]+$ ]]; then
  echo "Parameter [Year] in S3 zip file must be the number";
  exit 0;
fi

if ! [[ "$kai" =~ ^[1-9]+$ ]]; then
  echo "Parameter [Kai] in S3 zip file must be the number And Kai must be greater or equal 1";
  exit 0;
fi

count=`/usr/local/bin/aws s3 ls "$s3DirZipFile/$filename" | wc -l`
if [[ $count -le 0 ]]; then
    echo "Do Not Exist File: $s3DirZipFile/$filename . Please check again"
    exit 0;
fi

mkdir -p $ec2DirZipFile
/usr/local/bin/aws s3 cp $s3DirZipFile/$filename $ec2DirZipFile/$filename

extensionFile=$(file -b --mime-type "$ec2DirZipFile/$filename")
if [[ "$extensionFile" != "application/zip" ]]; then

    find $ec2DirZipFile -type f -name '*' -exec rm -f {} \;
    echo "This File Is Not Zip Format. Please Check Again"
    exit 0;
fi

if [[ $type = "4s5s_credentials" ]]; then
    mkdir -p $ec2Download_4s5s_credentials

    unzip -qq $ec2DirZipFile/$filename -d $ec2Download_4s5s_credentials

    echo "Begin sync directory downloaded file from EC2 to S3"
    /usr/local/bin/aws s3 sync $ec2Download_4s5s_credentials $s3DirDownload_4s5s_credentials

    # Clean all downloaded file at EC2
    find $ec2Download_4s5s_credentials -type f -name '*' -exec rm -f {} \;
    find $ec2DirZipFile -type f -name '*' -exec rm -f {} \;
fi

php index.php save-downloaded-file-s3 $year $kai $type

/usr/local/bin/aws s3 mv s3://dantai${APP_ENV}/zip-upload/${filename} s3://dantai${APP_ENV}/zip-upload-backup/${filename}
END=$(date +%s)
DIFF=$(( $END - $START ))
echo "Execute Time:  $DIFF seconds"

