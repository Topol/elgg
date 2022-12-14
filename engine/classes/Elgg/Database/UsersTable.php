<?php

namespace Elgg\Database;

use Elgg\Config;
use Elgg\Database;
use Elgg\Database\Clauses\OrderByClause;
use Elgg\Traits\TimeUsing;

/**
 * Users helper service
 *
 * @internal
 * @since 1.10.0
 */
class UsersTable {

	use TimeUsing;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var Database
	 */
	protected $db;

	/**
	 * @var MetadataTable
	 */
	protected $metadata;

	/**
	 * Constructor
	 *
	 * @param Config        $config   Config
	 * @param Database      $db       Database
	 * @param MetadataTable $metadata Metadata table
	 */
	public function __construct(Config $config, Database $db, MetadataTable $metadata) {
		$this->config = $config;
		$this->db = $db;
		$this->metadata = $metadata;
	}

	/**
	 * Get user by username
	 *
	 * @param string $username The user's username
	 *
	 * @return \ElggUser|null Depending on success
	 */
	public function getByUsername(string $username): ?\ElggUser {
		if (empty($username)) {
			return null;
		}

		// Fixes #6052. Username is frequently sniffed from the path info, which,
		// unlike $_GET, is not URL decoded. If the username was not URL encoded,
		// this is harmless.
		$username = rawurldecode($username);
		if (empty($username)) {
			return null;
		}

		$logged_in_user = elgg_get_logged_in_user_entity();
		if (!empty($logged_in_user) && ($logged_in_user->username === $username)) {
			return $logged_in_user;
		}

		$users = elgg_get_entities([
			'types' => 'user',
			'metadata_name_value_pairs' => [
				[
					'name' => 'username',
					'value' => $username,
					'case_sensitive' => false,
				],
			],
			'limit' => 1,
		]);

		return $users ? $users[0] : null;
	}

	/**
	 * Get an array of users from an email address
	 *
	 * @param string $email Email address
	 * @return \ElggUser[]
	 */
	public function getByEmail(string $email): array {
		if (!$email) {
			return [];
		}

		$users = elgg_get_entities([
			'types' => 'user',
			'metadata_name_value_pairs' => [
				[
					'name' => 'email',
					'value' => $email,
					'case_sensitive' => false,
				],
			],
			'limit' => 1,
		]);

		return $users ?: [];
	}

	/**
	 * Generates a unique invite code for a user
	 *
	 * @param string $username The username of the user sending the invitation
	 *
	 * @return string Invite code
	 * @see self::validateInviteCode()
	 */
	public function generateInviteCode(string $username): string {
		$time = $this->getCurrentTime()->getTimestamp();
		$token = _elgg_services()->hmac->getHmac([$time, $username])->getToken();
		
		return "{$time}.{$token}";
	}

	/**
	 * Validate a user's invite code
	 *
	 * @param string $username The username
	 * @param string $code     The invite code
	 *
	 * @return bool
	 * @see self::generateInviteCode()
	 */
	public function validateInviteCode(string $username, string $code): bool {
		// validate the format of the token created by self::generateInviteCode()
		$matches = [];
		if (!preg_match('~^(\d+)\.([a-zA-Z0-9\-_]+)$~', $code, $matches)) {
			return false;
		}
		$time = (int) $matches[1];
		$mac = $matches[2];

		return _elgg_services()->hmac->getHmac([$time, $username])->matchesToken($mac);
	}
}
