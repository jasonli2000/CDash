<?php
// kwtest library
require_once('kwtest/kw_web_tester.php');
require_once('kwtest/kw_db.php');

class ProjectInDbTestCase extends KWWebTestCase
{
  var $url = null;
  var $db  = null;
  
  function __construct()
    {
    parent::__construct();
    require('config.test.php');
    $this->url = $configure['urlwebsite'];
    $this->db  =& new database($db['type']);
    $this->db->setDb($db['name']);
    $this->db->setHost($db['host']);
    $this->db->setUser($db['login']);
    $this->db->setPassword($db['pwd']);
    }
 
  /** Helper function to login */
  function login()
    {
    $this->clickLink('Login');
    $this->setField('login','simpletest@localhost');
    $this->setField('passwd','simpletest');
    return $this->clickSubmit('Login >>');
    }    
    
  /** Test the creation of the project */
  function testCreateProject()
    {
    $content = $this->connect($this->url);
    if(!$content)
      {
      return;
      }
    $this->login();
    if(!$this->analyse($this->clickLink('[Create new project]')))
      {
      return;
      }
    
    // Create the project
    $this->setField('name','ProjectTest');
    $this->setField('description','This is a project test for cdash');
    $this->setField('public','1');
    $this->clickSubmit('Create Project');
         
    // Make sure the project is in the database
    $query = "SELECT COUNT(*) FROM project";
    $result = $this->db->query($query);
    if($this->db->getType() == "pgsql" && 
        $result[0]['count'] < 1)
      {
      $result = $result[0]['count'];  
      $errormsg = "The result of the query '$query' which is $result"; 
      $errormsg .= "is not the one expected: 1";
      $this->assertEqual($result[0]['count'],'1',$errormsg);
      return;
      }
    else if($this->db->getType() == "mysql" && 
           $result[0]['COUNT(*)'] < 1)
      {
      $result = $result[0]['COUNT(*)']; 
      $errormsg = "The result of the query '$query' which is $result"; 
      $errormsg .= "is not the one expected: 1";
      $this->assertEqual($result[0]['COUNT(*)'],'1',$errormsg);
      return;
      }
    $this->assertText('The project ProjectTest has been created successfully.');
    }
  
  /** Check that the project values in the database are correct */
  function testProjectTestInDatabase()
    {
    $query = "SELECT name,description,public FROM project WHERE name = 'ProjectTest'";
    $result = $this->db->query($query);
    $nameexpected = "ProjectTest";
    $descriptionexpected = "This is a project test for cdash";
    $publicexpected = 1;
    $expected = array('name'        =>  $nameexpected,
                      'description' =>  $descriptionexpected,
                      'public'      =>  $publicexpected);
    $this->assertEqual($result[0],$expected);
    }
  
  /** Test that we can access the project page */
  function testIndexProjectTest()
    {
    $content = $this->get($this->url.'/index.php?project=ProjectTest');
    $this->assertTitle('CDash - ProjectTest');
    }
  
  /** Test the edition of the project */
  function testEditProject()
    {
    $content = $this->connect($this->url);
    $this->assert($content);
    
    $this->login();
    $projectid = $this->db->query("SELECT id FROM project WHERE name = 'ProjectTest'");
    $content = $this->connect($this->url.'/createProject.php?projectid='.$projectid[0]['id']);
    $this->assert($content);
  
    $description = $this->_browser->getField('description');
    $public      = $this->_browser->getField('public');
    $descriptionExpected = 'This is a project test for cdash';
    if(strcmp($description,$descriptionExpected) != 0)
      {
      $this->assertEqual($description,$descriptionExpected);
      return;
      }
    if(strcmp($public,'1') != 0)
      {
      $this->assertEqual($public,'1');
      return;
      }
    $content  = $this->analyse($this->clickLink('CTestConfig.php'));
    $expected = '## This file should be placed in the root directory of your project.';
    if(!$this->findString($content,$expected))
      {
      $this->assertText($content,$expected);
      return;
      }
    $this->back();
    $this->post($this->getUrl(),array('Delete'=>true));
    $headerExpected = "window.location='user.php'";
    $content = $this->_browser->getContent();
    if($this->findString($content,$headerExpected))
      {
      $msg  = "We have well been redirecting to user.php\n";
      $msg .= "after to have deleted ProjectTest\n";
      $this->assertTrue(true,$msg);
      }
    else
      {
      $msg  = "We have not been redirecting to user.php\n";
      $msg .= "The deletion of ProjectTest failed\n";
      $this->assertTrue(false,$msg);
      }
    }
 
  /** Test the deletion of a project */
  function testDeleteProject()
    {
    $content = $this->connect($this->url);
    if(!$content)
      {
      return;
      }
    $this->login();
    if(!$this->analyse($this->clickLink('[Edit project]')))
      {
      return;
      }
    
    // Record the number of projects before
    $result = $this->db->query("SELECT COUNT(*) FROM project");      
    if( $this->db->getType() == "pgsql")
      {
      $countProjectsBefore = $result[0]['count'];
      }
    else
      {
      $countProjectsBefore = $result[0]['COUNT(*)'];
      }
   
    // Delete the project
    $this->clickSubmit('Delete Project');$result[0]['count'];
    
    // Check that it has been deleted correctly
    $result = $this->db->query("SELECT COUNT(*) FROM project");      
    if( $this->db->getType() == "pgsql")
      {
      if($result[0]['count'] != $countProjectsBefore-1)
        {
        $this->fail();
        }
      }
    else
      {
      if($result[0]['COUNT(*)'] != $countProjectsBefore-1)
        {
        $this->fail();
        }
      }
    $this->pass();  
    }
    
}


?>
