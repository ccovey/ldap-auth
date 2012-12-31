<?php

/**
 * Description of LDAPAuthManager
 *
 * @author ccovey
 */

namespace LDAPAuth;

use Illuminate\Auth as Auth;

use adLDAP;

class LDAPAuthManager extends Auth\AuthManager {
    
    /**
     * 
     * @return \Config\Packages\Guard
     */
    protected function createLDAPDriver()
    {
        $provider = $this->createLDAPProvider();
        
        return new Guard($provider, $this->app['session']);
    }
    
    /**
     * 
     * @return \Config\Packages\LDAPUserProvider
     */
    protected function createLDAPProvider()
    {
        $ad = new adLDAP\adLDAP();
        
        return new LDAPUserProvider($ad);
    }
}