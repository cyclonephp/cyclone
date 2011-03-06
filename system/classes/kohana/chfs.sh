#/bin/bash
dir=`pwd`
for i in `find . -name '*.php'`; do 
	cat $i | sed 's/class Kohana_//g' > ../$i
done; 
cd $dir
