<?php

namespace Elgg\PAM\User;

use Elgg\Exceptions\LoginException;

/**
 * PAM handler to authenticate a user based on username/password
 * Used for the 'user' policy
 *
 * @since 4.3
 * @internal
 */
class Password {
	
	/**
	 * Authenticate a user
	 *
	 * @param array $credentials the user credentials
	 *
	 * @return bool
	 * @throws LoginException
	 */
	public function __invoke(array $credentials): bool {
		if (!isset($credentials['username']) || !isset($credentials['password'])) {
			return false;
		}
		
		return elgg_call(ELGG_SHOW_DISABLED_ENTITIES, function() use ($credentials) {
			$user = get_user_by_username($credentials['username']);
			if (!$user) {
				throw new LoginException(_elgg_services()->translator->translate('LoginException:UsernameFailure'));
			}
			
			$password_svc = _elgg_services()->passwords;
			$password = $credentials['password'];
			$hash = $user->password_hash;
			
			if (check_rate_limit_exceeded($user->guid)) {
				throw new LoginException(_elgg_services()->translator->translate('LoginException:AccountLocked'));
			}
			
			if (!$password_svc->verify($password, $hash)) {
				log_login_failure($user->guid);
				
				throw new LoginException(_elgg_services()->translator->translate('LoginException:PasswordFailure'));
			}
			
			if ($password_svc->needsRehash($hash)) {
				$password_svc->forcePasswordReset($user, $password);
			}
			
			return true;
		});
	}
}