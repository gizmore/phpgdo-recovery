<?php
namespace GDO\Recovery;

use GDO\Core\GDO;
use GDO\Core\GDT_CreatedAt;
use GDO\Core\GDT_Token;
use GDO\User\GDO_User;
use GDO\User\GDT_User;

/**
 * A password reset token.
 *
 * @version 7.0.1
 * @author gizmore
 */
class GDO_UserRecovery extends GDO
{

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

	public static function getByUserId(string $userid): ?self
	{
		return self::getBy('pw_user_id', $userid);
	}

	public function validateToken(string $token): bool
	{
		return $this->getToken() === $token;
	}

	public function getToken(): string
	{
		return $this->gdoVar('pw_token');
	}

	public function gdoCached(): bool
	{
		return false;
	}

	# #############
	# ## Static ###
	# #############

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

}
