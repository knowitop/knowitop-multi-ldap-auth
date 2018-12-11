# Multiple LDAP Server Authentication for Combodo iTop

This module allows you to define multiple LDAP servers for user authentication.

## Installation

Install like any other extension.

- If you have ZIP downloaded extract and rename folder "knowitop-multi-ldap-auth-master" to "knowitop-multi-ldap-auth".
- Copy "knowitop-multi-ldap-auth" folder to itop/extensions folder and go to http://localhost/setup/.
- Select "Upgrade an existing iTop instance" and follow the wizard.

## Configuration

1. Define the LDAP settings in the iTop configuration file.

```php

// other params

$MyModuleSettings = array(

    // other params
    
    'knowitop-multi-ldap-auth' => array(
        'ldap_settings' => array(
            'your_ldap_config_name' => // <-- Settings defined by default, you are free to change, rename or remove it
                array(
                    'host' => 'localhost',
                    'port' => 389,
                    'default_user' => '',
                    'default_pwd' => '',
                    'base_dn' => 'dc=yourcompany,dc=com',
                    'user_query' => '(&(uid=%1$s)(inetuserstatus=ACTIVE))',
                    'options' =>
                        array(
                            17 => 3,
                            8 => 0,
                        ),
                    'start_tls' => false,
                ),
            'other_config' => // <-- Add your second LDAP settings named 'other_config' for example
                array(
                    'host' => 'my-ldap-host',
                    'port' => 10389,
                    'default_user' => '',
                    'default_pwd' => '',
                    'base_dn' => 'dc=yourcompany,dc=com',
                    'user_query' => '(&(uid=%1$s)(inetuserstatus=ACTIVE))',
                    'options' =>
                        array(
                            17 => 3,
                            8 => 0,
                        ),
                    'start_tls' => false,
                ),
              // <-- Add your third and others LDAP settings here if needed
        ),
    ),
);

// other params
```

2. Create a new **Multiple LDAP user** account and select its **LDAP config name** from the settings defined in the previous step (e.g. 'your_ldap_config_name' or 'other_config') or use LDAP settings from the standard authent-ldap module.

Note: use the `debug` directive from the standard authent-ldap module to debug this module.

## Links
- [iTop ITSM & CMDB Russian community](http://community.itop-itsm.ru)
- [Combodo](https://www.combodo.com/?lang=en)
