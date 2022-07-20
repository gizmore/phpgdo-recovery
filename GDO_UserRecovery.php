<?php
namespace GDO\Recovery;

use GDO\Core\GDO;
use GDO\Core\GDT_CreatedAt;
use GDO\DB\GDT_Token;
use GDO\User\GDT_User;
use GDO\User\GDO_User;

class GDO_UserRecovery extends GDO
{
	public function gdoCached() : bool { return false; }
	public function gdoColumns() : array
	{
		return array(
			GDT_User::make('pw_user_id')->primary(),
			GDT_Token::make('pw_token')->notNull(),
			GDT_CreatedAt::make('pw_created_at'),
		);
	}
	
	public function getToken()
	{
		return $this->gdoVar('pw_token');
	}
	
	public function validateToken($token)
	{
		return $this->getToken() === $token;
	}

	/**
	 * @return GDO_User
	 */
	public function getUser()
	{
		return $this->gdoValue('pw_user_id');
	}
	
	/**
	 * @param string $userid
	 * @return self
	 */
	public static function getByUserId($userid)
	{
		return self::getBy('pw_user_id', $userid);
	}
	
	/**
	 * 
	 * @param string $userid
	 * @param string $token
	 * @return self
	 */
	public static function getByUIDToken($userid, $tok)
	{
		if ($token = self::getByUserId($userid))
		{
			if ($token->validateToken($tok))
			{
				return $token;
			}
		}
	}
		
}
