<?php

Dict::Add('RU RU', 'Russian', 'Русский', array(
	'Class:UserMultipleLDAP' => 'Пользователь LDAP (несколько серверов)',
	'Class:UserMultipleLDAP+' => 'Пользователь, аутентифицируемый через LDAP',
	'Class:UserMultipleLDAP/Attribute:config_name' => 'Название набора параметров LDAP',
	'Class:UserMultipleLDAP/Attribute:config_name+' => 'Название используемого для аутентификации набора параметров LDAP из файла конфигурации iTop',
	'Class:UserMultipleLDAP/Attribute:config_name/Value:authent-ldap' => 'Использовать стандартный LDAP (%1$s:%2$d)', // %1$s - host, %2$d - port
	'Class:UserMultipleLDAP/Attribute:config_name/Value:config_not_found' => 'Ошибка: набор параметров \'%1$s\' не найден', // %1$s - название конфига
	'Class:UserMultipleLDAP/Attribute:config_name/Value:*' => '%1$s (%2$s:%3$d)', // %1$s - название конфига, %2$s - host, %3$d - port
));