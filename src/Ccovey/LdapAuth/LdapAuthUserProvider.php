<?php namespace Ccovey\LdapAuth;

use Illuminate\Auth;
use adLDAP\adLDAP;

/**
 * Class to build array to send to GenericUser
 * This allows the fields in the array to be
 * accessed through the Auth::user() method
 */
class LdapAuthUserProvider implements Auth\UserProviderInterface
{
    /**
     * Active Directory Object
     * 
     * @var adLDAP\adLDAP
     */
    protected $ad;
    
    /**
     * DI in adLDAP object for use throughout
     * 
     * @param adLDAP\adLDAP $conn
     */
    public function __construct(adLDAP $conn)
    {
        $this->ad = $conn;
    }

    /**
     * Retrieve a user by their unique idenetifier.
     *
     * @param  mixed  $identifier
     * @return Illuminate\Auth\GenericUser|null
     */
    public function retrieveByID($identifier)
    {
        $infoCollection = $this->ad->user()->infoCollection($identifier);
        
        if (isset($infoCollection)) {
            return new Auth\GenericUser((array) $this->setInfoArray($infoCollection));
        }
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return Illuminate\Auth\GenericUser|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        return $this->retrieveByID($credentials['username']);
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  Illuminate\Auth\UserInterface  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Auth\UserInterface $user, array $credentials)
    {
        return $this->ad->authenticate($credentials['username'], $credentials['password']);
    }
    
    /**
     * Build the array sent to GenericUser for use in Auth::user()
     * 
     * @param adLDAP\adLDAP $infoCollection
     * @return array $info
     */
    protected function setInfoArray(adLDAP $infoCollection)
    {
    	/*
		* in app/auth.php set the fields array with each value
		* as a field you want from active directory
		* If you have 'user' => 'username' it will set the $info['user'] = $infoCollection->username
		* refer to the adLDAP docs for which fields are available.
    	*/
        foreach (\Config::get('auth.fields') as $k => $field) {
            $info[$k] = $infoCollection->$field;
        }
        
        /*
		* I needed a user list to populate a dropdown
		* Set userlist to true in app/auth.php and set a table in app/auth.php as well
		* The table is the OU in Active directory you need a list of.
        */
        if (\Config::has('auth.userlist')) {
            $info['userlist'] = $this->ad->folder()->listing(array(\Config::get('auth.table')));
        }
        
        return $info;
    }

}