<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: displayImage.php 3379 2013-10-28 13:15:36Z jjomier $
  Language:  PHP
  Date:      $Date: 2013-10-28 13:15:36 +0000 (Mon, 28 Oct 2013) $
  Version:   $Revision: 3379 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
include("cdash/config.php");
require_once("cdash/pdo.php");

$imgid = pdo_real_escape_numeric($_GET["imgid"]);
// Checks
if(empty($imgid) || !is_numeric($imgid))
  {
  echo "Not a valid imgid!";
  return;
  }

$db = pdo_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
pdo_select_db("$CDASH_DB_NAME",$db);

$result = pdo_query("SELECT * FROM image WHERE id=$imgid");
$img_array = pdo_fetch_array($result);

switch($img_array["extension"])
  {
  case "image/jpg":
    header("Content-type: image/jpeg");
    break;
  case "image/jpeg":
    header("Content-type: image/jpeg");
    break;
  case "image/gif":
    header("Content-type: image/gif");
    break;
  case "image/png":
    header("Content-type: image/png");
    break;
  default:
    echo "Unknown image type: ";
    echo $img_array["extension"];
    return;
  }

if($CDASH_DB_TYPE == "pgsql")
  {
  $buf = "";
  while(!feof($img_array["img"]))
    {
    $buf .= fread($img_array["img"], 2048);
    }
  $buf = stripslashes($buf);
  }
else
  {
  $buf = $img_array["img"];
  }
echo $buf;
return;
?>
