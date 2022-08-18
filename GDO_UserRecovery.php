<?php
namespace GDO\Recovery;

use GDO\Core\GDO;
use GDO\Core\GDT_CreatedAt;
use GDO\Core\GDT_Token;
use GDO\User\GDT_User;
use GDO\User\GDO_User;

/**
 * A password reset token.
 *
 * @author gizmore
 * @version 7.0.1
 */
class GDO_UserRecovery extends GDO
{
	public function gdoCached(): bool
	{
		return false;
	}

	public function gdoColumns(): array
	{
		return [
			GDT_User::make('pw_user_id')->primary(),
			GDT_Token::make('pw_token')->notNull(),
			GDT_CreatedAt::make('pw_created_at'),
		];
	}

	public function getUser(): GDO_User
	{
		return $this->gdoValue('pw_user_id');
	}

	public function getToken(): string
	{
		return $this->gdoVar('pw_token');
	}

	public function validateToken(string $token): bool
	{
		return $this->getToken() === $token;
	}

	# #############
	# ## Static ###
	# #############
	public static function getByUserId(string $userid): ?self
	{
		return self::getBy('pw_user_id', $userid);
	}

	public static function getByUIDToken(string $userid, string $tok): ?self
	{
		if ($token = self::getByUserId($userid))
		{
			if ($token->validateToken($tok))
			{
				return $token;
			}
		}
		return null;
	}

}
