#!/bin/bash

OPERATION=$1
if [ "$OPERATION" = "" ]; then
  echo "Specify an operation to run"
  exit 1;
fi

scp -P 22123 compare.php webimage@control.corporatewebimage.com:wp-cli/
ssh -p 22123 webimage@control.corporatewebimage.com "scp -P 22123 ~/wp-cli/compare.php 10.0.1.9:~/"
ssh -p 22123 webimage@control.corporatewebimage.com "ssh -p 22123 10.0.1.9 php ~/compare.php $OPERATION"

#rsync --exclude app/repos/ -azve 'ssh -p 22123' ./ webimage@control.corporatewebimage.com:wp-cli/
#ssh -p 22123 webimage@control.corporatewebimage.com "rsync --exclude app/repos/ -azve 'ssh -p 22123' ~/wp-cli 10.0.1.9:~/"
