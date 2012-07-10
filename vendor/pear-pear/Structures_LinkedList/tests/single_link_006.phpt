--TEST--
single_link_006: Corner case: add a node before the root
--FILE--
<?php
 
$dir = dirname(__FILE__);
require 'Structures/LinkedList/Single.php';
require 'SingleLinkTester.php';

$xyy = new Structures_LinkedList_Single();
$xyy->prependNode($tester1);
$xyy->appendNode($tester2);
$xyy->appendNode($tester3);
$xyy->insertNode($tester4, $tester2, true);

// Ensure we can increment the current node without messing up the list
print "\nCurrent: " . $xyy->current()->getNumb() . "\n";
$link = $xyy->next();
print "Current: " . $link->getNumb() . "\n";

$xyy->insertNode($tester5, $tester1, true);

print "\n";

$link = $xyy->current();
print "Current: " . $link->getNumb();

print "\n\nWhile: ";
// test iteration with while()
$link = $xyy->rewind();
do {
    print $link->getNumb();
} while ($link = $xyy->next());

print "\n\nForeach: ";

// test foreach() iteration
foreach ($xyy as $bull) {
  print $bull->getNumb();
}
?>
--EXPECT--
Current: 1
Current: 4

Current: 4

While: 51423

Foreach: 51423
