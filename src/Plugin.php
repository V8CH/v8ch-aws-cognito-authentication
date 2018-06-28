<?php
/**
 * File defines core plugin class.
 *
 * @package main
 * @since   0.0.1
 */

namespace V8CH\WordPress\AWSCognitoAuthentication;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Dotenv\Dotenv;
use WP_Error;
use WP_User;

/**
 * Core plugin class.
 *
 * @author  Robert Pratt <bpong@v8ch.com>
 * @since   0.0.1
 */
class Plugin {

	/**
	 * Unique identifier.
	 *
	 * @access  protected
	 * @since   0.0.1
	 * @var     string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * Current version.
	 *
	 * @access  protected
	 * @since   0.0.1
	 * @var     string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Setup core functionality.
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		if ( defined( 'V8CH_AWS_COGNITO_AUTHENTICATION_VERSION' ) ) {
			$this->version = V8CH_AWS_COGNITO_AUTHENTICATION_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'v8ch-aws-cognito-authentication';
		$dotenv            = new Dotenv( __DIR__ . '/../' );
		$dotenv->load();

	}

	/**
	 * Authenticate agains AWS Cognito.
	 *
	 * @param   WP_User $user User object.
	 * @param   string  $username Submitted username.
	 * @param   string  $password Submitted password.
	 * @since   0.0.1
	 */
	public function authenticate_with_aws_cognito( $user, $username, $password ) {
		if ( '' === $username || '' === $password ) {
			return;
		}

		$cognito_identity_provider_client = new CognitoIdentityProviderClient(
			[
				'version'     => 'latest',
				'region'      => 'us-east-2',
				'credentials' => array(
					'key'    => getenv( AWS_COGNITO_APP_CLIENT_KEY ),
					'secret' => getenv( AWS_COGNITO_APP_CLIENT_SECRET ),
				),
			]
		);

		try {
			$auth_result = $cognito_identity_provider_client->adminInitiateAuth(
				[
					'AuthFlow'       => 'ADMIN_NO_SRP_AUTH',
					'AuthParameters' => [
						'USERNAME' => $username,
						'PASSWORD' => $password,
					],
					'ClientId'       => getenv( AWS_COGNITO_APP_CLIENT_ID ),
					'UserPoolId'     => getenv( AWS_COGNITO_USER_POOL_ID ),
				]
			);
		} catch ( CognitoIdentityProviderException $exception ) {
			switch ( $exception->getAwsErrorMessage() ) {
				case 'Incorrect username or password.':
					return $user = new WP_Error( 'denied', __( 'ERROR: Incorrect username or password.', 'default' ) );
				case 'User does not exist.':
					return $user = new WP_Error( 'denied', __( 'ERROR: Username not found.', 'default' ) );
				default:
					return new WP_Error( 'denied', __( 'ERROR: Unknown authentication error.', 'default' ) );
			}
		}

		$aws_cognito_user       = $cognito_identity_provider_client->getUser( [ 'AccessToken' => $auth_result->get( 'AuthenticationResult' )['AccessToken'] ] );
		$aws_cognito_username   = $aws_cognito_user->get( 'Username' );
		$aws_cognito_attributes = $aws_cognito_user->get( 'UserAttributes' );

		$userdata = WP_User::get_data_by( 'slug', $aws_cognito_username );

		return new WP_User( $userdata->ID );
	}



	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since   0.0.1
	 */
	public function run() {
		add_filter( 'authenticate', array( $this, 'authenticate_with_aws_cognito' ), 10, 3 );
		remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since   0.0.1
	 * @return  string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since   0.0.1
	 * @return  string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
