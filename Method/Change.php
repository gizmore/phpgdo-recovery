<?php
namespace GDO\Recovery\Method;

use GDO\Core\GDT;
use GDO\Core\GDT_String;
use GDO\Crypto\BCrypt;
use GDO\Crypto\GDT_Password;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\GDT_Validator;
use GDO\Form\MethodForm;
use GDO\Recovery\GDO_UserRecovery;

/**
 * Change your password after receiving a token via mail.
 *
 * @version 7.0.1
 * @author gizmore
 */
final class Change extends MethodForm
{

	private ?GDO_UserRecovery $token;

	public function isUserRequired(): bool { return false; }

	public function gdoParameters(): array
	{
		return [
			GDT_String::make('userid')->notNull(),
			GDT_String::make('token')->notNull(),
		];
	}

	public function execute(): GDT
	{
		$userid = $this->gdoParameterVar('userid');
		$token = $this->gdoParameterVar('token');
		if (!($this->token = GDO_UserRecovery::getByUIDToken($userid, $token)))
		{
			return $this->error('err_token');
		}
		return parent::execute();
	}

	public function createForm(GDT_Form $form): void
	{
		$this->title('mt_recovery_change', [sitename()]);
		$form->text('p_recovery_change');
		$form->addField(GDT_Password::make('new_password')->label('new_password')->tooltip('tt_password_according_to_security_level'));
		$retype = GDT_Password::make('password_retype')->label('password_retype')->tooltip('tt_password_retype');
		$form->addField($retype);
		$form->addField(GDT_Validator::make()->validator($form, $retype, [$this, 'validatePasswordEqual']));
		$form->actions()->addField(GDT_Submit::make());
		$form->addField(GDT_AntiCSRF::make());
	}

	public function formValidated(GDT_Form $form): GDT
	{
		$user = $this->token->getUser();
		$user->saveSettingVar('Login', 'password', BCrypt::create($form->getFormVar('new_password'))->__toString());
		$this->token->delete();
		return $this->message('msg_pass_changed');
	}

	public function renderPage(): GDT
	{
		return $this->templatePHP('change.php', ['form' => $this->getForm()]);
	}

	public function validatePasswordEqual(GDT_Form $form, GDT_Password $gdt)
	{
		return $form->getFormVar('new_password') === $form->getFormVar('password_retype') ? true : $gdt->error('err_password_retype');
	}

}
