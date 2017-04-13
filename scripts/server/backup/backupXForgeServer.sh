#!/bin/bash

TODAY=$(date +"%Y-%m-%d")
BACKUPDIR=/backup

echo -n "Dumping Mongo Database..."
MONGOBACKUPFILE="$BACKUPDIR/mongodb_backup.tgz"
MONGOOUTPUTDIR="$BACKUPDIR/mongo_backup"
/usr/bin/time -f "(finished in %E)" mongodump --quiet -o $MONGOOUTPUTDIR
/usr/bin/time -f "tar finished in %E" tar -czf $MONGOBACKUPFILE $MONGOOUTPUTDIR
rm -r $MONGOOUTPUTDIR

echo -n "Preparing SF Assets..."
SFASSETSBACKUPFILE="$BACKUPDIR/sf_assets_backup.tgz"
/usr/bin/time -f "(finished in %E)" tar -czf $SFASSETSBACKUPFILE /var/www/scriptureforge.org/htdocs/assets

echo -n "Preparing LF Assets..."
LFASSETSBACKUPFILE="$BACKUPDIR/lf_assets_backup.tgz"
/usr/bin/time -f "(finished in %E)" tar -czf $LFASSETSBACKUPFILE /var/www/languageforge.org/htdocs/assets

echo "Mongo:" `cat $MONGOBACKUPFILE |gunzip|tar -t|awk -F/ '{print $3;}'|sort|uniq|wc|awk '{print $1;}'` "databases backed up in archive $MONGOBACKUPFILE"
echo "SF Assets:" `cat $SFASSETSBACKUPFILE |gunzip|tar -t|awk -F/ '{print $7}'|sort|uniq|wc|awk '{print $1}'` "project's assets backed up in archive $SFASSETSBACKUPFILE"
echo "LF Assets:" `cat $LFASSETSBACKUPFILE |gunzip|tar -t|awk -F/ '{print $7}'|sort|uniq|wc|awk '{print $1}'` "project's assets backed up in archive $LFASSETSBACKUPFILE"

