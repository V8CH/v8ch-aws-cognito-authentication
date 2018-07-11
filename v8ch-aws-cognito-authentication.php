<?php // phpcs:ignore
/**
 * Author:      Robert Pratt
 * Author URI:  https://www.v8ch.com/
 * Description: Authenticates against AWS Cognito.
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 * Plugin Name: V8CH AWS Cognito Authentication
 * Plugin URI:  https://github.com/V8CH/v8ch-aws-cognito-authentication
 * Version:     1.0.0
 *
 * @package aws-cognito-authentication
 * @since   1.0.0
 */

use V8CH\WordPress\AWSCognitoAuthentication\Plugin;

require_once 'vendor/autoload.php';

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

function run_v8ch_aws_cognito_authentication()
{
    $plugin = new Plugin();
    $plugin->run();
}

run_v8ch_aws_cognito_authentication();
