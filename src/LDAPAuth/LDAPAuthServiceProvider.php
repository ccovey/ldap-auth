<?php

/**
 * Description of LDAPAuthServiceProvider
 *
 * @author ccovey
 */
namespace Ccovey\LDAPAuth;

use Illuminate\Auth;

class LDAPAuthServiceProvider extends Illuminate\Auth\AuthServiceProvider{
    public function register()
	{
		$this->app['auth'] = $this->app->share(function($app)
		{
			// Once the authentication service has actually been requested by the developer
			// we will set a variable in the application indicating such. This helps us
			// know that we need to set any queued cookies in the after event later.
			$app['auth.loaded'] = true;

			return new Ccovey\LDAPAuth\LDAPAuthManager($app);
		});
	}
}

?>
