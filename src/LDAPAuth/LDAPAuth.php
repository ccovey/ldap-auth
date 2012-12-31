<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LDAPAuthManager
 *
 * @author ccovey
 */

namespace Ccovey\LDAPAuth;

use adLDAP;

class LDAPAuthManager extends Illuminate\Auth\AuthManager {
    
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