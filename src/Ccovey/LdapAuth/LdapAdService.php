<?php namespace Ccovey\LdapAuth;

use adLDAP\adLDAP;

/**
 * Description of LdapAdService
 *
 * @author ccovey
 */
class LdapAdService extends adLDAP
{
    public function authenticate($username, $password = null, $preventRebind = false) {
        // Prevent null binding
        if ($username === null) { return false; }
        if (empty($username)) { return false; }
        
        // Allow binding over SSO for Kerberos
        if ($this->useSSO && $_SERVER['REMOTE_USER'] && $_SERVER['REMOTE_USER'] == $username && $this->adminUsername === null && $_SERVER['KRB5CCNAME']) {
            putenv("KRB5CCNAME=" . $_SERVER['KRB5CCNAME']);
            $this->ldapBind = @ldap_sasl_bind($this->ldapConnection, null, null, "GSSAPI");
            if (!$this->ldapBind) {
                throw new adLDAPException('Rebind to Active Directory failed. AD said: ' . $this->getLastError());
            }
            else {
                return true;
            }
        }
        
        // Bind as the user        
        $ret = true;
        $this->ldapBind = @ldap_bind($this->ldapConnection, $username . $this->accountSuffix, $password);
        if (!$this->ldapBind){ 
            $ret = false;
        }

        // Cnce we've checked their details, kick back into admin mode if we have it
        if ($this->adminUsername !== null && !$preventRebind) {
            $this->ldapBind = @ldap_bind($this->ldapConnection, $this->adminUsername . $this->accountSuffix , $this->adminPassword);
            if (!$this->ldapBind){
                // This should never happen in theory
                throw new adLDAPException('Rebind to Active Directory failed. AD said: ' . $this->getLastError());
            } 
        } 
        
        return $ret;
    }
}