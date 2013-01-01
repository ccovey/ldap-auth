ldap-auth
=========

ldap-auth

Laravel 4 Active Directory LDAP Authentication driver. 

After you run `composer install`, be sure to go into the adLDAP.php and set the configuration settings.

Current implementation returns user display name, user login name, groups the user is a part of, as well
as all users in the `Departments` User group. This will be changed shortly for more customizable user list returns.
