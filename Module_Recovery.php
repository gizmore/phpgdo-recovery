<?php
namespace GDO\Recovery;

use GDO\Core\GDO_Module;
use GDO\Date\GDT_Duration;
use GDO\Form\GDT_Form;
use GDO\Core\GDT_Checkbox;
use GDO\UI\GDT_Button;
/**
 * Password recovery module.
 *
 * @author gizmore
 * @version 7.0.1
 * @since 1.0.0
 */
class Module_Recovery extends GDO_Module
{
	##############
	### Module ###
	##############
	public function getDependencies() : array { return ['Mail']; }
	public function getFriendencies() : array { return ['Captcha']; }
	public function getClasses() : array { return [GDO_UserRecovery::class]; }
	public function onLoadLanguage() : void { $this->loadLanguage('lang/recovery'); }

	##############
	### Config ###
	##############
	public function getConfig() : array
	{
		return [
			GDT_Checkbox::make('recovery_login')->initial('1'),
			GDT_Checkbox::make('recovery_email')->initial('1'),
			GDT_Checkbox::make('recovery_captcha')->initial('1'),
			GDT_Duration::make('recovery_timeout')->initial('1h'),
		];
	}
	public function cfgLogin() : bool { return $this->getConfigValue('recovery_login'); }
	public function cfgEmail() : bool { return $this->getConfigValue('recovery_email'); }
	public function cfgCaptcha() : bool { return $this->getConfigValue('recovery_captcha'); }
	public function cfgTimeout() : int { return $this->getConfigValue('recovery_timeout'); }
	
	#############
	### Hooks ###
	#############
	/**
	 * Hook login form with link to recovery.
	 */
	public function hookLoginForm(GDT_Form $form)
	{
		$this->hookRegisterForm($form);
	}
	
	/**
	 * Hook register form with link to recovery.
	 */
	public function hookRegisterForm(GDT_Form $form)
	{
	    $form->actions()->addField(
	    	GDT_Button::make('btn_recovery')->secondary()
	    	->href(href('Recovery', 'Form')));
	}
	
}
