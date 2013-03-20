<?php namespace Ccovey\LdapAuth;

use Illuminate\Auth;
use adLDAP;

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
     *
     * @var type string
     */
    protected $model;
    
    /**
     * DI in adLDAP object for use throughout
     * 
     * @param adLDAP\adLDAP $conn
     */
    public function __construct(adLDAP\adLDAP $conn, $model = null)
    {
        $this->ad = $conn;
        
        $this->model = $model;
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

        $ldapUserInfo = $this->setInfoArray($infoCollection);

        if ($this->model) {
            $model = $this->createModel()->newQuery()->find($identifier);
            
            return $this->addLdapToModel($model, $ldapUserInfo);
        }
        
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
    protected function setInfoArray(adLDAP\collections\adLDAPUserCollection $infoCollection)
    {
    	/*
		* in app/auth.php set the fields array with each value
		* as a field you want from active directory
		* If you have 'user' => 'samaccountname' it will set the $info['user'] = $infoCollection->samaccountname
		* refer to the adLDAP docs for which fields are available.
    	*/
        if (\Config::has('auth.fields')) {
            foreach (\Config::get('auth.fields') as $k => $field) {
                $info[$k] = $infoCollection->$field;
            }
        }else{
            //if no fields array present default to username and displayName
            $info['username'] = $infoCollection->samaccountname;
            $info['displayname'] = $infoCollection->displayName;
        }
        
        /*
		* I needed a user list to populate a dropdown
		* Set userlist to true in app/config/auth.php and set a group in app/config/auth.php as well
		* The table is the OU in Active directory you need a list of.
        */
        if (\Config::has('auth.userlist')) {
            $info['userlist'] = $this->ad->folder()->listing(array(\Config::get('auth.group')));
        }
        
        return $info;
    }

    protected function createModel()
    {   
        $model = '\\' . ltrim($this->model, '\\');
        
        return new $model;
    }

    protected function addLdapToModel($model, $ldap)
    {
        $combined = $model->getAttributes() + $ldap;

        return $model->fill($combined);
    }
}