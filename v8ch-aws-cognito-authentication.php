<?php
/**
 * Author:      Robert Pratt
 * Author URI:  https://www.v8ch.com/
 * Description: Authenticates against AWS Cognito.
 * Domain Path: /languages
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 * Plugin Name: V8CH AWS Cognito Authentication
 * Plugin URI:  https://github.com/V8CH/aws-cognito-authentication
 * Text Domain: aws-cognito-authentication
 * Version:     0.0.1
 *
 * @category Authentication
 * @package  declaration
 * @author   Robert Pratt <bpong@v8ch.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://github.com/V8CH/aws-cognito-authentication
 */

use V8CH\WordPress\AWSCognitoAuthentication\Plugin;

require_once 'vendor/autoload.php';

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'V8CH_AWS_COGNITO_AUTHENTICATION_VERSION', '0.0.1' );

/**
 * Start the plugin.
 *
 * @since    0.0.1
 */
function run_v8ch_aws_cognito_authentication() {

	$plugin = new Plugin();
	$plugin->run();

}
run_v8ch_aws_cognito_authentication();
