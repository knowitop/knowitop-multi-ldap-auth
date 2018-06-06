# Multiple LDAP Server Authentication for Combodo iTop 2.4

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
        'debug' => false,
        'ldap_settings' => array(
            'default' => // <-- Settings defined by default, you are free to change, rename or remove it
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

2. Create a new **Multiple LDAP user** account and specify its **LDAP config name** field with the name of the settings from the previous step (e.g. 'default' or 'other_config').
