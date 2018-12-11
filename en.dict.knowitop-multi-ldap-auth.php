<?php

Dict::Add('EN US', 'English', 'English', array(
	'Class:UserMultipleLDAP' => 'Multiple LDAP user',
	'Class:UserMultipleLDAP+' => 'User authenticated by LDAP',
	'Class:UserMultipleLDAP/Attribute:config_name' => 'LDAP config name',
	'Class:UserMultipleLDAP/Attribute:config_name+' => 'LDAP config name from iTop config file',
	'Class:UserMultipleLDAP/Attribute:config_name/Value:authent-ldap' => 'Use standard LDAP (%1$s:%2$d)', // %1$s - host, %2$d - port
	'Class:UserMultipleLDAP/Attribute:config_name/Value:config_not_found' => 'Error: not found \'%1$s\' LDAP config', // %1$s - config name
	'Class:UserMultipleLDAP/Attribute:config_name/Value:*' => '%1$s (%2$s:%3$d)', // %1$s - config name, %2$s - host, %3$d - port
));
