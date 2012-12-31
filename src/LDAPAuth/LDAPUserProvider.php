<?php

/**
 * Description of LDAPUserProvider
 *
 * @author ccovey
 */
namespace LDAPAuth;

use adLDAP;

class LDAPUserProvider implements UserProviderInterface {
    
    protected $ad;
        
    public function __construct(adLDAP\adLDAP $conn)
    {
        $this->ad = $conn;
    }
    
	/**
	 * Retrieve a user by their unique idenetifier.
	 *
	 * @param  mixed  $identifier
	 * @return Illuminate\Auth\UserInterface|null
	 */
	public function retrieveByID($identifier)
    {
        $infoCollection = $this->ad->user()->infoCollection($identifier);
        $userList = $this->ad->folder()->listing(array('Departments'));
        unset($userList['count']);
        
        foreach ($userList as $user) {
            if (substr($user['distinguishedname'][0], 0, 3) !== 'OU=') {
                $users[] = substr(explode(',', $user['distinguishedname'][0])[0], 3);
            }
        }
        
        $info = [
            'id'       => $infoCollection->samaccountname,
            'username' => $infoCollection->displayname,
            'email'    => $infoCollection->mail,
            'groups'   => $infoCollection->memberOf,
            'userList' => $users
        ];
        
        if (isset($infoCollection)) {
            return new GenericUser((array) $info);
        }
    }

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array  $credentials
	 * @return Illuminate\Auth\UserInterface|null
	 */
	public function retrieveByCredentials(array $credentials)
    {
        $infoCollection = $this->ad->user()->infoCollection($credentials["username"]);
        $userList = $this->ad->folder()->listing(array('Departments'));
        unset($userList['count']);
        
        foreach ($userList as $user) {
            if (substr($user['distinguishedname'][0], 0, 3) !== 'OU=') {
                $users[] = substr(explode(',', $user['distinguishedname'][0])[0], 3);
            }
        }
        
        $info = [
            'id'       => $infoCollection->samaccountname,
            'username' => $infoCollection->displayname,
            'email'    => $infoCollection->mail,
            'groups'   => $infoCollection->memberOf,
            'userList' => $users
        ];
        
        if (isset($infoCollection)) {
            return new GenericUser((array) $info);
        }
    }

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  Illuminate\Auth\UserInterface  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validateCredentials(UserInterface $user, array $credentials)
    {
        return $this->ad->authenticate($credentials['username'], $credentials['password']);
    }
}