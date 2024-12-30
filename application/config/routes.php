<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
| $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

$route['404_override'] = 'custom404';

$route['default_controller'] = 'api';

// AUTHENTICATION
$route['api/generate-token'] = 'api/generate_token';

// USER
$route['api/get-users'] = 'api/get_users';
$route['api/get-user-details'] = 'api/get_user_details';
$route['api/send-credits'] = 'api/send_credits';
$route['api/verify-user-pin'] = 'api/verify_user_pin';
$route['api/update-user-pin'] = 'api/update_user_pin';
$route['api/send-otp'] = 'api/send_otp';
$route['api/verify-otp'] = 'api/verify_otp';

$route['api/request-update-commission-percent'] = 'api/request_update_commission_percent';

// COMMISSION REQUEST
$route['api/get-commission-requests'] = 'api/get_commission_requests';
$route['api/get-commission-request-details'] = 'api/get_commission_request_details';
$route['api/request-update-commission-percent'] = 'api/request_update_commission_percent';
$route['api/update-commission-request-status'] = 'api/update_commission_request_status';

// TRANSACTION
$route['api/get-transaction-details'] = 'api/get_transaction_details';
$route['api/get-transactions'] = 'api/get_transactions';
$route['api/get-transactions-with-date-range'] = 'api/get_transactions_with_date_range';

// CREDIT HISTORY
$route['api/insert-credit-history'] = 'api/insert_credit_history';
$route['api/get-credit-histories-with-date-range'] = 'api/get_credit_histories_with_date_range';

// ACCOUNT HISTORY
$route['api/insert-account-history'] = 'api/insert_account_history';
$route['api/get-account-histories-with-date-range'] = 'api/get_account_histories_with_date_range';

// NOTIFICATION
$route['api/insert-notification'] = 'api/insert_notification';
$route['api/update-notification-status'] = 'api/update_notification_status';
$route['api/update-all-notification-status'] = 'api/update_all_notification_status';
$route['api/get-notifications-with-date-range'] = 'api/get_notifications_with_date_range';

// NETWORK
$route['api/get-networks'] = 'api/get_networks';
$route['api/get-network-details'] = 'api/get_network_details';
$route['api/get-network-downlines'] = 'api/get_network_downlines';

$route['api/check-network-downline'] = 'api/check_network_downline';

// MISCELLANEOUS
$route['api/get-endpoints'] = 'api/get_endpoints';
$route['api/check-endpoint-required-data'] = 'api/check_endpoint_required_data';

// MAIN COMMISSION
  // SUPER AGENT
  $route['api/get-super-agent-master-agents'] = 'api/get_super_agent_master_agents';
  $route['api/get-super-agent-master-agent-agents'] = 'api/get_super_agent_master_agent_agents';
  $route['api/get-super-agent-master-agent-players'] = 'api/get_super_agent_master_agent_players';
  $route['api/get-super-agent-master-agent-player-details'] = 'api/get_super_agent_master_agent_player_details';
  $route['api/get-super-agent-master-agent-agent-players'] = 'api/get_super_agent_master_agent_agent_players';
  $route['api/get-super-agent-master-agent-agent-player-details'] = 'api/get_super_agent_master_agent_agent_player_details';
  $route['api/get-super-agent-players'] = 'api/get_super_agent_players';
  $route['api/get-super-agent-player-details'] = 'api/get_super_agent_player_details';

  // MASTER AGENT
  $route['api/get-master-agent-agents'] = 'api/get_master_agent_agents';
  $route['api/get-master-agent-agent-players'] = 'api/get_master_agent_agent_players';
  $route['api/get-master-agent-agent-player-details'] = 'api/get_master_agent_agent_player_details';
  $route['api/get-master-agent-players'] = 'api/get_master_agent_players';
  $route['api/get-master-agent-player-details'] = 'api/get_master_agent_player_details';

  // AGENT
  $route['api/get-agent-players'] = 'api/get_agent_players';
  $route['api/get-agent-player-details'] = 'api/get_agent_player_details';

// THIRD PARTY GAME
$route['api/third-party-game-generate-signature'] = 'api/third_party_game_generate_signature';
  
  


  
  
























