#/bin/bash
logfile='/tmp/chfs.log'
rm $logfile
for i in `find . -name '*.php'`; do 
	path=${i%/*}
	#echo $path
	if [ -f ../$i ]; then
		cat $i | sed 's/class Kohana_/class /g' > ../$i
		rm $i
		echo "removing $i" >> $logfile;
	else
		echo "skipping $i" >> $logfile
	fi
done;

cat $logfile
