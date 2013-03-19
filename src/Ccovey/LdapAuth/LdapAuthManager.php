<?php namespace Ccovey\LdapAuth;

use Illuminate\Auth;
use Illuminate\Support\Manager;

/**
* 
*/
class LdapAuthManager extends Auth\AuthManager
{
    /**
     * 
     * @return \Config\Packages\Guard
     */
    protected function createLdapDriver()
    {
        $provider = $this->createLdapProvider();
        
        return new Auth\Guard($provider, $this->app['session']);
    }
    
    /**
     * 
     * @return \Config\Packages\LdapUserProvider
     */
    protected function createLdapProvider()
    {
        $ad = new adLDAP\adLDAP();
        
        return new LdapUserProvider($ad);
    }
}