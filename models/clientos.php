<?php
/*=========================================================================
 Program:   CDash - Cross-Platform Dashboard System
 Module:    $Id: clientos.php 3283 2012-11-01 17:06:38Z david.cole $
 Language:  PHP
 Date:      $Date: 2012-11-01 17:06:38 +0000 (Thu, 01 Nov 2012) $
 Version:   $Revision: 3283 $
 Copyright (c) 2002 Kitware, Inc.  All rights reserved.
 See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.
 This software is distributed WITHOUT ANY WARRANTY; without even
 the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 PURPOSE.  See the above copyright notices for more information.
 =========================================================================*/
class ClientOS
{
  var $Id;
  var $Name;
  var $Version;
  var $Bits;
  var $Platforms;
  var $OperatingSystems;
    
  function __construct()
    {
    $this->Platforms = array(
      0 => "Unknown",
      1 => "Windows",
      2 => "Linux",
      3 => "Mac",
      );

    $this->OperatingSystems = array(
      0 => "Unknown",
      1 => "Vista",
      2 => "7",
      3 => "Ubuntu",
      4 => "Debian",
      5 => "Fedora",
      6 => "CentOS",
      7 => "Tiger",
      8 => "Leopard", 
      9 => "SnowLeopard",
      10 => "XP",
      11 => "NT",
      12 => "2000",
      13 => "Lion",
      14 => "MountainLion",
      );
    }

  /** Get name */
  function GetName()
    {
    if(!$this->Id)
      {
      add_log("ClientOS::GetName()","Id not set");
      return;
      }
    $name = pdo_query("SELECT name FROM client_os WHERE id=".qnum($this->Id));
    $row = pdo_fetch_array($name);
    return $row[0];
    }
  
  /** Get bits */
  function GetBits()
    {
    if(!$this->Id)
      {
      add_log("ClientOS::GetBits()","Id not set");
      return;
      }
    $name = pdo_query("SELECT bits FROM client_os WHERE id=".qnum($this->Id));
    $row = pdo_fetch_array($name);
    return $row[0];
    }
    
  /** Get version */
  function GetVersion()
    {
    if(!$this->Id)
      {
      add_log("ClientOS::GetVersion()","Id not set");
      return;
      }
    $name = pdo_query("SELECT version FROM client_os WHERE id=".qnum($this->Id));
    $row = pdo_fetch_array($name);
    return $row[0];
    }
       
    
  /** Save a site */  
  function Save()
    {
    $name = $this->GetNameFromPlatform($this->Name);
    $version = $this->GetNameFromVersion($this->Version);
    
    if(strlen($name) == 0)
      {  
      return false;
      }
    
    // Check if the name and bits system already exists
    $query = pdo_query("SELECT id FROM client_os WHERE name='".$name."' AND version='".$version."' AND bits='".$this->Bits."'");
    if(pdo_num_rows($query) == 0)
      {
      $sql = "INSERT INTO client_os (name,version,bits) 
              VALUES ('".$name."','".$version."','".$this->Bits."')";
      pdo_query($sql);
      $this->Id = pdo_insert_id('client_os');
      add_last_sql_error("ClientOS::Save()");
      }
    else // update
      {
      $query_array = pdo_fetch_array($query);
      $this->Id = $query_array['id'];
      }
    }   // end Save
  
  /** Get all the OS */  
  function GetAll()
    {
    $ids = array();
    $sql = "SELECT id FROM client_os ORDER BY name";
    $query = pdo_query($sql);
    while($query_array = pdo_fetch_array($query))
      {
      $ids[] = $query_array['id'];
      }
    return $ids;    
    }    

  /** Get the OS id from the description */  
  function GetOS($name,$version='',$bits='')
    {
    $sql = "SELECT id FROM client_os WHERE ";
    $ids = array();
    $firstarg = true;
    if($name!='')
      {  
      $name = pdo_real_escape_string($name); 
      $sql .= " name='".$name."'"; 
      $firstarg = false;  
      }
    
    if($version!='')
      {
      if(!$firstarg)
        {
        $sql .= " AND ";  
        }  
      $version = pdo_real_escape_string($version);  
      $sql .= " version='".$version."'"; 
      $firstarg = false;  
      }
      
    if($bits!='')
      {
      if(!$firstarg)
        {
        $sql .= " AND ";  
        }  
      $bits = pdo_real_escape_string($bits);  
      $sql .= " bits='".$bits."'"; 
      $firstarg = false;  
      }
      
    $query = pdo_query($sql);
    while($query_array = pdo_fetch_array($query))
      {
      $ids[] = $query_array['id'];
      }
    return $ids;    
    } // end GetOS  
    
  /** Get the platform name */  
  function GetPlatformFromName($name)
    {
    $key = array_search($name, $this->Platforms);
    if($key !== false)
      {
      return $key;  
      }
    return 0;  
    }

  /** Get the OS name */  
  function GetNameFromPlatform($platform)
    {
    return $this->Platforms[$platform];  
    }

  /** Get the platform name */  
  function GetVersionFromName($name)
    {
    $key = array_search($name, $this->OperatingSystems);
    if($key !== false)
      {
      return $key;  
      }
    return 0; 
    }
    
  /** Get the OS name */  
  function GetNameFromVersion($platform)
    {
    return $this->OperatingSystems[$platform];
    }  
}
?>
