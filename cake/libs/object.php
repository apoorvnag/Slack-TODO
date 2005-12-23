<?php
/* SVN FILE: $Id$ */

/**
 * Object class, allowing __construct and __destruct in PHP4.
 *
 * Also includes methods for logging and the special method RequestAction,
 * to call other Controllers' Actions from anywhere.
 *
 * PHP versions 4 and 5
 *
 * CakePHP :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright (c) 2005, Cake Software Foundation, Inc. 
 *                     1785 E. Sahara Avenue, Suite 490-204
 *                     Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright    Copyright (c) 2005, Cake Software Foundation, Inc.
 * @link         http://www.cakefoundation.org/projects/info/cakephp CakePHP Project
 * @package      cake
 * @subpackage   cake.cake.libs
 * @since        CakePHP v 0.2.9
 * @version      $Revision$
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
  * Included libraries.
  */
uses('log');

/**
 * Object class, allowing __construct and __destruct in PHP4.
 *
 * Also includes methods for logging and the special method RequestAction,
 * to call other Controllers' Actions from anywhere.
 *
 * @package    cake
 * @subpackage cake.cake.libs
 * @since      CakePHP v 0.2.9
 */
class Object
{

/**
 * Log object
 *
 * @var object
 */
   var $_log = null;

/**
 * A hack to support __construct() on PHP 4
 * Hint: descendant classes have no PHP4 class_name() constructors,
 * so this constructor gets called first and calls the top-layer __construct()
 * which (if present) should call parent::__construct()
 *
 * @return Object
 */
   function Object()
   {
      $args = func_get_args();
      register_shutdown_function(array(&$this, '__destruct'));
      call_user_func_array(array(&$this, '__construct'), $args);
   }

/**
 * Class constructor, overridden in descendant classes.
 */
   function __construct()
   {
   }

/**
 * Class destructor, overridden in descendant classes.
 */
   function __destruct()
   {
   }

/**
 * Object-to-string conversion.
 * Each class can override this method as necessary.
 *
 * @return string The name of this class
 */
   function toString()
   {
      return get_class($this);
   }

/**
 * Calls a controller's method from any location.
 *
 * @param string $url  URL in the form of Cake URL ("/controller/method/parameter")
 * @param array $extra If array includes the key "render" it sets the AutoRender to true.
 * @return boolean  Success
 */
    function requestAction ($url, $extra = array())
    {
        if(in_array('render', $extra))
        {
            $extra['render'] = 0;
        }
        else
        {
          $extra['render'] = 1;
        }
        $dispatcher =& new Dispatcher();
        return $dispatcher->dispatch($url, $extra);
    }

/**
 * API for logging events.
 *
 * @param string $msg Log message
 * @param int $type Error type constant. Defined in /libs/log.php.
 */
   function log ($msg, $type=LOG_ERROR)
   {
      if (is_null($this->_log))
      {
         $this->_log = new Log ();
      }

      switch ($type)
      {
         case LOG_DEBUG:
            return $this->_log->write('debug', $msg);
         default:
            return $this->_log->write('error', $msg);
      }
   }

/**
 * Renders the Missing Controller web page.
 *
 */
   function missingController()
   {
       $this->autoLayout = true;
       $this->pageTitle = 'Missing Controller';
       $this->render('../errors/missingController');
       exit();
   }

/**
 * Renders the Missing Action web page.
 *
 */
   function missingAction()
   {
       $this->autoLayout = true;
       $this->pageTitle = 'Missing Method in Controller';
       $this->render('../errors/missingAction');
       exit();
   }

/**
 * Renders the Private Action web page.
 *
 */
   function privateAction()
   {
       $this->autoLayout = true;
       $this->pageTitle = 'Trying to access private method in class';
       $this->render('../errors/privateAction');
       exit();
   }

/**
 * Renders the Missing View web page.
 *
 */
   function missingView()
   {
      //We are simulating action call below, this is not a filename!
      $this->autoLayout = true;
      $this->missingView = $this->name;
      $this->pageTitle = 'Missing View';
      $this->render('../errors/missingView');
   }

/**
 * Renders the Missing Database web page.
 *
 */
    function missingDatabase()
    {
        $this->autoLayout = true;
        $this->pageTitle = 'Scaffold Missing Database Connection';
        $this->render('../errors/missingScaffolddb');
        exit();
    }

/**
 * Renders the Missing Table web page.
 *
 */
    function missingTable($tableName)
    {
	    $error =& new Controller();
	    $error->constructClasses();
        $error->missingTable = $this->table;
        $error->missingTableName = $tableName;
        $error->pageTitle = 'Missing Database Table';
        $error->render('../errors/missingTable');
        exit();
    }

/**
 * Renders the Missing Table web page.
 *
 */
    function missingConnection()
    {
        $error =& new Controller();
	    $error->constructClasses();
        $error->missingConnection = $this->name;
        $error->autoLayout = true;
        $error->pageTitle = 'Missing Database Connection';
        $error->render('../errors/missingDatabase');
        exit();
    }

/**
 * Renders the Missing Helper file web page.
 *
 */
    function missingHelperFile($file)
    {
        $this->missingHelperFile = $file;
        $this->missingHelperClass = Inflector::camelize($file) . "Helper";
        $this->pageTitle = 'Missing Helper File';
        $this->render('../errors/missingHelperFile');
        exit();
    }

/**
 * Renders the Missing Helper class web page.
 *
 */
    function missingHelperClass($class)
    {
        $this->missingHelperClass = Inflector::camelize($class) . "Helper";
        $this->missingHelperFile = Inflector::underscore($class);
        $this->pageTitle = 'Missing Helper Class';
        $this->render('../errors/missingHelperClass');
        exit();
    }
}

?>