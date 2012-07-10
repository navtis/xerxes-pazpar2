--TEST--
single_link_005: Pass different class types to constructors
--FILE--
<?php
 
$dir = dirname(__FILE__);
require 'Structures/LinkedList/Single.php';

class TesterExtend extends Structures_LinkedList_SingleNode {
    protected $_my_number;

    function __construct($tester, $num) {
        $this->_my_number = $num;
        parent::__construct($tester);
    }
}

class TesterFail {
    protected $summary;
    protected $fulltext;

    function __construct($summary, $fulltext) {
        $this->summary = $summary;
        $this->fulltext = $fulltext;
    }
}


// This should work: TesterExtend extends the Structure_LinkedList_SingleNode class
$tester_extend = new TesterExtend(null, 1);
$xyy = new Structures_LinkedList_Single($tester_extend);
print "Checking for errors in the expected success case:\n";

// This should fail
print "Checking for errors in the expected failure case:\n";
$tester_fail = new TesterFail(null, 1);
$xyy_fail = new Structures_LinkedList_Single($tester_fail);
?>
--EXPECTF--
Checking for errors in the expected success case:
Checking for errors in the expected failure case:

%satal error: Argument 1 passed to Structures_LinkedList_Single::__construct() must be an instance of Structures_LinkedList_SingleNode%s
