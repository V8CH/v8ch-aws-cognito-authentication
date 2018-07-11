<?php // phpcs:ignore
/**
 * File defines core plugin class.
 *
 * @package aws-cognito-authentication
 * @since   1.0.0
 */

namespace V8CH\WordPress\AWSCognitoAuthentication;

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Dotenv\Dotenv;
use WP_Error;
use WP_User;

$dotenv = new Dotenv(__DIR__ . '/../');
$dotenv->load();

class Plugin
{
    protected $cognitoIdpClient;

    public function __construct()
    {
        $this->cognitoIdpClient = new CognitoIdentityProviderClient(
            [
                'version'     => 'latest',
                'region'      => 'us-east-2',
                'credentials' => [
                        'key'    => getenv(AWS_COGNITO_APP_CLIENT_KEY),
                        'secret' => getenv(AWS_COGNITO_APP_CLIENT_SECRET),
                ],
            ]
        );
    }

    public function authenticateWithAwsCognito($user, $username, $password)
    {
        if ('' === $username || '' === $password) {
            return;
        }

        try {
            $authResult = $this->cognitoIdpClient->adminInitiateAuth(
                [
                'AuthFlow'       => 'ADMIN_NO_SRP_AUTH',
                'AuthParameters' => [
                    'USERNAME' => $username,
                    'PASSWORD' => $password,
                ],
                'ClientId'       => getenv(AWS_COGNITO_APP_CLIENT_ID),
                'UserPoolId'     => getenv(AWS_COGNITO_USER_POOL_ID),
                ]
            );
        } catch (CognitoIdentityProviderException $exception) {
            switch ($exception->getAwsErrorMessage()) {
                case 'Incorrect username or password.':
                    return $user = new WP_Error('denied', __('ERROR: Incorrect username or password.', 'default'));
                case 'User does not exist.':
                    return $user = new WP_Error('denied', __('ERROR: Username not found.', 'default'));
                default:
                    return new WP_Error('denied', __('ERROR: Unknown authentication error.', 'default'));
            }
        }

        $awsCognitoUser = $this->cognitoIdpClient->getUser(
            [
                'AccessToken' => $authResult->get('AuthenticationResult')['AccessToken'],
            ]
        );

        $userdata = WP_User::get_data_by('slug', $awsCognitoUser->get('Username'));

        return new WP_User($userdata->ID);
    }

    public function run()
    {
        remove_action('authenticate', 'wp_authenticate_username_password', 20);
        add_filter('authenticate', [$this, 'authenticateWithAwsCognito'], 40, 3);
    }
}
