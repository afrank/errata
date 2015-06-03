#!/bin/bash

curl -s "http://localhost:60010/master-status" | grep table.jsp | cut -d= -f3 | cut -d\> -f1 | grep -v "ROOT\|META" | sort -u | while read table; do curl -s "http://localhost:60010/table.jsp?name=$table" | grep -B2 "not deployed" | head -1 | cut -d\> -f2 | cut -d\< -f1; done | while read region; do echo "assign '$region'" | hbase shell; done
