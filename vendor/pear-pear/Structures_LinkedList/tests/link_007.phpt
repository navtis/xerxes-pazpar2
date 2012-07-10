--TEST--
link_007: Test many corner cases
--FILE--
<?php
 
$dir = dirname(__FILE__);
require 'Structures/LinkedList/Double.php';
require 'LinkTester.php';

$xyy = new Structures_LinkedList_Double();

// prepend to an empty list corner case: 1
$xyy->prependNode($tester1);
print "Current: {$xyy->current()->getNumb()}\n";

// prepend to a list with only one element: 21
$xyy->prependNode($tester2);
print "Current: {$xyy->current()->getNumb()}\n";

// insert after tail node corner case: 213
$xyy->insertNode($tester3, $tester1);
print "Current: {$xyy->current()->getNumb()}\n";

// insert before root node corner case: 4213
$xyy->insertNode($tester4, $tester2, true);
print "Current: {$xyy->current()->getNumb()}\n";

// insert before tail node corner case: 42153
$xyy->insertNode($tester5, $tester3, true);
print "Current: {$xyy->current()->getNumb()}\n";

// insert after root node corner case: 421653
$xyy->insertNode($tester6, $tester1);
print "Current: {$xyy->current()->getNumb()}\n";

print "Foreach: ";
foreach ($xyy as $node) {
    print $node->getNumb();
}

print "\nWhile (in reverse): ";
// test reverse iteration with while()
$link = $xyy->end();
do {
    print $link->getNumb();
} while ($link = $xyy->previous());
?>
--EXPECT--
Current: 1
Current: 1
Current: 1
Current: 1
Current: 1
Current: 1
Foreach: 421653
While (in reverse): 356124
