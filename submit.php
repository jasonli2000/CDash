<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id$
  Language:  PHP
  Date:      $Date$
  Version:   $Revision$

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
//error_reporting(0); // disable error reporting

/** Adding some PHP include path */
$path = dirname(__FILE__);
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

// Open the database connection
include("cdash/config.php");
require_once("cdash/pdo.php");
include("cdash/do_submit.php");
include("cdash/clientsubmit.php");

$db = pdo_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
pdo_select_db("$CDASH_DB_NAME",$db);
set_time_limit(0);

// Send to the client submit
client_submit($fp, $projectid);

$file_path='php://input';
//$file_path='backup/coverage/CoverageLog.xml';
$fp = fopen($file_path, 'r');

$projectname = $_GET["project"];
$projectid = get_project_id($projectname);

// If not a valid project we return
if($projectid == -1)
  {
  echo "Not a valid project";
  add_log('Not a valid project. projectname: ' . $projectname, 'global:submit.php');
  exit();
  }

// If the submission is asynchronous we store in the database
if($CDASH_ASYNCHRONOUS_SUBMISSION)
  {
  do_submit_asynchronous($fp, $projectid);
  }
else  
  {
  do_submit($fp, $projectid);
  }
?>
