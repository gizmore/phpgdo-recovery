<?php
namespace GDO\Recovery\Method;

use GDO\Captcha\GDT_Captcha;
use GDO\Core\GDT;
use GDO\Core\GDT_Hook;
use GDO\Core\GDT_Template;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Mail\GDT_Email;
use GDO\Mail\Mail;
use GDO\Net\GDT_IP;
use GDO\Recovery\GDO_UserRecovery;
use GDO\Recovery\Module_Recovery;
use GDO\UI\GDT_Link;
use GDO\User\GDO_User;
use GDO\User\GDT_Username;

/**
 * Request Password Forgotten Token.
 * Disabled when DEBUG_MAIL is on :)
 *
 * @author gizmore
 */
final class Form extends MethodForm
{

	public function isUserRequired(): bool { return false; }

	public function isEnabled(): bool { return (!GDO_DEBUG_EMAIL) || (GDT_IP::isLocal()); }

	public function createForm(GDT_Form $form): void
	{
		if (Module_Recovery::instance()->cfgLogin())
		{
			$form->addField(GDT_Username::make('login')->tooltip('tt_recovery_login')->max(128));
		}

		elseif (Module_Recovery::instance()->cfgEmail())
		{
			$form->addField(GDT_Email::make('email')->tooltip('tt_recovery_email'));
		}

		else
		{
			$this->error('err_no_recovery_option');
		}

		if (Module_Recovery::instance()->cfgCaptcha())
		{
			$form->addField(GDT_Captcha::make());
		}

		$form->actions()->addField(GDT_Submit::make());
		$form->addField(GDT_AntiCSRF::make());

		GDT_Hook::callHook('RecoveryForm', $form);
	}

	public function formValidated(GDT_Form $form)
	{
		if (!($user = GDO_User::getByName($form->getFormVar('login'))))
		{
			if (!($user = GDO_User::table()->getSingleWithSetting('Mail', 'email', $form->getFormVar('email'))))
			{
				return $this->error('err_email_or_login')->addField($this->renderPage());
			}
		}
		if (!$user->hasMail())
		{
			return $this->error('err_recovery_needs_a_mail', [$user->renderUserName()]);
		}
		$this->sendMail($user);
		return $this->message('msg_recovery_mail_sent');
	}

	public function renderPage(): GDT
	{
		return $this->templatePHP('form.php', ['form' => $this->getForm()]);
	}

	public function sendMail(GDO_User $user)
	{
		$token = GDO_UserRecovery::blank(['pw_user_id' => $user->getID()])->replace();
		$link = GDT_Link::anchor(url('Recovery', 'Change', "&userid={$user->getID()}&token=" . $token->getToken()));

		$mail = Mail::botMail();
		$mail->setSubject(t('mail_subj_recovery', [sitename()]));
		$tVars = [
			'username' => $user->renderUserName(),
			'link' => $link,
		];
		$body = GDT_Template::php('Recovery', 'mail/recovery_mail.php', $tVars);
		$mail->setBody($body);
		$mail->sendToUser($user);
	}

}
