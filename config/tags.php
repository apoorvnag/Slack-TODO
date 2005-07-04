<?php
//////////////////////////////////////////////////////////////////////////
// + $Id$
// +------------------------------------------------------------------+ //
// + Cake PHP : Rapid Development Framework <http://www.cakephp.org/> + //
// + Copyright: (c) 2005, Cake Authors/Developers                     + //
// +------------------------------------------------------------------+ //
// + Licensed under The MIT License                                   + //
//////////////////////////////////////////////////////////////////////////

/**
 * In this file, you can set up 'templates' for every tag generated by the tag
 * generator.
 * 
 * @package cake
 * @subpackage cake.config
 */

/**
 * Tag template for a link. 
 */
define('TAG_LINK', '<a href="%s"%s>%s</a>');

/**
 * Tag template for a mailto: link. 
 */
define('TAG_MAILTO', '<a href="mailto:%s"%s>%s</a>');

/**
 * Tag template for opening form tag. 
 */
define('TAG_FORM', '<form %s>');

/**
 * Tag template for an input type='text' tag. 
 */
define('TAG_INPUT',			'<input name="data[%s]" %s/>');

/**
 * Tag template for an input type='hidden' tag. 
 */
define('TAG_HIDDEN',			'<input type="hidden" name="data[%s]" %s/>');

/**
 * Tag template for a textarea tag. 
 */
define('TAG_AREA',			'<textarea name="data[%s]"%s>%s</textarea>');

/**
 * Tag template for a input type='checkbox ' tag. 
 */
define('TAG_CHECKBOX',		'<label for="tag_%s"><input type="checkbox" name="data[%s]" id="tag_%s" %s/>%s</label>');

/**
 * Tag template for a input type='radio' tag. 
 */
define('TAG_RADIOS', 		'<label for="tag_%s"><input type="radio" name="data[%s]" id="tag_%s" %s/>%s</label>');

/**
 * Tag template for a select opening tag. 
 */
define('TAG_SELECT_START', '<select name="data[%s]"%s>');

/**
 * Tag template for an empty select option tag. 
 */
define('TAG_SELECT_EMPTY', '<option value=""%s></option>');

/**
 * Tag template for a select option tag. 
 */
define('TAG_SELECT_OPTION','<option value="%s"%s>%s</option>');

/**
 * Tag template for a closing select tag. 
 */
define('TAG_SELECT_END',	'</select>');

/**
 * Tag template for a password tag. 
 */
define('TAG_PASSWORD',		'<input type="password" name="data[%s]" %s/>');

/**
 * Tag template for a file input tag. 
 */
define('TAG_FILE',			'<input type="file" name="%s" %s/>');

/**
 * Tag template for a submit button tag. 
 */
define('TAG_SUBMIT',			'<input type="submit" %s/>');

/**
 * Tag template for an image tag. 
 */
define('TAG_IMAGE',			'<img src="%s" alt="%s" %s/>');

/**
 * Tag template for a table header tag. 
 */
define('TAG_TABLE_HEADER',	'<th%s>%s</th>');

/**
 * Tag template for table headers row tag. 
 */
define('TAG_TABLE_HEADERS','<tr%s>%s</tr>');

/**
 * Tag template for a table cell tag. 
 */
define('TAG_TABLE_CELL',	'<td%s>%s</td>');

/**
 * Tag template for a table row tag. 
 */
define('TAG_TABLE_ROW',		'<tr%s>%s</tr>');

/**
 * Tag template for a CSS link tag. 
 */
define('TAG_CSS',          '<link rel="%s" type="text/css" href="%s" />');

/**
 * Tag template for a charset meta-tag. 
 */
define('TAG_CHARSET',      '<meta http-equiv="Content-Type" content="text/html; charset=%s" />');

/**
 * Tag template for inline JavaScript.
 */
define('TAG_JAVASCRIPT', '<script language="javascript" type="text/javascript">%s</script>');

/**
 * Tag template for included JavaScript.
 */
define('TAG_JAVASCRIPT_INCLUDE', '<script language="javascript" type="text/javascript" src="%s"></script>');

?>
