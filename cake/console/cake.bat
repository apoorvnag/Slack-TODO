::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
::
:: Bake is a shell script for running CakePHP bake script
:: PHP versions 4 and 5
::
:: CakePHP(tm) :  Rapid Development Framework (http://cakephp.org)
:: Copyright 2005-2010, Cake Software Foundation, Inc.
::
:: Licensed under The MIT License
:: Redistributions of files must retain the above copyright notice.
::
:: @filesource
:: @copyright		Copyright 2005-2010, Cake Software Foundation, Inc.
:: @link				http://cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
:: @package			cake
:: @subpackage		cake.cake.console
:: @since			CakePHP(tm) v 1.2.0.5012
:: @version			$Revision$
:: @modifiedby		$LastChangedBy$
:: @lastmodified	$Date$
:: @license			http://www.opensource.org/licenses/mit-license.php The MIT License
::
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

:: In order for this script to work as intended, the cake\console\ folder must be in your PATH

@echo.
@echo off

SET app=%0
SET lib=%~dp0

php -q "%lib%cake.php" -working "%CD%" %*

echo.