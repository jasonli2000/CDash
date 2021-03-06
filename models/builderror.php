<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: builderror.php 3044 2011-11-06 18:59:58Z jjomier $
  Language:  PHP
  Date:      $Date: 2011-11-06 18:59:58 +0000 (Sun, 06 Nov 2011) $
  Version:   $Revision: 3044 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
/** BuildError */
class BuildError
{
  var $Type;
  var $LogLine;
  var $Text;
  var $SourceFile;
  var $SourceLine;
  var $PreContext;
  var $PostContext;
  var $RepeatCount;
  var $BuildId;

  // Insert in the database (no update possible)
  function Insert()
    {
    if(!$this->BuildId)
      {
      echo "BuildError::Insert(): BuildId not set<br>";
      return false;
      }

    $text = pdo_real_escape_string($this->Text);

    if(strlen($this->PreContext) == 0)
      {
      $precontext = "NULL";
      }
    else
      {
      $precontext = "'".pdo_real_escape_string($this->PreContext)."'";
      }

    if(strlen($this->PostContext) == 0)
      {
      $postcontext = "NULL";
      }
    else
      {
      $postcontext = "'".pdo_real_escape_string($this->PostContext)."'";
      }

    if(empty($this->SourceLine))
      {
      $this->SourceLine = 0;
      }
    if(empty($this->RepeatCount))
      {
      $this->RepeatCount = 0;
      }

    $crc32 = 0;
    // Compute the crc32
    if($this->SourceLine==0)
      {
      $crc32 = crc32($text); // no need for precontext or postcontext, this doesn't work for parallel build
      }
    else
      {
      $crc32 = crc32($text.$this->SourceFile.$this->SourceLine); // some warning can be on the same line
      }

    $query = "INSERT INTO builderror (buildid,type,logline,text,sourcefile,sourceline,precontext,
                                      postcontext,repeatcount,newstatus,crc32)
              VALUES (".qnum($this->BuildId).",".qnum($this->Type).",".qnum($this->LogLine).",'$text','$this->SourceFile',".qnum($this->SourceLine).",
              ".$precontext.",".$postcontext.",".qnum($this->RepeatCount).",0,".qnum($crc32).")";
    if(!pdo_query($query))
      {
      add_last_sql_error("BuildError Insert",0,$this->BuildId);
      return false;
      }
    return true;
    } // end insert
}
?>
