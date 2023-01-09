<?php
 error_reporting(E_ALL ^ E_NOTICE);
 putenv('LDAPTLS_REQCERT=never');
 require_once( 'config.php' );

 require( dirname(__FILE__) . '/functions/classes/class.Common.php' );          // Class common - common functions
 require( dirname(__FILE__) . '/functions/classes/class.Mail.php' );            // Class for Mailing
 require( dirname(__FILE__) . '/functions/adLDAP/src/adLDAP.php');              // ad ldap

 // regext for user name from LDAP CN=xxx, xxx
 $re = '/^CN=(.*?),OU/m';

 $db_connect = new PDO(sprintf("mysql:host=%s;dbname=%s", $db['host'], $db['name']), $db['user'], $db['pass']);

 // get AD parameters
 // *************************************************************************
 $query = "SELECT id, params FROM usersAuthMethod WHERE type = 'AD' LIMIT 1";
 $result = $db_connect->query($query);
 $rows = $result->fetchAll(PDO::FETCH_ASSOC);

 // get authMethod id for AD
 // *************************************************************************
 $auth_type_id = $rows[0]['id'];

 // prepare ldap connection parameters
 // *************************************************************************
 $ldap_config = json_decode($rows[0]['params'], true);
 $ldap_config['domain_controllers'] = explode(",", str_replace(" ", "", $ldap_config['domain_controllers']));

 $ldap_config['admin_username'] = sprintf("%s", $ldap_config['adminUsername']);
 $ldap_config['admin_password'] = $ldap_config['adminPassword'];

 try { $ad_ldap = new adLDAP($ldap_config); }
 catch (adLDAPException $e) {
    echo $e;
    exit();
 }

 $query = sprintf("SELECT id, authMethod, username, groups, real_name, role FROM users WHERE authMethod = %d AND groups NOT LIKE (SELECT CONCAT('%%\"',g_id,'\":\"',g_id,'\"%%')AS tmp_group FROM userGroups WHERE g_name = 'ServiceAccounts')", $auth_type_id);

 // $query = sprintf("SELECT id, authMethod, username, groups, real_name, role FROM users WHERE authMethod = %d", $auth_type_id);
 $result = $db_connect->query($query);
 $rows = $result->fetchAll(PDO::FETCH_ASSOC);

 $ipam_users = array();
 foreach ($rows as $row)
 {
  $ipam_users[$row['username']] = [ "groups" => $row['groups'], "role" => $row['role'] ];
 }

 // get groups from DB
 // *************************************************************************

 $query = "SELECT g_id, g_name FROM userGroups WHERE g_name LIKE 'ad_%'";
 $result = $db_connect->query($query);
 $rows = $result->fetchAll(PDO::FETCH_ASSOC);

 // retrive users for each group from DB
 // *************************************************************************

 foreach ($rows as $row)
 {
  print sprintf("group name:%s\n", $row['g_name']);

  $ad_group = preg_replace('/^(ad_)/','', $row['g_name']);
  $group_id = $row['g_id'];

  // get goup members
  $group_members = $ad_ldap->group()->infoCollection($ad_group, array('member'));

  foreach ($group_members->member as $user)
  {
   $tmp_group = sprintf('{"%d":"%d"}', $group_id, $group_id);

   $result = ldap_search($ad_ldap->getLdapConnection(), $user, '(cn=*)', array('samaccountname', 'mail'));
   $userinfo = ldap_get_entries($ad_ldap->getLdapConnection(), $result);
   $user_account = $userinfo[0]['samaccountname'][0];
   $user_email = $userinfo[0]['mail'][0];

   preg_match_all($re, $user, $matches, PREG_SET_ORDER, 0);
   $user_cn = (str_replace('\,', '', $matches[0][1]));

   if (! in_array($user_account, array_keys($ipam_users)) )
   {
    // add new users
    // *************************************************************************
    print sprintf(" - add : %s\n", $user_cn);
    $query_exec[] = sprintf("INSERT INTO users(username, real_name, email, groups, authMethod, role) VALUES( '%s', '%s', '%s', '%s', %d, 'User')\n",
        $user_account,
        $user_cn,
        $user_email,
        $tmp_group,
        $auth_type_id
    );
   }
   else
   {
     // update user group
     // *************************************************************************
     if ( ($ipam_users[$user_account]['role'] == 'User' ) && ($ipam_users[$user]['groups'] != $tmp_group) )
     {
      $query_exec[] = sprintf("UPDATE users SET groups='%s' WHERE username = '%s'\n", $tmp_group, $user_account);
      print sprintf(" - update : %s\n", $user_cn);
     }
     $ipam_users[$user_account]['ad'] = True;
   }
  }
 }

 // clean up / remove users
 // *************************************************************************
 foreach (array_keys($ipam_users) as $user)
 {
  if (!isset($ipam_users[$user]['ad']))
  {
   $query_exec[] = sprintf("UPDATE users SET disabled = 'Yes' WHERE username = '%s'", $user);
   print sprintf(" - disable : %s\n", $user);
  }
 }

 // update database
 // *************************************************************************
 try {
  $db_connect->beginTransaction();
   foreach ($query_exec as $query) { $db_connect->exec($query); }
  $db_connect->commit();
 } catch(Exception $e) {
    $db_connect->rollback();
    throw $e;
}
?>
