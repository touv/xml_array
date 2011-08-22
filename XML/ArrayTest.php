<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker :


require_once 'Array.php';

class XML_ArrayTest extends PHPUnit_Framework_TestCase
{

    function test_io()
    {
        $input = file_get_contents(dirname(__FILE__).'/sample01.xml');
        $array = XML_Array::import($input);
        $output = XML_Array::export($array, XML_Array::START_DOCUMENT | XML_Array::INDENT);
        $this->assertEquals(preg_replace(',\s+,', ' ', $input), preg_replace(',\s+,', ' ', $output));
    }

}
