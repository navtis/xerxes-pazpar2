<?php

namespace Application\Model\Pazpar2;

use Xerxes,
    Zend\Debug,
    //Xerxes\Record\Format,
        Xerxes\Utility\Parser,
        Xerxes\Record\Chapter,
        Xerxes\Record\Subject as XSubject,
    Xerxes\Record\Link,
    Application\Model\Search\Item,
    Application\Model\Search\Holding;

/**
 * Pazpar2 Record
 * 
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

/* inherits huge list of fields from parent; make sure these are
populated as far as possible */
class Record extends Xerxes\Record
{
        protected $source = "pazpar2";
    protected $responsible; // munged list of editors etc
    protected $locations; // array of unique target names and titles
    protected $mergedHolding; // container for all Holdings for record
    // new fields not present in parent
    protected $genres = array(); 
    protected $credits = array();
    protected $geographic = array();
    protected $citation;
    protected $biography;
    protected $issuer;
    protected $physical;
    protected $frequency;
    protected $issue;
    protected $organization;

    // add extra fields to parent Record
    // Retained just for debugging: convenient place to print out xml record
    public function toXml()
    {
        $objXml = parent::toXml();

//       echo($this->xmlpp($objXml->saveXML(), false));
        return $objXml;
    }

    /** Map the xml returned by Pazpar2 onto the standard set of 
     * Record elements, which toXML() will then expand into Xerxes
     * xml format 
     */
        public function map()
        {
                
        if ( ! is_null( $this->document->documentElement->getElementsByTagName("hit")->item(0) ) )
        {
            // we've come from 'search'
            $record = $this->document->documentElement->getElementsByTagName("hit")->item(0);
        }
        else
        { 
            // this is a single 'record' already
            $record = $this->document->documentElement;
        }
//var_dump($this->document->saveXML());
                $this->score = (string) $this->getElementValue($record, "relevance");       
                $this->title = (string) $this->getElementValue($record, "md-title")
                    . " " . (string) $this->getElementValue($record, "md-title-number-section")
                    . (string) $this->getElementValue($record, "md-title-name-section");

                $this->sub_title = (string) $this->getElementValue($record, "md-title-remainder");
                $this->series_title = (string) $this->getElementValue($record, "md-series-title");
                $this->isbns = array_unique($this->getElementValues($record, "md-isbn") );
                $this->issns = array_unique($this->getElementValues($record, "md-issn") );
                $this->language = (string) $this->getElementValue($record, "md-language");
                $this->citation = (string) $this->getElementValue($record, "md-citation");
                $this->biography = (string) $this->getElementValue($record, "md-biography");
                $this->issuer = (string) $this->getElementValue($record, "md-issuer");
                $this->places = array_unique($this->getElementValues($record, "md-geographic") );
                $this->periods = array_unique($this->getElementValues($record, "md-period") );
                $credits[] = $this->getElementValue($record, "md-credits");
                $credits[] = $this->getElementValue($record, "md-performers");
                $this->credits = array_unique($credits);

                // format                       
                $risformat = $this->getElementValue($record,"md-medium");
                $this->format->setFormat($risformat);
                $this->format->setPublicFormat($this->format->getConstNameForValue($risformat));

                // extent is used in Bibliographic 
                $this->extent = $this->getElementValue($record,"md-physical-extent");
        
                // Xerxes doesn't seem to use this info by default, so this is a new field
                $physical = array();
                if (! is_null( $this->getElementValue($record,"md-physical-format") ) )
                {
                    $physical[] = $this->getElementValue($record,"md-physical-format");
                }
                if (! is_null( $this->getElementValue($record,"md-physical-extent") ) )
                {
                    $physical[] = $this->getElementValue($record,"md-physical-extent");
                }
                if (! is_null( $this->getElementValue($record,"md-physical-dimensions") ) )
                {
                    $physical[] = $this->getElementValue($record,"md-physical-dimensions");
                    foreach($physical as &$p)
                    $p = trim($p, '.;,');
                    $this->physical = implode("; ", $physical);
                }

                // fetch the unique target library names and keys for this record
                // for use on results page
                $locations = $record->getElementsByTagName('location');
                foreach($locations as $location)
                {
                    $this->locations[$location->getAttribute('name')] = $this->getElementValue($location, "location_title");
                }
                // and now get the holdings information
                // for use on record page (location list is empty if saving the record)
                if ( is_array( $this->locations ) )
                {
                    $this->populateHoldings($locations);
                }

                // description
                $this->description = implode(' ', $this->getElementValues( $record, "md-xerxes-note") );
                $this->snippet = $this->getElementValue( $record, "md-snippet" );
                $this->abstract = $this->getElementValue( $record, "md-abstract" );
                        
                // record id
                $this->record_id = urlencode((string)$this->getElementValue($record, "recid"));
                // year
                $this->year = $this->getElementValue($record, "md-date");
                
                $this->edition = $this->getElementValue($record, "md-edition");

                $this->parseAuthor($record);

                // publication information
                $this->place = $this->getElementValue($record, "md-publication-place");
                $this->publisher = $this->getElementValue($record, "md-publication-name");
                $this->organization = $this->getElementValue($record, "md-organization");
                $this->frequency = (string) $this->getElementValue($record, "md-publication-frequency");
                $this->issue = (string) $this->getElementValue($record, "md-issues");
                if ( is_null($this->year) )
                {
                    $this->year = $this->getElementValue($record, "md-publication-date");
                }
        
                // Table of Contents, if any
                if (! is_null($this->getElementValue($record,"md-toc") ) )
                {   
                    $this->parseTOC($this->getElementValue($record,"md-toc"));
                }

                //subjects
                $subjects = $this->getElementValues($record, "md-subject-long");
                if ( sizeof( $subjects ) == 0 )
                    $subjects = $this->getElementValues($record, "md-subject");

                // remove duplicates
                $subjects = array_unique($subjects); 
                // sort into ascending length
                usort($subjects, function($a, $b) {
                    return strlen($a) - strlen($b);
                });

                /* this version keeps the original strings */
                foreach( $subjects as $subject)
                {
                    $subject_object = new XSubject(); 
                    $subject_object->display = (string) $subject;
                    $subject_object->value = (string) $subject;

                    array_push($this->subjects, $subject_object);
                }

                /* this alternative version breaks into separate terms: not better? 
                // Split all subjects into component parts and order by frequency
                $subj_array = array();
                foreach( $subjects as $subject)
                {
                    $bits = explode(',', $subject);
                    foreach($bits as $bit)
                    {
                        $subj_array[] = trim($bit);
                    }
                }
                $subj_count = array_count_values($subj_array);
                arsort($subj_count);
                foreach( $subj_count as $subject => $count)
                {
                    $subject_object = new Subject();
                    $subject_object->display = (string) $subject;
                    $subject_object->value = (string) $subject;

                    array_push($this->subjects, $subject_object);
                }
                */

                //genres are very similar to Subjects
                $genres = $this->getElementValues($record, "md-genre");
                // remove duplicates
                $this->genres = array_unique($genres); 

            }

    /**
     * Recover authors from Author and Title-responsibility fields
     * Sets $this->authors
     */
    protected function parseAuthor($record)
    {
                // authors 
        if (! is_null($this->getElementValue($record,"md-author") ) )
        {
            $author = $this->getElementValue($record,"md-author");
            $this->authors[] = new Xerxes\Record\Author($author, null, 'personal');
        }
        if ( !is_null( $this->getElementValue($record,"md-title-responsibility") ) )
        {
            $responsible = trim( $this->getElementValue($record,"md-title-responsibility") );
            // remove fully enclosing brackets
            $responsible = trim( preg_replace( '/^\[([^\]]*)\]$/', '$1', $responsible ) );
            
            // try to extract people and their roles
            $role = ''; $persons='';
            if ( preg_match('/^\[(.*)\](.*)$/', $responsible, $matches) )
            {
                $role = $matches[1];
                $persons = $matches[2];
            } 
            else if ( preg_match('/^(.*) by (.*)$/', $responsible, $matches) )
            {
                $role = $matches[1];
                $persons = $matches[2];
            } 
            else if ( preg_match('/^ed. (.*)$/i', $responsible, $matches) )
            {
                $role = 'Editor';
                $persons = $matches[1];
            } 
            else if ( preg_match('/^editors?(.*)$/i', $responsible, $matches) )
            {
                $role = 'Editor';
                $persons = $matches[1];
            }
            else if ( preg_match('/^authors?(.*)$/i', $responsible, $matches) )
            {
                $role = 'Author';
                $persons = $matches[1];
            }
            if (preg_match('/edit/i', $role) )
            {
                $role = 'Editor';
            }
            //echo("<p>role: $role people: $persons</p>");
            // if we don't have the people in the authors, try to add them
            // FIXME should have a 'match' function in Author
            $people = explode(',', $persons); // may be a list
            $found = false;
            if ( count($this->authors) > 0 )
            {
                foreach ($people as $person)
                {
                    $title_parts = preg_split('/\W+/', $person);
                    foreach($this->authors as $author)
                    { 
                        // let's hope its not John and Jane Smith
                        if ( in_array($author->last_name, $title_parts) ) 
                        { 
                            $found = true; 
                            break;
                        } 
                    } 
                }
            }
            if (! $found )
            {
                $this->responsible = $responsible;
                /* too dangerous adding to primary author 
                if ( count($this->authors) > 0 ){
                    $additional = true;
                }
                else
                {
                    $additional = false;
                }
                foreach ($people as $person)
                {
                    $this->authors[] = new Xerxes\Record\Author($person, null, 'personal', $additional);
                  
                }
                */
            }

        }
        // maybe a conference proceedings?
        if (! is_null($this->getElementValue($record,"md-meeting-name") ) )
        {
            $author = $this->getElementValue($record,"md-meeting-name");
            $author_object = new Xerxes\Record\Author($author, null, 'conference');
            $this->authors[] = $author_object;
        }
        // or a corporate author?
        if (! is_null($this->getElementValue($record,"md-corporate-name") ) )
        {
            $author = $this->getElementValue($record,"md-corporate-name");
            $author_object = new Xerxes\Record\Author($author, null, 'corporate');
            $this->authors[] = $author_object;
        }
        // or an organization with a journal? 
        if (! is_null($this->getElementValue($record,"title-reponsibility") ) )
        {
            $author = $this->getElementValue($record,"title-responsibility");
            $author_object = new Xerxes\Record\Author($author, null, 'corporate');
            $this->authors[] = $author_object;
        }

    }
    
    /**
     * Populate mergedHoldings with holding/circulation information returned
     * from Z-Servers. Attempts some hacky patches for different ILS holding formats
     *
     * @param elementList $locations
     */
    protected function populateHoldings($locations)
    {
        $this->mergedHolding = new MergedHolding();

        $domxpath = new \DOMXPath($this->document);
        
        foreach($this->locations as $loc_name => $loc_title)
        {
            $hs = new Holdings();
            $hs->setTargetName($loc_name);
            $hs->setTargetTitle($loc_title);
            $recs = $domxpath->query("//location[@name='$loc_name']");

            foreach( $recs as $rec ) 
            {
                $linkback = $this->getElementValue($rec, "linkback_url");
                if ( strlen( $linkback ) > 0 )
                {
                    $rid = $this->getElementValue($rec, "md-iii-id");
                    // is this an innopac library?
                    if ( strlen( $rid ) > 1 )
                    {
                        // remove leading . and trailing digit
                        $rid = preg_replace('/^\./', '', $rid);
                        $rid = substr($rid, 0 , -1);
                        $linkback = preg_replace( '/____/', $rid, $linkback ); 
                        $link = new Link($linkback, 'original', "Holdings information in $loc_title Library catalogue");
                        $hs->addLink($link);
                    } 
                    else
                    {
                        $rid = $this->getElementValue($rec, "md-id");
                        if ( strlen( $rid ) > 1 )
                        {
                            // FIXME get me out of here
                            if ( preg_match('/^UCL/', $rid) )
                            {
                                $rid = substr( $rid, -9);
                            }

                            $linkback = preg_replace( '/____/', $rid, $linkback ); 
                            $link = new Link($linkback, 'original', "Holdings information in $loc_title Library catalogue");
                            $hs->addLink($link);
                        }
                        else
                        {
                            // can't create a link without an id
                        }
                    }
                }

                //if an electronic url, storeit
                $els = $rec->getElementsByTagname("md-electronic-url");
                $source = $this->getElementValue($rec, "md-journal-title");
                $source = is_null($source)?'':"from $source";
                $display = "Electronic resource $source for $loc_title members";
                foreach($els as $el)
                {
                    $url = $el->nodeValue;
                    // FIXME could do a lot more here
                    if (preg_match( '/pdf$/i', $url ) )
                    {
                        $l = new Link($url, 'pdf', $display);
                    }
                    else
                    {
                        $l = new Link($url, 'any', $display);
                    }
                    $hs->addLink($l);
                }

                $els = $rec->getElementsByTagname("md-opacholding");
                foreach($els as $el)
                {
                    // a bare holding, without circulation info
                    $i = new Item();
                    $i->setProperty("callnumber", $el->getAttribute('callnumber'));
                    $i->setProperty("location", $el->getAttribute('locallocation'));
                    $i->setProperty("availability", $el->getAttribute('available'));
                    $hs->addItem($i);
                }                        
                
                $els = $rec->getElementsByTagname("md-opacitem");
                foreach($els as $el)
                {
                    // circulation data
                    $i = new Item();
                    $i->setProperty("bib_id", $el->getAttribute('itemid'));
                    $i->setProperty("availability", $el->getAttribute('available'));
                    $i->setProperty("status", $el->getAttribute('duration'));
                    $i->setProperty("location", $el->getAttribute('locallocation'));
                    $i->setProperty("reserve", $el->getAttribute('onhold'));
                    $i->setProperty("duedate", $el->getAttribute('duedate'));
                    $i->setProperty("callnumber", $el->getAttribute('callnumber'));
                    $hs->addItem($i);
                }
                // holding records from unicorn
                $els = $rec->getElementsByTagname("md-holding");
                foreach($els as $el)
                {
                    // circulation data
                    $i = new Item();
                    $i->setProperty("availability", $el->getAttribute('available'));
                    $i->setProperty("status", $el->getAttribute('duration'));
                    $i->setProperty("location", $el->getAttribute('locallocation'));
                    $i->setProperty("callnumber", $el->getAttribute('callnumber'));
                    $i->setProperty("duedate", $el->getAttribute('duedate'));
                    $hs->addItem($i);
                }
                // If it's a journal we want to treat the issues available
                // in the same way
                // FIXME allow for separate Vol/Issue/year data
                // FIXME use new holdings fields
                $els = $rec->getElementsByTagname("md-issues"); //ULS format
                foreach($els as $el)
                {
                    $i = new Item();
                    $i->setProperty( "callnumber", $el->nodeValue );
                    $hs->addItem($i);
                }
            }
            if (! $hs->hasCirculationData())
            {  
                $i = new Item();
                if ( $hs->hasLinks() )
                {
                    $i->setProperty("location", "linkback");
                }
                else
                {
                    $i->setProperty("location", "none");
                }
                $hs->addItem($i);
            }
            
            $this->mergedHolding->addHoldings($hs);
        }
    }

    public function getMergedHolding()
    {
        return $this->mergedHolding;
    }


    protected function parseTOC($table_of_contents)
    {

        if ( $table_of_contents != "" ) 
        { 
            $chapter_titles_array = explode("--", $table_of_contents); 
            foreach ( $chapter_titles_array as $chapter ) 
            { 
                $chapter_obj = new Chapter($chapter); 
                if ( strpos($chapter, "/") !== false ) 
                { 
                    $chapter_parts = explode("/", $chapter); 
                    $chapter_obj->title = $chapter_parts[0]; 
                    $chapter_obj->author = $chapter_parts[1]; 
                } 
                else 
                { 
                    $chapter_obj->statement = $chapter; 
                } 
                $this->toc[] = $chapter_obj;
            }
        }
        //Debug::dump($this->toc);
    }

    public function getStandardNumbers()
    {
        if (sizeof( $this->issns ) > 0)
            return $this->issns;
        else if (sizeof( $this->isbns ) > 0)
            return $this->isbns;
        else return array();
    }

    public function getISBNs()
    {
        return $this->isbns;
    }

    public function getISSNs()
    {
        return $this->issns;
    }

    /* XML access and formatting utilities */

        protected function getElement($node, $name)
        {
                $elements = $node->getElementsByTagName($name);
                
                if ( count($elements) > 0 )
                {
                        return $elements->item(0);
                }
                else
                {
                        return null;
                }
        }
        
        protected function getElementValue($node, $name)
        {
                $element = $this->getElement($node, $name);
                
                if ( $element != null )
                {
                        return $element->nodeValue;
                }
                else
                {
                        return null;
                }
        }
        
        protected function getElementValues($node, $name)
        {
                $values = array();
                
                $elements = $node->getElementsByTagName($name);
                
                foreach ( $elements as $node )
                {
                        array_push($values, $node->nodeValue);
                }
                
                return array_unique($values);
        }

        protected function getElementValuesAttributes($node, $name, $attrname)
        {
                $values = array();
                
                $elements = $node->getElementsByTagName($name);
                
                foreach ( $elements as $node )
                {
                        array_push($values, $node->getAttribute($attrname));
                }
                
                return $values;
        }

        protected function getElementValuesAttributePairs($node, $name, $keyname, $valname)
        {
            $values = array();
            
            $elements = $node->getElementsByTagName($name);
             
            foreach ( $elements as $node )
            {
                $values[$node->getAttribute($keyname)] = $node->getAttribute($valname);
            }
            return $values;
        }

    /** Prettifies an XML string into a human-readable and indented work of art
     * Just used for debugging
     * FIXME delete this function before release - not mine
     *  @param string $xml The XML as a string 
     *  @param boolean $html_output True if the output should be escaped (for use in HTML) 
     */  
     function xmlpp($xml, $html_output=false) {  
         $xml_obj = new \SimpleXMLElement($xml);  
         $level = 4;  
         $indent = 0; // current indentation level  
         $pretty = array();  
                            
         // get an array containing each XML element  
         $xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));  
                                   
         // shift off opening XML tag if present  
         if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0])) {  
             $pretty[] = array_shift($xml);  
         }  
                                                          
         foreach ($xml as $el) {  
            if (preg_match('/^<([\w])+[^>\/]*>$/U', $el)) {  
                // opening tag, increase indent  
                $pretty[] = str_repeat(' ', $indent) . $el;  
                $indent += $level;  
            } else {  
                if (preg_match('/^<\/.+>$/', $el)) {              
                    $indent -= $level;  // closing tag, decrease indent  
                }  
                if ($indent < 0) {  
                    $indent += $level;  
                }  
                $pretty[] = str_repeat(' ', $indent) . $el;  
            }  
                                                                                                              }     
                                                                                                              $xml = implode("\n", $pretty);     
        return ($html_output) ? htmlentities($xml) : $xml;  
     }  
}

?>
