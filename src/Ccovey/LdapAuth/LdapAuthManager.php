<?php namespace Ccovey\LdapAuth;

use Exception;
use adLDAP\adLDAP;
use Illuminate\Auth\Guard;
use Illuminate\Auth\AuthManager;

class LdapAuthManager extends AuthManager
{
    /**
     * 
     * @return \Config\Packages\Guard
     */
    protected function createLdapDriver()
    {
        $provider = $this->createLdapProvider();
        
        return new Guard($provider, $this->app['session.store']);
    }
    
    /**
     * 
     * @return \Config\Packages\LdapUserProvider
     */
    protected function createLdapProvider()
    {
        $ad = new adLDAP($this->getLdapConfig());

        $model = null;
        
        if ($this->app['config']['auth.model']) {
            $model = $this->app['config']['auth.model'];
        }
        
        return new LdapAuthUserProvider($ad, $this->getAuthConfig(), $model);
    }

    protected function getAuthConfig()
    {
        if ( ! is_null($this->app['config']['auth']) ) {
            return $this->app['config']['auth'];
        }
        throw new MissingAuthConfigException;
    }

    protected function getLdapConfig()
    {
        if (is_array($this->app['config']['adldap'])) return $this->app['config']['adldap'];

        return array();
    }
}

/**
* MissingAuthConfigException
*/
class MissingAuthConfigException extends Exception
{
    
    function __construct()
    {
        $message = "Please Ensure a config file is present at app/config/auth.php";

        parent::__construct($message);
    }
}
