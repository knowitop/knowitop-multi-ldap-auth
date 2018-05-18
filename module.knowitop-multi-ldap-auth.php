<?php

if (function_exists('ldap_connect')) {

    SetupWebPage::AddModule(
        __FILE__, // Path to the current file, all other file names are relative to the directory containing this file
        'knowitop-multi-ldap-auth/1.0.0',
        array(
            // Identification
            //
            'label' => 'User authentication based on multiple LDAP servers',
            'category' => 'authentication',

            // Setup
            //
            'dependencies' => array(
            ),
            'mandatory' => false,
            'visible' => true,

            // Components
            //
            'datamodel' => array(
                'model.knowitop-multi-ldap-auth.php',
            ),
            'data.struct' => array(),
            'data.sample' => array(),

            // Documentation
            //
            'doc.manual_setup' => '',
            'doc.more_information' => '',

            // Default settings
            //
            'settings' => array(
                'debug' => false,
                'ldap_settings' => array(
                    'default' => array(
                        'host' => 'localhost', // host or IP address of your LDAP server
                        'port' => 389,          // LDAP port (std: 389)
                        'default_user' => '', // User and password used for initial "Anonymous" bind to LDAP
                        'default_pwd' => '',  // Leave both blank, if anonymous (read-only) bind is allowed
                        'base_dn' => 'dc=yourcompany,dc=com', // Base DN for User queries, adjust it to your LDAP schema
                        'user_query' => '(&(uid=%1$s)(inetuserstatus=ACTIVE))', // Query used to retrieve each user %1$s => iTop login
                        // For Windows AD use (samaccountname=%1$s) or (userprincipalname=%1$s)

                        // Some extra LDAP options, refer to: http://www.php.net/manual/en/function.ldap-set-option.php for more info
                        'options' => array(
                            LDAP_OPT_PROTOCOL_VERSION => 3,
                            LDAP_OPT_REFERRALS => 0,
                        ),
                        'start_tls' => false,
                    )
                )
            )
        )
    );
}