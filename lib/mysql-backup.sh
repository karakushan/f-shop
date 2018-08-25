#!/bin/bash
cd /home/vkarakushan/site-backups/mysql
rm ./sql-dump-3.gz
mv sql-dump-2.gz sql-dump-3.gz
mv sql-dump-1.gz sql-dump-2.gz
mv sql-dump-0.gz sql-dump-1.gz
mysqldump -ugrand-admin -pjeremy1519 grand-present | gzip > /home/vkarakushan/site-backups/mysql/sql-dump-0.gz
