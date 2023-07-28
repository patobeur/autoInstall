#Auto installer php

This will autoinstall itself.



## process

- 1 index.php call Page.php
- 1.1 Page.php check existing config

	if (!file_exists('./conf/config.php') && file_exists('./php/Installer.php')) require('./php/Installer.php');

- 1.2 Installer.php send a form asking for db datas and first user acount datas.

- 1.3 if form datas are ok then send process installation
- 2.0 Check if server connection ok ! 
  - back to form if not !
- 2.1 Check if 'tools' db existe and connection ok ! 
  - CREATE 'tools' database if not !
- 2.2 Check if table 'toolsUsers' existe ! 
  - CREATE 'toolsUsers' table if not !
- 2.3 INSERT a user row in table 'toolsUsers'

## Todo 
- [ ] finish Process ReadMe
- [ ] ...
- [ ] ...
- [ ] chmod test
- [ ] ...
- [ ] ...
- [ ] check injection
- [ ] ...
- [ ] ...
- [ ] make it cute !
- [ ] ...
- [ ] ...
- [ ] Check security
- [ ] ...
- [ ] ...
- [ ] ...
- [ ] ...
- [ ] ...
- [ ] make it friendly user !
- [ ] ...
- [ ] but check security twice first !
- [ ] 
