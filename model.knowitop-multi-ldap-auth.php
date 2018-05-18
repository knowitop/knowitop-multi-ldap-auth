<?php

/**
 * Multiple LDAP Authentication (created from standard authent-ldap module)
 *
 * @author      Vladimir Kunin https://community.itop-itsm.ru
 * @license     http://opensource.org/licenses/AGPL-3.0
 */
class UserMultipleLDAP extends UserInternal
{
    public static function Init()
    {
        $aParams = array
        (
            "category" => "addon/authentication",
            "key_type" => "autoincrement",
            "name_attcode" => "login",
            "state_attcode" => "",
            "reconc_keys" => array('login'),
            "db_table" => "priv_usermultipleldap",
            "db_key_field" => "id",
            "db_finalclass_field" => "",
            "display_template" => "",
        );
        MetaModel::Init_Params($aParams);
        MetaModel::Init_InheritAttributes();
        MetaModel::Init_AddAttribute(new AttributeString("config_name", array("allowed_values" => null, "sql" => 'config_name', "default_value" => 'default', "is_null_allowed" => false, "depends_on" => array(), "always_load_in_tables" => false)));
        MetaModel::Init_SetZListItems('details', array('contactid', 'first_name', 'email', 'login', 'language', 'status', 'config_name', 'profile_list', 'allowed_org_list'));
        MetaModel::Init_SetZListItems('list', array('first_name', 'last_name', 'login', 'status'));
        MetaModel::Init_SetZListItems('standard_search', array('login', 'contactid', 'status'));
        MetaModel::Init_SetZListItems('advanced_search', array('login', 'contactid'));
    }

    /**
     * @param string $sPassword The user's password to validate against the LDAP server
     * @return boolean True if the password is Ok, false otherwise
     * @throws ArchivedObjectException
     * @throws CoreException
     */
    public function CheckCredentials($sPassword)
    {
        if (!function_exists('ldap_connect')) {
            throw new CoreException("'ldap_connect' function doesn't exist. Check that php-ldap extension is installed.");
        }
            $aConfigs = MetaModel::GetModuleSetting('knowitop-multi-ldap-auth', 'ldap_settings', array());
        if (empty($aConfigs)) {
            $this->LogMessage("can not found 'ldap_settings' directive. Check the configuration file config-itop.php.");
            return false;
        }
        $sUserConfigName = $this->Get('config_name');
        $aLDAPConfig = isset($aConfigs[$sUserConfigName]) ? $aConfigs[$sUserConfigName] : array();
        if (empty($aLDAPConfig)) {
            $this->LogMessage("can not found LDAP settings with name '$sUserConfigName' for user '" . $this->Get('login') . "'. Check the configuration file config-itop.php.");
            return false;
        } else {
            $sLDAPHost = $aLDAPConfig['host'] ?: 'localhost';
            $iLDAPPort = $aLDAPConfig['port'] ?: 389;

            $sDefaultLDAPUser = $aLDAPConfig['default_user'] ?: '';
            $sDefaultLDAPPwd = $aLDAPConfig['default_pwd'] ?: '';
            $bLDAPStartTLS = $aLDAPConfig['start_tls'] == true;

            $aOptions = $aLDAPConfig['options'] ?: array();
        }

        if (array_key_exists(LDAP_OPT_DEBUG_LEVEL, $aOptions)) {
            // Set debug level before trying to connect, so that debug info appear in the PHP error log if ldap_connect goes wrong
            $bRet = ldap_set_option($hDS, LDAP_OPT_DEBUG_LEVEL, $aOptions[LDAP_OPT_DEBUG_LEVEL]);
            $this->LogMessage("ldap_set_option('$name', '$value') returned " . ($bRet ? 'true' : 'false'));
        }
        $hDS = @ldap_connect($sLDAPHost, $iLDAPPort);
        if ($hDS === false) {
            $this->LogMessage("can not connect to the LDAP server '$sLDAPHost' (port: $iLDAPPort). Check the configuration '$sUserConfigName' in file config-itop.php.");
            return false;
        }
        foreach ($aOptions as $name => $value) {
            $bRet = ldap_set_option($hDS, $name, $value);
            $this->LogMessage("ldap_set_option('$name', '$value') returned " . ($bRet ? 'true' : 'false'));
        }
        if ($bLDAPStartTLS) {
            $this->LogMessage("ldap_authentication: start tls required.");
            $hStartTLS = ldap_start_tls($hDS);
            //$this->LogMessage("ldap_authentication: hStartTLS = '$hStartTLS'");
            if (!$hStartTLS) {
                $this->LogMessage("ldap_authentication: start tls failed.");
                return false;
            }
        }

        if ($bind = @ldap_bind($hDS, $sDefaultLDAPUser, $sDefaultLDAPPwd)) {
            // Search for the person, using the specified query expression
            $sLDAPUserQuery = $aLDAPConfig['user_query'] ?: '';
            $sBaseDN = $aLDAPConfig['base_dn'] ?: '';

            $sLogin = $this->Get('login');
            $iContactId = $this->Get('contactid');
            $sFirstName = '';
            $sLastName = '';
            $sEMail = '';
            if ($iContactId > 0) {
                $oPerson = MetaModel::GetObject('Person', $iContactId);
                if (is_object($oPerson)) {
                    $sFirstName = $oPerson->Get('first_name');
                    $sLastName = $oPerson->Get('name');
                    $sEMail = $oPerson->Get('email');
                }
            }
            // %1$s => login
            // %2$s => first name
            // %3$s => last name
            // %4$s => email
            $sQuery = sprintf($sLDAPUserQuery, $sLogin, $sFirstName, $sLastName, $sEMail);
            $hSearchResult = @ldap_search($hDS, $sBaseDN, $sQuery);

            $iCountEntries = ($hSearchResult !== false) ? @ldap_count_entries($hDS, $hSearchResult) : 0;
            switch ($iCountEntries) {
                case 1:
                    // Exactly one entry found, let's check the password by trying to bind with this user
                    $aEntry = ldap_get_entries($hDS, $hSearchResult);
                    $sUserDN = $aEntry[0]['dn'];
                    $bUserBind = @ldap_bind($hDS, $sUserDN, $sPassword);
                    if (($bUserBind !== false) && !empty($sPassword)) {
                        ldap_unbind($hDS);
                        return true; // Password Ok
                    }
                    $this->LogMessage("wrong password for user: '$sUserDN'.");
                    return false; // Wrong password
                    break;

                case 0:
                    // User not found...
                    $this->LogMessage("no entry found with the query '$sQuery', base_dn = '$sBaseDN'. User not found in LDAP.");
                    break;

                default:
                    // More than one entry... maybe the query is not specific enough...
                    $this->LogMessage("several (" . ldap_count_entries($hDS, $hSearchResult) . ") entries match the query '$sQuery', base_dn = '$sBaseDN', check that the query defined in '$sUserConfigName' ldap config in config-itop.php is specific enough.");
            }
            return false;
        } else {
            // Trace: invalid default user for LDAP initial binding
            $this->LogMessage("can not bind to the LDAP server '$sLDAPHost' (port: $iLDAPPort), user='$sDefaultLDAPUser', pwd='$sDefaultLDAPPwd'. Error: '" . ldap_error($hDS) . "'. Check the '$sUserConfigName' ldap configuration file config-itop.php.");
            return false;
        }
    }

    public function TrustWebServerContext()
    {
        return false;
    }

    public function CanChangePassword()
    {
        return false;
    }

    public function ChangePassword($sOldPassword, $sNewPassword)
    {
        return false;
    }

    protected function LogMessage($sMessage, $aData = array())
    {
        if (MetaModel::GetModuleSetting('knowitop-multi-ldap-auth', 'debug', false) && MetaModel::IsLogEnabledIssue()) {
            if (MetaModel::IsValidClass('EventIssue')) {
                $oLog = new EventIssue();

                $oLog->Set('message', $sMessage);
                $oLog->Set('userinfo', '');
                $oLog->Set('issue', 'Multiple LDAP Authentication');
                $oLog->Set('impact', 'User login rejected');
                $oLog->Set('data', $aData);
                $oLog->DBInsertNoReload();
            }

            IssueLog::Error('multiple_ldap_authentication: ' . $sMessage);
        }
    }
}
