;<?php die() ?>
; SVN FILE: $Id$
;/**
; * Short description for file.
; * 
; * In this file, you can set up 'templates' for every tag generated by the tag
; * generator.
; *
; * PHP versions 4 and 5
; *
; * CakePHP :  Rapid Development Framework <http://www.cakephp.org/>
; * Copyright (c) 2005, CakePHP Authors/Developers
; *
; * Author(s): Michal Tatarynowicz aka Pies <tatarynowicz@gmail.com>
; *            Larry E. Masters aka PhpNut <nut@phpnut.com>
; *            Kamil Dzielinski aka Brego <brego.dk@gmail.com>
; *
; *  Licensed under The MIT License
; *  Redistributions of files must retain the above copyright notice.
; *
; * @filesource 
; * @author       CakePHP Authors/Developers
; * @copyright    Copyright (c) 2005, CakePHP Authors/Developers
; * @link         https://trac.cakephp.org/wiki/Authors Authors/Developers
; * @package      cake
; * @subpackage   cake.config
; * @since        CakePHP v 0.2.9
; * @version      $Revision$
; * @modifiedby   $LastChangedBy$
; * @lastmodified $Date$
; * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
; */


; Tag template for a link.
link = "<a href="%s"%s>%s</a>"

; Tag template for a mailto: link. 
mailto = "<a href="mailto:%s"%s>%s</a>"

; Tag template for opening form tag. 
form = "<form %s>"

; Tag template for an input type='text' tag. 
input = "<input name="data[%s][%s]"%s/>"

; Tag template for an input type='textarea' tag
textarea = "<textarea name="data[%s][%s]"%s>%s</textarea>"

; Tag template for an input type='hidden' tag. 
hidden = "<input type="hidden" name="data[%s][%s]"%s/>"

; Tag template for a textarea tag.
textarea = "<textarea name="data[%s][%s]"%s>%s</textarea>"

; Tag template for a input type='checkbox ' tag. 
checkbox = "<input type="checkbox" name="data[%s][%s]" id="tag_%s" %s/>"

; Tag template for a input type='radio' tag.
radio = "<input type="radio" name="data[%s][%s]" id="tag_%s"%s/>"

; Tag template for a select opening tag.
selectStart = "<select name="data[%s][%s]"%s>"

; Tag template for a select opening tag.
selectMultipleStart = "<select name="data[%s][%s][]"%s>"

; Tag template for an empty select option tag.
selectEmpty = "<option value=""%s></option>"

; Tag template for a select option tag.
selectOption = "<option value="%s"%s>%s</option>"

; Tag template for a closing select tag. 
selectEnd = "</select>"

; Tag template for a password tag.
password = "<input type="password" name="data[%s][%s]"%s/>"

; Tag template for a file input tag. 
file = "<input type="file" name="%s"%s/>"

; Tag template for a submit button tag. 
submit = "<input type="submit"%s/>"

; Tag template for an image tag.
image =" <img src="%s"%s/>"

; Tag template for a table header tag. 
tableHeader = "<th%s>%s</th>"

; Tag template for table headers row tag.
tableHeaderRow = "<tr%s>%s</tr>"

; Tag template for a table cell tag. 
tableCell = "<td%s>%s</td>"

; Tag template for a table row tag. 
tableRow = "<tr%s>%s</tr>"

; Tag template for a CSS link tag. 
css = "<link rel="%s" type="text/css" href="%s" />"

; Tag template for a charset meta-tag.
charset = "<meta http-equiv="Content-Type" content="text/html; charset=%s" />"

; Tag template for inline JavaScript.
javascriptBlock = "<script type="text/javascript">%s</script>"

; Tag template for included JavaScript.
javascriptLink = "<script type="text/javascript" src="%s"></script>"