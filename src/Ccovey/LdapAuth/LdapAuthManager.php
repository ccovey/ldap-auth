<?php namespace Ccovey\LdapAuth;

use Illuminate\Auth;

use adLDAP\adLDAP;

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
        $ad = new LdapAdService();

        $model = null;
        
        if ($this->app['config']['auth.model']) {
            $model = $this->app['config']['auth.model'];
        }
        
        return new LdapAuthUserProvider($ad, $model);
    }
}