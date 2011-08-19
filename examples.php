<?php
require_once 'XML/Array.php';


function test($a) {
    static $c = 0;
    echo '#',++$c,' ----------------------------------------------------------------------',PHP_EOL;
    var_export($a);
    ;
    $doc = new DOMDocument($x = XML_Array::export($a, XML_Array::INDENT));
    $doc->loadXML($x);
    echo PHP_EOL, $x, PHP_EOL;
}

$a = array('tata', 'toto');
test($a);

$a = array('a' => 'tata', 'b' => 'toto');
test($a);

$a = array('tata', array('truc', 'bidule'));
test($a);


$a = array('tata' => array('truc', 'bidule'));
test($a);


$a = array('tata' => array('truc' => 'chouette', 'bidule' => 'hibou'));
test($a);


$a = array('tata' => array('truc' => 'chouette', '#text' => 'hibou'));
test($a);


$a = array('tata' => array('truc' => 'chouette', '#text' => 'chouette', '#comment' => 'hibou'));
test($a);


$a = array('tata' => array('truc' => 'chouette', '#text' => array('chouette', 'hibou')));
test($a);

$a = array('tata' => array('truc' => 'chouette', '#text' => array('bidule' => 'chouette', 'hibou')));
test($a);

$a = array('tata' => array('truc' => 'chouette', '#text' => array('bidule' => 'chouette', 'machin' => 'hibou')));
test($a);


$a = array('root' => array('machin' => array( array('truc' => 0, '#text' => 'bidule'),  array('chouette' => '1', '#text' => 'chose'))));
test($a);





