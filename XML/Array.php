<?php
/**
 * XML_Array
 *
 * Copyright (c) 2011, Nicolas Thouvenin
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the author nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE REGENTS AND CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

/**
 * An XML to Array PHP converter that implents the Google XML/JSON mapping 
 *
 * http://code.google.com/apis/gdata/docs/json.html
 *
 * @package   XML_Array
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2011 Nicolas Thouvenin
 * @license   http://opensource.org/licenses/bsd-license.php BSD Licence
 */
class XML_Array
{
    static public $xml_version = '1.0';
    static public $xml_encoding = 'UTF-8';
    static public $xml_indent = true;
    static public $default_tag_name = 'row';
    static public $text_object = array('$t', '_t', '_text', '#text');
    static public $comment_object = array('$c', '_c', '_comment', '#comment');
    static public $special_attributes = array(
        'xml:id'    => array('xmllang', 'xml:lang', 'xml$lang'),
        'xml:space' => array('xmlspace', 'xml:space', 'xml$space'),
        'xml:id'    => array('xmlid', 'xml:id', 'xml$id'),
        'xml:idref' => array('xmlidref', 'xml:idref', 'xml$idref'),
    );

    /**
     * Array to XML
     * @param array
     * @param XMLWriter
     */
    static public function export($array, $xw = null) 
    {
        if (is_null($xw)) {
            $start = true;
            $xw = new XMLWriter;
            $xw->openMemory();
            $xw->startDocument(self::$xml_version, self::$xml_encoding);
            $xw->setIndent(self::$xml_indent);
        }
        else {
            $start = false;
        }
        foreach($array as $key => $value)
        {
            if (is_string($key) and !is_array($value)) {
                if (in_array($key, self::$text_object)) {
                    $xw->text($value);
                }
                elseif (in_array($key, self::$comment_object)) {
                    $xw->writeComment($value);
                }
                else {
                    $found  = false;
                    foreach(self::$special_attributes as $name => $aliases) {
                        if (in_array($key, $aliases)) {
                            $found  = true;
                            $xw->writeAttribute($name, $value);
                            break;
                        }
                    }
                    if (!$found) {
                        $xw->writeAttribute($key, $value);
                    }
                }
            }
            elseif (is_string($key) and is_array($value) and !is_numeric(key($value))) {
                $xw->startElement($key);
                self::export($value, $xw);
                $xw->endElement();
            }
            elseif (is_string($key) and is_array($value) and is_numeric(key($value))) {
                foreach($value as $k => $v) {
                    $xw->startElement($key);
                    self::export($v, $xw);
                    $xw->endElement();
                }
            }
            elseif (is_numeric($key)  and !is_array($value)) {
                $xw->writeCData($value);
            }
            elseif (is_numeric($key)  and is_array($value)) {
                $xw->startElement(self::$default_tag_name);
                self::export($value, $xw);
                $xw->endElement();
            }
        }
        if ($start) {
            $xw->endDocument();
            return $xw->outputMemory();
        }
    }

    /**
     * XML to Array 
     * @param string
     */
    public static function import($xml, $options = null)
    {
        $r = new XMLReader();
        $r->xml($xml, null, is_null($options) ? LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG | LIBXML_NONET | LIBXML_NSCLEAN : $options);
        $ret = self::_import($r);
        $r->close();
        return $ret;
    }

    protected static function _import($xr)
    {
        $previous = null;
        $tree = array();
        while($xr->read()) {
            if ($xr->nodeType ===  XMLReader::END_ELEMENT) {
                return $tree;
            }
            elseif ($xr->nodeType === XMLReader::ELEMENT) {
                $name = $xr->name;
                $node = array();
                $isempty = $xr->isEmptyElement;

                if ($xr->hasAttributes) {
                    while($xr->moveToNextAttribute()) {
                        $node[$xr->name] = $xr->value;
                    }
                }
                if (!$isempty) {
                    $content = self::_import($xr);
                    if (is_string($content)) {
                        $node['_t'] = $content;
                    }
                    elseif (is_array($content)) {
                        $node += $content;
                    }
                }
                if (isset($tree[$name]) and is_array($tree[$name])) {
                    if (is_integer(key($tree[$name]))) {
                        $tree[$name][] = $node;
                    }
                    else {
                        $tmp = $tree[$name];
                        $tree[$name] = array($tmp, $node);
                    }
                }
                elseif (isset($tree[$name])) {
                    $tree[$name] .= $node;
                }
                else {
                    $tree[$name] = $node;
                }

                $previous =& $node;
            }
            elseif (is_string($previous)) {
                $previous .= $xr->value;
            }
            else {
                $name = $xr->name;
                $node = $xr->value;
                if (isset($tree[$name]) and is_array($tree[$name])) {
                    if (is_integer(key($tree[$name]))) {
                        $tree[$name][] = $node;
                    }
                    else {
                        $tmp = $tree[$name];
                        $tree[$name] = array($tmp, $node);
                    }
                }
                elseif (isset($tree[$name])) {
                    $tree[$name] .= $node;
                }
                else {
                    $tree[$name] = $node;
                }
                $previous = $node;
            }
        }
        return $tree;
    }
}
