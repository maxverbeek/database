<?php
/**
 * @author     Sibren Talens  <sibrentalens@gmail.com>
 * @copyright  2014-2015      Sibren Talens
 * @license    Apache license http://sibrentalens.com/license
 * @link       GitHub         https://github.com/SibrenTalens/database
 */

// Include the Database class
require 'Database.php';

//////////////////
// INITIALIZING //
//////////////////
// Create a new instance
$db = new Database(
	'test',        // The name of the database
	'root',        // The username
	'root',        // The password
	'127.0.0.1',   // The database host, optional
	[]             // Options passed while connecting, optional
);

// Echo an open pre tag so the prints look fancy
echo '<pre>';

////////////////////
// SELECTING DATA //
////////////////////
// Query the password of 'Bill'
// Arguments:
//   * Table
//   * A conditon
//   * What column to select
//   * Limit the result
// Return:
//     Array with results
$get = $db->get('users', 'username = Bill', 'password', true);

// Fetch the results of the query
// And print them
var_dump($get->result());

////////////////////
// INSERTING DATA //
////////////////////
// Query the data
// Arguments:
//   * Table
//   * The data to insert
// Return:
//     Bool status

// Create an array with the data to insert
$data = [
	'username' => 'Steve',         // The key is the database column, the value is the value
	'password' => md5('password'), // Never encrypt with md5, nor use 'password' as a password
	'role' => 0
];

$insert = $db->insert('users', $data);
var_dump($insert);

///////////////////
// UPDATING DATA //
///////////////////
// Update the password of Steve
// Arguments:
//   * Table
//   * A conditon
//   * The column and value to edit
// Return:
//     Bool status
$update = $db->update('users', 'username = Steve', ['role' => 1]);
var_dump($update);

///////////////////
// DELETING DATA //
///////////////////
// Delete The user Steve
// Arguments:
//   * Table
//   * A conditon
// Return:
//     Bool status
$delete = $db->delete('users', 'username = Steve');
var_dump($delete);

//////////
// MISC //
//////////
// Select some data from the table for test purposes
$test = $db->get('users');

// Return the number of rows fetched
var_dump($test->count());

// Return the first result
var_dump($test->first());

// Return true if any errors
var_dump($test->error());

// Close the pre tag
echo '</pre>';
