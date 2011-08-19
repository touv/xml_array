<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker :


require_once 'Array.php';

class XML_ArrayTest extends PHPUnit_Framework_TestCase
{

    function test_io()
    {
        $input = file_get_contents(dirname(__FILE__).'/sample01.xml');
        $array = XML_Array::import($input);
        $output = XML_Array::export($array);
        $this->assertEquals(preg_replace(',\s+,', ' ', $input), preg_replace(',\s+,', ' ', $output));
    }

    function test_misc()
    {
        //        $data = array('truc', 'bidule', 'chouette');
        //        $data = array('root' => array('#text' => 'bidule'));
        //        $data = array('root' => array('machin' => array( array('truc' => 0, '#text' => 'bidule'),  array('chouette' => '1', '#text' => 'chose'))));
        //        echo XML_Array::export($data);
    }

}
