<?php

/**
 * Multiple LDAP Authentication (based on standard authent-ldap module)
 *
 * @author      Vladimir Kunin https://community.itop-itsm.ru
 * @license     http://opensource.org/licenses/AGPL-3.0
 */
class UserMultipleLDAP extends UserLDAP
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
		MetaModel::Init_AddAttribute(new AttributeLDAPConfigSelect("config_name", array(
			"allowed_values" => null,
			"sql" => 'config_name',
			"default_value" => 'authent-ldap',
			"is_null_allowed" => false,
			"depends_on" => array(),
			"always_load_in_tables" => false
		)));
		MetaModel::Init_SetZListItems('details', array(
			'contactid',
			'first_name',
			'email',
			'login',
			'language',
			'status',
			'config_name',
			'profile_list',
			'allowed_org_list'
		));
		// MetaModel::Init_SetZListItems('list', array('first_name', 'last_name', 'login', 'status'));
		// MetaModel::Init_SetZListItems('standard_search', array('login', 'contactid', 'status'));
		// MetaModel::Init_SetZListItems('advanced_search', array('login', 'contactid'));
	}

	/**
	 * @param string $sPassword The user's password to validate against the LDAP server
	 *
	 * @return boolean True if the password is Ok, false otherwise
	 * @throws CoreException
	 */
	public function CheckCredentials($sPassword)
	{
		if (!function_exists('ldap_connect'))
		{
			throw new CoreException("'ldap_connect' function doesn't exist. Check that php-ldap extension is installed.");
		}
		$sUserConfigName = $this->Get('config_name');
		if ($sUserConfigName && $sUserConfigName !== 'authent-ldap')
		{
			$aConfigs = self::GetLDAPConfigs();
			if (empty($aConfigs))
			{
				$this->LogMessage("can not found 'ldap_settings' directive. Check the configuration file config-itop.php.");

				return false;
			}
			$aLDAPConfig = isset($aConfigs[$sUserConfigName]) ? $aConfigs[$sUserConfigName] : array();
			if (empty($aLDAPConfig))
			{
				$this->LogMessage("can not found LDAP settings with name '$sUserConfigName' for user '".$this->Get('login')."'. Check the configuration file config-itop.php.");

				return false;
			}
			// NOTE: Rewrite standard LDAP config for the user authentication
			// Is this can affect other users in some way??
			$oAppConfig = MetaModel::GetConfig();
			foreach ($aLDAPConfig as $sKey => $value)
			{
				$oAppConfig->SetModuleSetting('authent-ldap', $sKey, $value);
			}
		}

		return parent::CheckCredentials($sPassword);
	}

	protected function LogMessage($sMessage, $aData = array())
	{
		parent::LogMessage('multiple_ldap_authentication: '.$sMessage, $aData);
	}

	static public function GetLDAPConfigs()
	{
		$aConfigs = MetaModel::GetModuleSetting('knowitop-multi-ldap-auth', 'ldap_settings', array());
		$aDefaults = [
			'host' => 'localhost',
			'port' => 389,
			'default_user' => '',
			'default_pwd' => '',
			'user_query' => '',
			'base_dn' => '',
			'start_tls' => true,
			'options' => []
		];
		array_walk($aConfigs, function (&$aConfig) use ($aDefaults) {
			return array_merge($aDefaults, $aConfig);
		});

		return $aConfigs;
	}
}


class AttributeLDAPConfigSelect extends AttributeEnum
{

	public function GetAllowedValues($aArgs = array(), $sContains = '')
	{
		$aLDAPConfigs = UserMultipleLDAP::GetLDAPConfigs();
		$aAllowedValues = [];
		foreach (array_keys($aLDAPConfigs) as $sKey)
		{
			$aAllowedValues[$sKey] = $this->GetValueLabel($sKey);
		}
		$aAllowedValues['authent-ldap'] = $this->GetValueLabel('authent-ldap');

		return $aAllowedValues;
	}

	public function GetValueLabel($sValue)
	{
		if ($sValue === 'authent-ldap')
		{
			$sStandardHost = MetaModel::GetModuleSetting('authent-ldap', 'host', 'localhost');
			$iStandardPort = MetaModel::GetModuleSetting('authent-ldap', 'port', 389);

			return Dict::Format('Class:'.$this->GetHostClass().'/Attribute:'.$this->GetCode().'/Value:'.$sValue,
				$sStandardHost, $iStandardPort);
		}
		elseif (!is_null($sValue))
		{
			$aLDAPConfigs = UserMultipleLDAP::GetLDAPConfigs();
			if (isset($aLDAPConfigs[$sValue]))
			{
				return Dict::Format('Class:'.$this->GetHostClass().'/Attribute:'.$this->GetCode().'/Value:*', $sValue,
					$aLDAPConfigs[$sValue]['host'], $aLDAPConfigs[$sValue]['port']);
			}
			else
			{
				return Dict::Format('Class:'.$this->GetHostClass().'/Attribute:'.$this->GetCode().'/Value:config_not_found',
					$sValue);
			}
		}

		return parent::GetValueLabel($sValue);
	}
}