<?php namespace Ccovey\LdapAuth;

use Illuminate\Auth;

/**
 * Description of LdapUser
 *
 * @author ccovey
 */
class LdapUser implements Auth\UserInterface
{
    /**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
    public function getAuthIdentifier()
	{
		return $this->getKey();
	}
    
    /**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->attributes['password'];
	}
}