<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 copyright: 2010 California State University
 version: $Id: worldcat_lookup.xsl 1460 2010-10-26 21:00:20Z dwalker@calstate.edu $
 package: Worldcat
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
 -->
 
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../includes.xsl" />
<xsl:import href="../search/record.xsl" />
<xsl:import href="../search/books.xsl" />
<xsl:import href="includes.xsl" />
<xsl:import href="eng.xsl" />

<xsl:output method="html" />

<xsl:template match="/*">
    <xsl:call-template name="surround" />
</xsl:template>

<xsl:template name="main">
    <xsl:call-template name="session-data"/>
    <xsl:call-template name="record" />
</xsl:template>

    <!-- 
         TEMPLATE: SIDEBAR 
    --> 
    <xsl:template name="sidebar"> 
        <!-- <xsl:call-template name="account_sidebar" /> -->
        <xsl:if test="//config/external_isn_link">
		    <xsl:call-template name="external_links"/>
        </xsl:if>
        <xsl:call-template name="citation" /> 
    </xsl:template>

    <xsl:template name="external_links">
    <xsl:if test="//isn_links">
    <div id="isn_links" class="box">
        <h2>
            <xsl:copy-of select="$text_record_find_more" /><xsl:text> </xsl:text>
        </h2>
        <ul>
            <xsl:for-each select="//isn_links/*">
                <li>
                    <a href="{.}" target="_blank"><xsl:value-of select="name(.)"/></a>
                </li>
            </xsl:for-each>
        </ul>
    </div>
    </xsl:if>
    </xsl:template>

<!-- override javascript-include from ../includes.xsl GS -->
<xsl:template name="javascript_include"> 
    <xsl:call-template name="jslabels" /> 
    <script src="javascript/jquery/jquery-1.6.2.min.js" language="javascript" type="text/javascript"></script> <script src="javascript/results.js" language="javascript" type="text/javascript"></script> <script src="javascript/pz2_ping.js" language="javascript" type="text/javascript"></script> 
</xsl:template> 

    <!--
       TEMPLATE: OVERRIDE SEARCH/RECORD/RECORD AUTHORS TO ADD TITLE-RESPONSIBILITY
    -->

    <xsl:template name="record_authors">
        <xsl:choose>
            <xsl:when test="authors/author[@type = 'personal']"> 
                <xsl:variable name="primauth">
                    <xsl:value-of select="authors/author[@type='personal'][1]/aufirst"/> <xsl:text> </xsl:text> 
                    <xsl:value-of select="authors/author[@type='personal'][1]/auinit"/> 
                    <xsl:value-of select="authors/author[@type='personal'][1]/aulast"/>
                </xsl:variable>
                <div id="record-authors"> 
                    <dt><xsl:copy-of select="$text_results_author" />:</dt> 
                    <dd> 
                    <xsl:for-each select="authors/author[@type = 'personal']"> 
                        <xsl:value-of select="aufirst" /><xsl:text> </xsl:text> 
                        <xsl:value-of select="auinit" /><xsl:text> </xsl:text> 
                        <xsl:value-of select="aulast" /><xsl:text> </xsl:text> 
                        <xsl:if test="following-sibling::author[@type = 'personal']"> 
                            <xsl:text> ; </xsl:text> 
                        </xsl:if> 
                    </xsl:for-each> 
                    </dd> 
                    <xsl:variable name="resp">
                        <xsl:value-of select="responsible"/>
                    </xsl:variable>
                    <xsl:if test="responsible">
                        <xsl:if test="translate($resp,'.','') != $primauth">
                            <dt></dt>
                            <dd><xsl:value-of select="responsible"/></dd>
                        </xsl:if>
                     </xsl:if>
                </div>
            </xsl:when>
            <xsl:when test="responsible">
                <div id="record-authors"> 
                    <dt><xsl:copy-of select="$text_results_author" />:</dt> 
                    <dd><xsl:value-of select="responsible"/></dd>
                </div>
             </xsl:when>
             <xsl:otherwise />
          </xsl:choose>
    </xsl:template>

    <!--
       TEMPLATE: OVERRIDE SEARCH/RECORD/RECORD SERIES
    -->

    <xsl:template name="series"> 
        <xsl:if test="series_title"> 
            <div> 
                <dt>Series:</dt> 
                <dd> 
                <xsl:value-of select="series_title" /> 
                </dd> 
            </div> 
        </xsl:if> 
    </xsl:template>

    <!--
       TEMPLATE: OVERRIDE SEARCH/RECORD/RECORD record_details TO ADD PHYSICAL INFO
    -->

    <xsl:template name="record_details"> 
        <xsl:call-template name="record_abstract" /> 
        <xsl:call-template name="record_recommendations" /> 
        <xsl:call-template name="record_toc" /> 
        <xsl:call-template name="record_subjects" /> 
        <xsl:call-template name="record_genres" /> 
        <xsl:call-template name="geographic" />
        <div id="record-additional-info"> 
            <h2>Additional details</h2> 
            <dl> 
                <xsl:call-template name="record_language" /> 
                <xsl:call-template name="physical" /> 
                <xsl:call-template name="record_standard_numbers" /> 
                <xsl:call-template name="record_notes" /> 
                <xsl:call-template name="description" /> 
                <xsl:call-template name="credits" />
                <xsl:call-template name="additional-title-info" /> 
            </dl> 
        </div> 
    </xsl:template>

	<!--
		TEMPLATE: RECORD GENRES
	-->
	
	<xsl:template name="record_genres">
		<xsl:if test="genres/genre">
			<h2><xsl:copy-of select="$text_record_genres" />:</h2>
			<ul>
				<xsl:for-each select="genres/genre">
					<li><xsl:value-of select="." /></li>
				</xsl:for-each>
			</ul>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="credits">
		<xsl:if test="credits/credit">
			<h2><xsl:copy-of select="$text_record_credits" />:</h2>
			<ul>
				<xsl:for-each select="credits/credit">
					<li><xsl:value-of select="." /></li>
				</xsl:for-each>
			</ul>
		</xsl:if>
	</xsl:template>
	
	<xsl:template name="geographic">
		<xsl:if test="places/place">
			<h2><xsl:copy-of select="$text_record_geographic" />:</h2>
			<ul>
				<xsl:for-each select="places/place">
					<li><xsl:value-of select="." /></li>
				</xsl:for-each>
			</ul>
		</xsl:if>
	</xsl:template>
	
    <xsl:template name="physical"> 
        <xsl:if test="physical"> 
            <div> 
                <dt>Format:</dt> 
                <dd> 
                <xsl:value-of select="physical" /> 
                </dd> 
            </div> 
        </xsl:if> 
    </xsl:template>

    <!--
                 TEMPLATE: RECORD ACTIONS: OVERRIDEN FROM search/record to allow multiple holdings
    -->

    <xsl:template name="record_actions">
        <div id="record-full-text" class="raised-box record-actions">
    
        <xsl:for-each select="//mergedHolding/holdings">
            <xsl:call-template name="availability">
                <xsl:with-param name="context">record</xsl:with-param>
            </xsl:call-template>
        </xsl:for-each>

<!--            <xsl:call-template name="save_record" /> -->
        </div>
    </xsl:template>

	<!-- 	
		TEMPLATE: AVAILABILITY LOOKUP OVERRIDEN FROM search/books.xsl
	-->
	
	<xsl:template name="availability_lookup">
		<xsl:param name="record_id" />
		<xsl:param name="isbn" />
		<xsl:param name="oclc" />
		<xsl:param name="type" select="'none'" />
		<xsl:param name="nosave" />
		<xsl:param name="context">results</xsl:param>
			
		<xsl:variable name="source" select="//request/source" />
		<xsl:variable name="target_name" select="target_name" />
		<xsl:variable name="target_title" select="target_title" />
	    	
<!--		<xsl:variable name="printAvailable" select="count(../holdings/items/item[availability=1])" /> -->
		<xsl:variable name="printAvailable" select="count(items/item)" />
		<xsl:variable name="onlineCopies" select="count(links/link[@type != 'none'])" />
		<xsl:variable name="totalCopies" select="$printAvailable + $onlineCopies" />

		<xsl:choose>
			
		    <xsl:when test="1=1"> <!-- always do this for now GS -->	
		
				<xsl:choose>		

					<xsl:when test="items/item">
						<!-- item and holdings data already fetched and in the XML response -->
					
						<!-- pick display type -->
					
						<xsl:choose>
						
							<xsl:when test="holding">
							
								<xsl:call-template name="availability_lookup_holdings">
									<xsl:with-param name="context" select="$context" />
								</xsl:call-template>
								
							</xsl:when>
							
							<xsl:when test="$type = 'summary'">
							
								<xsl:call-template name="availability_lookup_summary">
									<xsl:with-param name="totalCopies" select="$totalCopies" />
									<xsl:with-param name="printAvailable" select="$printAvailable" />
								</xsl:call-template> 
								
							</xsl:when>
							<xsl:otherwise>
							
								<xsl:call-template name="availability_lookup_full">
									<xsl:with-param name="totalCopies" select="$totalCopies" />
								</xsl:call-template>
							
							</xsl:otherwise>
						</xsl:choose>
					
					</xsl:when>
	
					<!-- not here, so need to get it dynamically with ajax -->
			
					<xsl:otherwise>
								
						<div id="{//request/controller}-{$record_id}-{$type}" class="availability-load"></div>
			
					</xsl:otherwise>				
				</xsl:choose>
	
				<!-- check for full-text -->
				
				<xsl:call-template name="full_text_links"/>	
								
			</xsl:when>
			
			<!-- no lookup required, thanks -->
			
			<xsl:otherwise>
				<xsl:call-template name="availability_lookup_none" />	
			</xsl:otherwise>
		</xsl:choose>
	
	</xsl:template>

	<!-- 	
		TEMPLATE: AVAILABILITY LOOKUP FULL
		A full table-view of the (print) holdings information, with full-text below
	-->
	
	<xsl:template name="availability_lookup_full">
		<xsl:param name="totalCopies" />

		<xsl:if test="count(items/item) != '0'">
			<xsl:call-template name="availability_item_table" />
		</xsl:if>
		
		<xsl:if test="$totalCopies = 0">
			<xsl:call-template name="ill_option" />
		</xsl:if>
				
	</xsl:template>
	
	<!-- 	
		TEMPLATE: AVAILABILITY ITEM TABLE
		Show the items in a table
	-->
	
	<xsl:template name="availability_item_table">
	
		<div>
			<xsl:attribute name="class">
				<xsl:text>booksAvailable</xsl:text>
				<xsl:if test="//request/action = 'record'">
					<xsl:text> booksAvailableRecord</xsl:text>
				</xsl:if>
			</xsl:attribute>
			
			<table class="holdings-table" width="100%">
				<xsl:if test="target_title">
                    <tr>
                        <xsl:choose>
                            <xsl:when test="links/link[@type='original']">
					            <th colspan="1">Institution: <span style="font-weight: bold"><xsl:value-of select="target_title"/></span></th>
					            <th colspan="4"><a href="{links/link/url}" target="_blank"><xsl:value-of select="$text_record_linkback"/></a></th>
                            </xsl:when>
                            <xsl:otherwise>
					            <th colspan="5">Institution: <span style="font-weight: bold"><xsl:value-of select="target_title"/></span></th>
                            </xsl:otherwise>
                        </xsl:choose>
                    </tr>
				</xsl:if>
			<tr>
				<th>Location</th>
				<th>Call Number</th>
				<th>Availability</th>
				<th>Status</th>
				<th>Due date</th>
			</tr>
			<xsl:for-each select="items/item">
                <xsl:variable name="loc">
                    <xsl:value-of select="location"/>
                </xsl:variable>
				<tr>
                    <xsl:choose>
                        <xsl:when test="$loc='none'">
					        <td><xsl:value-of select="$text_record_no_holdings" /></td>
                        </xsl:when>
                        <xsl:when test="$loc='linkback'">
                            <td> <xsl:value-of select="$text_record_availability"/><xsl:text> </xsl:text><a href="{../../links/link/url}" target="_blank"><xsl:value-of select="../../target_title"/><xsl:text> </xsl:text><xsl:value-of select="$text_record_catalogue_entry"/></a> </td>
                        </xsl:when>
                        <xsl:otherwise>
					        <td><xsl:value-of select="location" /></td>
                        </xsl:otherwise>
                    </xsl:choose>
					<td><xsl:value-of select="callnumber" /></td>
					<td><xsl:value-of select="availability" /></td>
					<td><xsl:value-of select="status" /></td>
					<td><xsl:value-of select="duedate" /></td>
				</tr>
			</xsl:for-each>
			</table>
		</div>
	
	</xsl:template>

</xsl:stylesheet>
