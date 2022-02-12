<h1>Migration</h1>

- [Upgrade to version 2022020600 or newer](#upgrade-to-version-2022020600-or-newer)

## Upgrade to version 2022020600 or newer

The following steps must be performed before upgrading:

Because the format of the graderID changed, the old graderID's in the database must be converted to the new format. 

To do this, there is a block in the db/upgrade.php file in which this can be done.
This block is marked by: ```-- 2022020600 Migration Code```

Migration is performed by adding entries to the $migrationSQL array which is located in the previously mentioned block.

The format of an entry looks like: ```"SET gradername" = '<graderName>', graderversion = '<graderVersion>' WHERE graderid = '<oldGraderId>'"```
- ```<oldGraderId>``` is the old GraderID that should be converted
- ```<graderName>``` is the name of the grader, this name is part of the new graderID
- ```<graderVersion>``` is the version of the grader, this is also part of the new graderID

Add an entry for every grader that should be migrated and change the values enclosed by <> to the specific values (and remove all "<" and ">"). 
Several entries are separated by ","

