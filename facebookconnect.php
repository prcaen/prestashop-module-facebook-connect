<?php
class FacebookConnect extends Module
{
	private $_xmlFacebookLocales = 'https://www.facebook.com/translations/FacebookLocales.xml';
	private $_defaultCountry		 = 'fr_FR';
	function __construct()
	{
		$this->name = 'facebookconnect';
		$this->tab = 'front_office_features';
		$this->version = 0.1;
		$this->author = 'Pierrick CAEN';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Facebook connect');
		$this->description = $this->l('Implement a Facebook connect');
	}
	
	public function install()
	{
		if(!parent::install())
			return false;

		// Add DB fields
		if(!DB::getInstance()->Execute("INSERT INTO `" . _DB_PREFIX_ . "hook` SET `name`= 'identificationForm', `title`= 'Identification form', `description`= 'Hook for the identification form'"))
			return false;

		if(!Configuration::updateValue('FB_APP_ID', '1234')
			|| !Configuration::updateValue('FB_COUNTRY', $this->_defaultCountry)
			|| !Configuration::updateValue('FB_USER_LOCATION', '')
			|| !Configuration::updateValue('FB_USER_HOMETOWN', '')
			|| !Configuration::updateValue('FB_USER_BIRTHDAY', 'on'))
			return false;

		if(!$this->registerHook('header') || !$this->registerHook('top') || !$this->registerHook('identificationForm'))
			return false;

		return true;
	}

	public function uninstall()
	{
		if(!parent::uninstall())
			return false;

		// Remove DB fields
		if(!DB::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "hook` WHERE `hook`.`name` = 'identificationForm'"))
			return false;

		if(!Configuration::deleteByName('FB_APP_ID')
			|| !Configuration::deleteByName('FB_COUNTRY')
			|| !Configuration::deleteByName('FB_USER_LOCATION')
			|| !Configuration::deleteByName('FB_USER_HOMETOWN')
			|| !Configuration::deleteByName('FB_USER_BIRTHDAY'))
			return false;

		return true;
	}

	public function getContent()
	{
		$output = $this->postProcess();

		$output .= '<div class="conf warn"><img src="../img/admin/warning.gif" alt="warn">' . $this->l('To display the Facebook connect in the authentification page, please add {if isset($HOOK_IDENTIFICATION_FORM)}{$HOOK_IDENTIFICATION_FORM}{/if} on line 102 after {if !isset($email_create)}') . '</div>';

		return $output . $this->_displayForm();
	}

	private function _displayForm()
	{
		$output  = '';
		$output .= '<form action="' . Tools::safeOutput($_SERVER['REQUEST_URI']) . '" method="post">';
		$output .= '	<fieldset>';
		$output .= '		<legend>' . $this->l('Settings') . '</legend>';
		$output .= '		<p>';
		$output .= '			<label for="fb_app_id">' . $this->l('Facebook app id') . '</label>';
		$output .= '			<input type="text" name="fb_app_id" id="fb_app_id" value="' . Configuration::get('FB_APP_ID') . '" />';
		$output .= '			<em style="color: #7F7F7F; font-size: 0.85em">' . $this->l('Your application ID which you can get from:') . ' <a href="https://developers.facebook.com">https://developers.facebook.com</a>' . '</em>';
		$output .= '		</p>';
		$output .= '		<p>';
		$output .= '			<label for="fb_country">' . $this->l('Default country') . '</label>';
		$output .= '			<select name="fb_country" id="fb_country">';
		foreach($this->getFacebookLocales($this->_xmlFacebookLocales) as $locale)
			$output .= '				<option value="' . $locale['code'] . '"' . (Configuration::get('FB_COUNTRY') == $locale['code'] ? 'selected="selected"' : '') . '>' . $locale['name'] . '</option>';
		$output .= '			</select>';
		$output .= '		</p>';
		$output .= '		<p>';
		$output .= '			<input type="checkbox" name="fb_user_location" id="fb_user_location"' . (Configuration::get('FB_USER_LOCATION') == 'on' ? 'checked="checked"' : '') . ' />';
		$output .= '			<label for="fb_user_location">' . $this->l('Use location permission') . '</label>';
		$output .= '		</p>';
		$output .= '		<p style="margin-bottom: 1.2em">';
		$output .= '			<input type="checkbox" name="fb_user_hometown" id="fb_user_hometown"' . (Configuration::get('FB_USER_HOMETOWN') == 'on' ? 'checked="checked"' : '') . ' />';
		$output .= '			<label for="fb_user_hometown">' . $this->l('Use hometown permission') . '</label>';
		$output .= '		</p>';
		$output .= '		<p>';
		$output .= '			<input type="checkbox" name="fb_user_birthday" id="fb_user_birthday"' . (Configuration::get('FB_USER_BIRTHDAY') == 'on' ? 'checked="checked"' : '') . ' />';
		$output .= '			<label for="fb_user_birthday">' . $this->l('Use birthday permission') . '</label>';
		$output .= '		</p>';
		$output .= '		<p style="text-align: center">';
		$output .= '			<input type="submit" class="button" name="submit_config" value="'.$this->l('Validate').'"/>';
		$output .= '		</p>';
		$output .= '	</fieldset>';
		$output .= '</form>';

		return $output;
	}
	
	public function postProcess()
	{
		if(Tools::isSubmit('submit_config'))
		{
			Configuration::updateValue('FB_APP_ID', (int)Tools::getValue('fb_app_id'));
			Configuration::updateValue('FB_COUNTRY', Tools::getValue('fb_country'));
			Configuration::updateValue('FB_USER_LOCATION', Tools::getValue('fb_user_location'));
			Configuration::updateValue('FB_USER_HOMETOWN', Tools::getValue('fb_user_hometown'));
			Configuration::updateValue('FB_USER_BIRTHDAY', Tools::getValue('fb_user_birthday'));
			return '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
	}

	function hookHeader($params)
	{
		global $smarty;
		$smarty->assign(array('HOOK_IDENTIFICATION_FORM' => Module::hookExec('identificationForm')));

		// Add media to header
		Tools::addCSS(($this->_path) . 'facebookconnect.css', 'all');
		//Tools::addJS(($this->_path)	 . 'http://connect.facebook.net/'.Configuration::get('FB_COUNTRY').'/all.js');
	}

	function hookTop($params)
	{
		global $smarty;

		$conf = array(
			'fb_app_id'	 => Configuration::get('FB_APP_ID'),
			'fb_country' => Configuration::get('FB_COUNTRY'),
			'fb_perms'	 => (Configuration::get('FB_USER_LOCATION') == 'on' ? 'user_location,' : '') . (Configuration::get('FB_USER_HOMETOWN') == 'on' ? 'user_hometown,' : '') . (Configuration::get('FB_USER_BIRTHDAY') == 'on' ? 'user_birthday' : '')
		);

		$smarty->assign('fb_connect', $conf);

		return $this->display(__FILE__, 'facebookconnect.tpl');
	}

	function hookIdentificationForm($params)
	{
		return $this->hookTop($params);
	}

	private function getFacebookLocales($link)
	{
		$xml = simplexml_load_file($link);
		$locales = array();
		$i = 0;

		foreach ($xml as $locale)
		{
			$locales[$i]['name'] = $locale->englishName->__toString();
			$locales[$i]['code'] = $locale->codes->code->standard->representation->__toString();
			$i++;
		}

		return $locales;
	}
}
?>