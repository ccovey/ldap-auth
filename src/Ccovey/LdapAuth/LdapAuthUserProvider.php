<?php namespace Ccovey\LdapAuth;

use Illuminate\Config\Repository;
use adLDAP;
use Illuminate\Auth;

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
    public function __construct(adLDAP\adLDAP $conn, $config, $model = null)
    {
        $this->ad = $conn;

        $this->config = $config;
        
        $this->model = $model;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @param  mixed  $ldapIdentifier; default to null
     * @return Illuminate\Auth\GenericUser|null
     */
    public function retrieveByID($identifier, $ldapIdentifier = null)
    {
        if( is_null($ldapIdentifier) ) {
            $ldapIdentifier = $identifier;
        }
        $ldapUserInfo = null;

        $infoCollection = $this->ad->user()->infoCollection($ldapIdentifier, array('*') );

        if ( $infoCollection ) {
            $ldapUserInfo = $this->setInfoArray($infoCollection);

            if ($this->model) {
                $model = $this->createModel()->newQuery()->where($this->getUsernameField(), $identifier)->first();

                if ( ! is_null($model) ) {
                    return $this->addLdapToModel($model, $ldapUserInfo);
                }
            }

            return new LdapUser((array) $ldapUserInfo);
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
        // \Log::warning(json_encode($credentials));//debug
        if ($this->model) {
            $query = $this->createModel()->newQuery();

            foreach ($credentials as $key => $value)
            {
                if ( ! str_contains($key, 'password')) $query->where($key, $value);
            }

            $model = $query->first();
            // \Log::warning($model->$identifierField);//debug
            if($model) {
                return $this->retrieveByID($model->getKey(), $model->{$this->getUsernameField()});
            }
        }
        return $this->retrieveByID( $credentials[$this->getUsernameField()] );
    }

    /**
     * @return string
     */
    public function getUsernameField()
    {
        return (\Config::has('auth.username_field')) ? \Config::get('auth.username_field') : 'username';
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
    protected function setInfoArray($infoCollection)
    {
    	/*
		* in app/auth.php set the fields array with each value
		* as a field you want from active directory
		* If you have 'user' => 'samaccountname' it will set the $info['user'] = $infoCollection->samaccountname
		* refer to the adLDAP docs for which fields are available.
    	*/
        if ( ! empty($this->config['fields'])) {
            foreach ($this->config['fields'] as $k => $field) {
                if ($k == 'groups') {
                    $info[$k] = $this->getAllGroups($infoCollection->memberof);
                }elseif ($k == 'primarygroup') {
                    $info[$k] = $this->getPrimaryGroup($infoCollection->distinguishedname);
                }else{    
                    $info[$k] = $infoCollection->$field;
                }
            }
        }else{
            //if no fields array present default to username and displayName
            $info['username'] = $infoCollection->samaccountname;
            $info['displayname'] = $infoCollection->displayName;
            $info['primarygroup'] = $this->getPrimaryGroup($infoCollection->distinguishedname);
            $info['groups'] = $this->getAllGroups($infoCollection->memberof);
        }
        
        /*
		* I needed a user list to populate a dropdown
		* Set userlist to true in app/config/auth.php and set a group in app/config/auth.php as well
		* The table is the OU in Active directory you need a list of.
        */
        if ( ! empty($this->config['userList'])) {
            $info['userlist'] = $this->ad->folder()->listing(array($this->config['group']));
        }

        return $info;
    }

    /**
     * 
     * @return Illuminate\Auth\UserInterface
     */
    public function createModel()
    {   
        $model = '\\' . ltrim($this->model, '\\');
        
        return new $model;
    }

    /**
     * Add Ldap fields to current user model.
     * 
     * @param Illuminate\Auth\UserInterface $model
     * @param adLDAP\collection\adLDAPCollection $ldap
     * @return Illuminate\Auth\UserInterface
     */
    protected function addLdapToModel($model, $ldap)
    {
        $combined = $model->getAttributes() + $ldap;

        return $model->fill($combined);
    }

    /**
     * Return Primary Group Listing
     * @param  array $groupList 
     * @return string
     */
    protected function getPrimaryGroup($groupList)
    {
        $groups = explode(',', $groupList);

        return substr($groups[1], '3');
    }

    /**
     * Return list of groups (except domain and suffix)
     * @param  array $groups 
     * @return array
     */
    protected function getAllGroups($groups) 
    {
        $grps = '';
        if ( ! is_null($groups) ) {
            foreach ($groups as $k => $group) {
                $splitGroups = explode(',', $group);
                foreach ($splitGroups as $splitGroup) {
                    if (substr($splitGroup,0, 3) !== 'DC=') {
                        $grps[substr($splitGroup, '3')] = substr($splitGroup, '3');
                    }
                }
            }
        }

        return $grps;
    }

    public function getModel()
    {
        return $this->model;
    }
}
