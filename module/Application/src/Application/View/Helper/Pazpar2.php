<?php

namespace Application\View\Helper;

use Xerxes\Record,
    Xerxes\Utility\Parser,
    Application\Model\Search\Query,
    Application\Model\Search\Result,
    Application\Model\Search\ResultSet;

class Pazpar2 extends Search
{

    /* Facets need to restart the search; pazpar2 decides if the buffered
     * results are reusable. So rather than linking to resultsAction,
     * facets need to link back to searchAction
     */
    public function facetParams() 
    { 
        $params = $this->currentParams(); 
        $params["start"] = null; // send us back to page 1
        $params['action'] = 'search';
        return $params; 
    } 

    /**
     * Parameters to construct the url on the search redirect
     * Needs to retain search parameters (unlike search helper)
     * in order for pazpar2 facets to work
     * @return array
     */

     public function searchRedirectParams()
     {
        $params = $this->currentParams(); 
        $params['controller'] = $this->request->getParam('controller');
        $params['action'] = "results";
        $params['sort'] = $this->request->getParam('sort');

        return $params;
     }

    public function addQueryLinks(Query $query)
    {
        parent::addQueryLinks($query); 
        foreach ( $query->getLimits() as $limit ) 
        { 
            $params = $this->currentParams(); 
            $params = Parser::removeFromArray($params, $limit->field, $limit->value); 
            $params['action'] = 'search'; 
            $limit->remove_url = $this->request->url_for($params); 
        } 
    }

    /** 
    * URLs for the external searches (configurable)
    * The searches are mapped onto the current search
    * This version is very M25 specific
    * @param config Engine configuration
    * @return array key => url 
    */ 
    public function addExternalLinks($config)
    {
        $urls = array();
        // gather the information
        // we are ignoring target libraries 
        // since there is no point limiting the search
        // and formats
        // since the format mapping is not 1=>1
        $field = $this->request->getParam('field', null, true); 
        $query = $this->request->getParam('query', null, true);
        $facet_subject = $this->request->getParam('facet.subject', null, true);
        $facet_author = $this->request->getParam('facet.author', null, true);
        if ( $config->getConfig('search_copac', false) == 'true' )
        {
            $map = array(
                'title' => 'title',
                'author' => 'author',
                'isbn' => 'isn',
                'issn' => 'isn',
                'subject' => 'sub',
                'keyword' => 'keyword',
            );
            $url = 'http://copac.ac.uk/search?';
            $url .= $map[$field[0]] .'=' . urlencode( $query[0] );
            if ( $facet_subject[0] )
            {
                $url .= '&sub=' . urlencode( $facet_subject[0] );
            }
            if ( $facet_author[0] )
            {
                $url .= '&author=' . urlencode( $facet_author[0] );
            }
            $urls['COPAC'] = $url;
        }
        if (( $config->getConfig('search_suncat', false) == 'true' )
            && ($field[0] != 'author') && ($field[0] != 'isbn') )
        {
            $map = array(
                'title' => 'WTI',
                'subject' => 'WSU',
                'keyword' => 'WRD',
                'issn' => 'ISSN',
             );

            $url = 'http://suncat.edina.ac.uk/F?func=find-b&';
            $url .= 'request=' . urlencode( $query[0] ) . '&';
            $url .= 'find_code=' . $map[$field[0]] . '&';
            $url .= 'filter_code2=WGO&filter_request2=England+SouthEast';
            $urls['SUNCAT'] = $url;
        }
        return $urls;
    }

    /** 
    * URLs for the external versions of a single item
    * identified by IS[B,S]N
    * @param ResultSet $results
    * @param config Engine configuration
    * @return array key => url 
    */ 
    public function addExternalRecordLinks( ResultSet &$results, $config )
    {
        // dummy foreach - there should only ever be one
		foreach ( $results->getRecords() as $result )
		{
            $issn = $isbn = null;
			$xerxes_record = $result->getXerxesRecord();
            $issns = $xerxes_record->getISSNs();
            $isbns = $xerxes_record->getISBNs();
            /* we will just work with the first one of each */
            if ( isset($issns[0] ) )
            {
                $issn = $issns[0];
            }
            if ( isset($isbns[0] ) )
            {
                $isbn = $isbns[0];
            }
            $settings = $config->getConfig("external_isn_link", 'false');
            if ( $settings != null )
            {
                if ( $issn != null )
                {
                    foreach ( $settings->option as $option )
                    {
                        if ($option['active'] == 'true'
                                && ( ( $option['type'] == 'issn' ) 
                                    || ( $option['type'] == 'isn' ) )
                           )
                        {
                            $url = preg_replace( '/____/', $issn, $option['url'] );
                            $result->isn_links[(string)$option['public']] = $url;
                        }
                    }
                }
                if ( $isbn != null )
                {
                    foreach ( $settings->option as $option )
                    {
                        if ($option['active'] == 'true'
                                &&  (  ( $option['type'] == 'isbn' ) 
                                    || ( $option['type'] == 'isn' ) )
                           )
                        {
                            $url = preg_replace( '/____/', $isbn, $option['url'] );
                            $result->isn_links[(string)$option['public']] = $url;
                        }
                    }
                }
            }
        }
    }

    /** 
    * URL for the full record display, including targets 
    * 
    * @param $result Record object 
    * @return string url 
    */ 
    public function linkFullRecord( Record $result ) 
    { 
        $arrParams = array( 
            'controller' => $this->request->getParam('controller'), 
            "action" => "record", 
            "id" => $result->getRecordID(), 
            'target' => $this->request->getParam('target', null, true) 
        ); 
        return $this->request->url_for($arrParams); 
    } 
    /** 
    * URL for the record display, with no target yet specified
    * 
    * @param $result Record object 
    * @return string url 
    */
    public function linkOther( Result $result ) 
    {
        $record = $result->getXerxesRecord();
        $arrParams = array( 
            'controller' => $this->request->getParam('controller'), 
            "action" => "record", 
            "id" => $record->getRecordID()
        ); 
        $result->url_for_item = $this->request->url_for($arrParams); 
        return $result;
    } 

	/**
	 * Add links to facets
	 * 
	 * @param ResultSet $results
	 */	
	
	public function addFacetLinks( ResultSet &$results )
	{	
		// facets

		$facets = $results->getFacets();
		
		if ( $facets != "" )
		{
			foreach ( $facets->getGroups() as $group )
			{
				foreach ( $group->getFacets() as $facet )
				{
					// existing url
						
					$url = $this->facetParams();
							
					// now add the new one
							
					if ( $facet->key != "" ) 
					{
						// key defines a way to pass the (internal) value
						// in the param, while the name is the display value
					    // NB different behavious from other Xerxes apps
						$url["facet." . $group->name] = urlencode($facet->key);
					}
					else
					{
						$url["facet." . $group->name] = $facet->name;									
					}
					$facet->url = $this->request->url_for($url);
				}
			}
		}
	}

	/**
	 * Paging element
	 * Overrides version in Helper/Search.php to add first/last
     * options. Merge back in later? FIXME
	 * @param int $total 		total # of hits for query
	 * @param int $start 		First record on current page
	 * @param int $max 			maximum number of results to show per page
	 * 
	 * @return DOMDocument formatted paging navigation
	 */
	
	public function pager( $total, $start, $max )
	{
		if ( $total < 1 )
		{
			return null;
		}
		
		$objXml = new \DOMDocument( );
		$objXml->loadXML( "<pager />" );
	
		$base_record = 1; // starting record in any result set
		$page_number = 1; // starting page number in any result set
		$bolShowFirst = false; // show the first page when you get past page 10
		$bolShowLast = false; // show the last page when it's past the numbered range shown
		
		if ( $start == 0 ) 
		{
			$start = 1;
		}
		
		$current_page = (($start - 1) / $max) + 1; // calculates the current selected page
		$bottom_range = $current_page - 4; // used to show a range of pages
		$top_range = $current_page + 4; // used to show a range of pages
		
		$total_pages = ceil( $total / $max ); // calculates the total number of pages
		
		// for pages 1-8 show just 1-8 (or whatever records per page)
		
		if ( $bottom_range < 0 )
		{
			$bottom_range = 0;
		}

		if ( $bottom_range > 2 ) // as we already have the 'previous' link when page=2
        {
			$bolShowFirst = true;
        }

		if ( $current_page < 4 )
		{
			$top_range = 8;
		} 
		
        if ( $total_pages > $top_range )
        {
            $bolShowLast = true;
        }

		// chop the top pages as we reach the end range
		
		if ( $top_range > $total_pages )
		{
			$top_range = $total_pages;
		}
		
		// see if we even need a pager
		
		if ( $total > $max )
		{
			// create pages and links

            if ( $bolShowFirst )
            {
			    $objPage = $objXml->createElement( "page", "|<" );
						
				$params = $this->currentParams();
				$params["start"] = '1';
						
				$link = $this->request->url_for( $params );
						
				$objPage->setAttribute( "link", Parser::escapeXml( $link ) );
				$objPage->setAttribute( "type", "first" );
				$objXml->documentElement->appendChild( $objPage );
			}

			$previous = $start - $max;

            if ( $start > $max )
			{
				$objPage = $objXml->createElement( "page", "<" ); // element to hold the text_results_previous label
				
				$params = $this->currentParams();
				$params["start"] =  $previous;
				
				$link = $this->request->url_for( $params );
				
				$objPage->setAttribute( "link", Parser::escapeXml( $link ) );
				//$objPage->setAttribute( "type", "previous" );
				$objXml->documentElement->appendChild( $objPage );
			}


			while ( $base_record <= $total )
			{
				if ( $page_number >= $bottom_range && $page_number <= $top_range )
				{
					if ( $current_page == $page_number )
					{
						$objPage = $objXml->createElement( "page", $page_number );
						$objPage->setAttribute( "here", "true" );
						$objXml->documentElement->appendChild( $objPage );
					} 
					else
					{
						$objPage = $objXml->createElement( "page", $page_number );
						
						$params = $this->currentParams();
						$params["start"] = $base_record;
						
						$link = $this->request->url_for( $params );
						
						$objPage->setAttribute( "link", Parser::escapeXml( $link ) );
						$objXml->documentElement->appendChild( $objPage );
					
					}
				}
				
				$page_number++;
				$base_record += $max;
			}
			
			$next = $start + $max;
			
			if ( $next <= $total )
			{
				$objPage = $objXml->createElement( "page", ">" ); // element to hold the text_results_next label
				
				$params = $this->currentParams();
				$params["start"] =  $next;
				
				$link = $this->request->url_for( $params );
				
				$objPage->setAttribute( "link", Parser::escapeXml( $link ) );
				//$objPage->setAttribute( "type", "next" );
				$objXml->documentElement->appendChild( $objPage );
			}

            if ( $bolShowLast )
            {
			    $objPage = $objXml->createElement( "page", ">|" );
						
				$params = $this->currentParams();
				$params["start"] = $total - $max + 1;
						
				$link = $this->request->url_for( $params );
						
				$objPage->setAttribute( "link", Parser::escapeXml( $link ) );
				//$objPage->setAttribute( "type", "last" );
				$objXml->documentElement->appendChild( $objPage );
			}

		}
		
		return $objXml;
	}
	
}

?>
