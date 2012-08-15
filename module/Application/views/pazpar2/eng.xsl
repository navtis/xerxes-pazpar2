<?xml version="1.0" encoding="utf-8"?>

<!--

 author: David Walker
 copyright: 2009 California State University
 version: $Id: eng.xsl 1898 2011-04-15 11:26:15Z helix84@centrum.sk $
 package: Xerxes
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
 -->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<!-- 
	TEXT LABELS 
	These are global variables that provide the text for the system.
	
	Variable names should follow the pattern of: text_{location}_{unique-name}
	Keep them in alphabetical order!!
-->
	
    <xsl:variable name="text_header_options">Options</xsl:variable>
    <xsl:variable name="text_header_my_options">Set user options</xsl:variable>
    <xsl:variable name="text_header_select_library">Select libraries to search</xsl:variable>
    <xsl:variable name="text_record_genres">Genres</xsl:variable>
    <xsl:variable name="text_record_credits">Credits</xsl:variable>
    <xsl:variable name="text_record_find_more">Find elsewhere</xsl:variable>
    <xsl:variable name="text_record_frequency_label">Frequency</xsl:variable>
    <xsl:variable name="text_record_geographic">Places</xsl:variable>
    <xsl:variable name="text_record_no_holdings">Holdings information not available</xsl:variable>
    <xsl:variable name="text_record_linkback">Original library catalogue entry</xsl:variable>
    <xsl:variable name="text_record_availability">Available from</xsl:variable>
    <xsl:variable name="text_record_catalogue_entry">catalogue entry</xsl:variable>
    <xsl:variable name="text_record_organization_label">Organization</xsl:variable>
    <xsl:variable name="text_record_publisher_label">Publisher</xsl:variable>
    <xsl:variable name="text_record_place_label">Place</xsl:variable>
	<xsl:variable name="text_region_libraries_desc">Select the regions or individual libraries you wish to search</xsl:variable>
	<xsl:variable name="text_libraries_desc">Select the libraries you wish to search</xsl:variable>
	<xsl:variable name="text_subjects_desc">Select the libraries to search by the subjects they specialise in</xsl:variable>
	<xsl:variable name="text_access_desc">Select the libraries to search according to your access rights</xsl:variable>
    <xsl:variable name="text_results_edition">Edition</xsl:variable>
    <xsl:variable name="text_results_publisher">Published by</xsl:variable>
    <xsl:variable name="text_options_module">User options</xsl:variable>
    <xsl:variable name="text_uls_search_module">Find Print Journals</xsl:variable>
    <xsl:variable name="text_search_module">Find Books and Journals</xsl:variable>
    <!-- override treatment of language in labels/eng.xsl for results page -->
    <xsl:template name="text_results_language">
        <xsl:if test="language and language != 'English'">
            <span class="results-language"> (<xsl:value-of select="language" />)</span>
        </xsl:if>
    </xsl:template>
    
    <!-- Translate medium names from Xerxes internal : Displayable -->
	<xsl:template name="text_results_format">
		<xsl:param name="format" />
		<xsl:choose>
			<xsl:when test="$format = 'Generic'">Generic</xsl:when>
			<xsl:when test="$format = 'AbstractOfWork'">Abstract</xsl:when>
			<xsl:when test="$format = 'AggregatedDatabase'">Aggregated database</xsl:when>
			<xsl:when test="$format = 'AncientText'">Historic text</xsl:when>
			<xsl:when test="$format = 'ArticleElectronic'">Electronic article</xsl:when>
			<xsl:when test="$format = 'ArticleInPress'">In press</xsl:when>
			<xsl:when test="$format = 'ArticleJournal'">Journal article</xsl:when>
			<xsl:when test="$format = 'ArticleMagazine'">Magazine article</xsl:when>
			<xsl:when test="$format = 'Articlenewspaper'">Newspaper article</xsl:when>
			<xsl:when test="$format = 'Artwork'">Work of art</xsl:when>
			<xsl:when test="$format = 'AudiovisualMaterial'">Audio-visual</xsl:when>
			<xsl:when test="$format = 'Bill'">Law</xsl:when>
			<xsl:when test="$format = 'BillUnenacted'">Green paper</xsl:when>
			<xsl:when test="$format = 'Blog'">Blog</xsl:when>
			<xsl:when test="$format = 'Book'">Book</xsl:when>
        	<xsl:when test="$format = 'BookEdited'">Book</xsl:when>
			<xsl:when test="$format = 'BookElectronic'">eBook</xsl:when>
			<xsl:when test="$format = 'BookSection'">Chapter</xsl:when>
			<xsl:when test="$format = 'BookSectionElectronic'">Chapter (electronic)</xsl:when>
			<xsl:when test="$format = 'Broadcast'">Broadcast</xsl:when>
			<xsl:when test="$format = 'Courtcase'">Court case</xsl:when>
			<xsl:when test="$format = 'Catalog'">Catalogue</xsl:when>
			<xsl:when test="$format = 'Chart'">Chart</xsl:when>
			<xsl:when test="$format = 'ClassicalWork'">Classical work</xsl:when>
			<xsl:when test="$format = 'ComputerProgram'">Electronic work</xsl:when>
			<xsl:when test="$format = 'ConferencePaper'">Conference paper</xsl:when>
			<xsl:when test="$format = 'ConferenceProceeding'">Proceedings</xsl:when>
			<xsl:when test="$format = 'Dataset'">Dataset</xsl:when>
			<xsl:when test="$format = 'EncyclopediaArticle'">Encyclopedia article</xsl:when>
			<xsl:when test="$format = 'Equation'">Equation</xsl:when>
			<xsl:when test="$format = 'Figure'">Figure</xsl:when>
			<xsl:when test="$format = 'GovernmentDocument'">Government Document</xsl:when>
			<xsl:when test="$format = 'Grant'">Grant</xsl:when>
			<xsl:when test="$format = 'Hearing'">Hearing</xsl:when>
			<xsl:when test="$format = 'InternetCommunication'">Internet communication</xsl:when>
			<xsl:when test="$format = 'Journal'">Journal</xsl:when>
			<xsl:when test="$format = 'LegalRule'">Legal ruling</xsl:when>
			<xsl:when test="$format = 'Manuscript'">Manuscript</xsl:when>
			<xsl:when test="$format = 'Map'">Map</xsl:when>
			<xsl:when test="$format = 'MusicalScore'">Score</xsl:when>
			<xsl:when test="$format = 'OnlineDatabase'">Database</xsl:when>
			<xsl:when test="$format = 'OnlineMultimedia'">Multimedia</xsl:when>
			<xsl:when test="$format = 'Pamphlet'">Pamphlet</xsl:when>
			<xsl:when test="$format = 'Patent'">Patent</xsl:when>
			<xsl:when test="$format = 'PersonalCommunication'">Personal communication</xsl:when>
			<xsl:when test="$format = 'Report'">Report</xsl:when>
			<xsl:when test="$format = 'Serial'">Serial</xsl:when>
			<xsl:when test="$format = 'Slide'">Slide</xsl:when>
			<xsl:when test="$format = 'SoundRecording'">Sound recording</xsl:when>
			<xsl:when test="$format = 'Standard'">Standard</xsl:when>
			<xsl:when test="$format = 'Statute'">Statute</xsl:when>
			<xsl:when test="$format = 'Thesis'">Thesis</xsl:when>
			<xsl:when test="$format = 'UnpublishedWork'">Unpublished work</xsl:when>
			<xsl:when test="$format = 'VideoRecording'">Video recording</xsl:when>
			<xsl:when test="$format = 'WebPage'">Web page</xsl:when>
			<xsl:when test="$format = 'BookReview'">Book review</xsl:when>
			<xsl:when test="$format = 'Image'">Image</xsl:when>
			<xsl:when test="$format = 'Kit'">Kit</xsl:when>
			<xsl:when test="$format = 'MixedMaterial'">Mixed material</xsl:when>
			<xsl:when test="$format = 'PhysicalObject'">Physical object</xsl:when>
			<xsl:when test="$format = 'Review'">Review</xsl:when>
			<xsl:when test="$format = 'Article'">Journal article</xsl:when>
			<xsl:when test="$format = 'Unknown'"></xsl:when>
			<xsl:when test="$format = 'Periodical'">Periodical</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$format" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
    <!-- Translate medium names from RIS : Displayable -->
	<xsl:template name="text_limit_format">
		<xsl:param name="format" />
		<xsl:choose>
			<xsl:when test="$format = 'GEN'">Generic</xsl:when>
			<xsl:when test="$format = 'ABS'">Abstract</xsl:when>
			<xsl:when test="$format = 'AGGR'">Aggregated database</xsl:when>
			<xsl:when test="$format = 'ANCIENT'">Historic text</xsl:when>
			<xsl:when test="$format = 'EJOUR'">Electronic article</xsl:when>
			<xsl:when test="$format = 'INPR'">In press</xsl:when>
			<xsl:when test="$format = 'JOUR'">Journal article</xsl:when>
			<xsl:when test="$format = 'MGZN'">Magazine article</xsl:when>
			<xsl:when test="$format = 'NEWS'">Newspaper article</xsl:when>
			<xsl:when test="$format = 'ART'">Work of art</xsl:when>
			<xsl:when test="$format = 'ADVS'">Audio-visual</xsl:when>
			<xsl:when test="$format = 'BILL'">Law</xsl:when>
			<xsl:when test="$format = 'UNBILL'">Green paper</xsl:when>
			<xsl:when test="$format = 'BLOG'">Blog</xsl:when>
			<xsl:when test="$format = 'BOOK'">Book</xsl:when>
        	<xsl:when test="$format = 'EDBOOK'">Book</xsl:when>
			<xsl:when test="$format = 'EBOOK'">eBook</xsl:when>
			<xsl:when test="$format = 'CHAP'">Chapter</xsl:when>
			<xsl:when test="$format = 'ECHAP'">Chapter (electronic)</xsl:when>
			<xsl:when test="$format = 'MBCT'">Broadcast</xsl:when>
			<xsl:when test="$format = 'CASE'">Court case</xsl:when>
			<xsl:when test="$format = 'CTLG'">Catalogue</xsl:when>
			<xsl:when test="$format = 'CHART'">Chart</xsl:when>
			<xsl:when test="$format = 'CLSWK'">Classical work</xsl:when>
			<xsl:when test="$format = 'COMP'">Electronic work</xsl:when>
			<xsl:when test="$format = 'CPAPER'">Conference paper</xsl:when>
			<xsl:when test="$format = 'CONF'">Proceedings</xsl:when>
			<xsl:when test="$format = 'DATA'">Dataset</xsl:when>
			<xsl:when test="$format = 'ENCYC'">Encyclopedia article</xsl:when>
			<xsl:when test="$format = 'EQUA'">Equation</xsl:when>
			<xsl:when test="$format = 'FIGURE'">Figure</xsl:when>
			<xsl:when test="$format = 'GOVDOC'">Government Document</xsl:when>
			<xsl:when test="$format = 'GRNT'">Grant</xsl:when>
			<xsl:when test="$format = 'HEAR'">Hearing</xsl:when>
			<xsl:when test="$format = 'ICOMM'">Internet communication</xsl:when>
			<xsl:when test="$format = 'JFULL'">Journal</xsl:when>
			<xsl:when test="$format = 'LEGAL'">Legal ruling</xsl:when>
			<xsl:when test="$format = 'MANSCPT'">Manuscript</xsl:when>
			<xsl:when test="$format = 'MAP'">Map</xsl:when>
			<xsl:when test="$format = 'MUSIC'">Score</xsl:when>
			<xsl:when test="$format = 'DBASE'">Database</xsl:when>
			<xsl:when test="$format = 'MULTI'">Multimedia</xsl:when>
			<xsl:when test="$format = 'PAMP'">Pamphlet</xsl:when>
			<xsl:when test="$format = 'PAT'">Patent</xsl:when>
			<xsl:when test="$format = 'PCOMM'">Personal communication</xsl:when>
			<xsl:when test="$format = 'RPRT'">Report</xsl:when>
			<xsl:when test="$format = 'SER'">Serial</xsl:when>
			<xsl:when test="$format = 'SLIDE'">Slide</xsl:when>
			<xsl:when test="$format = 'SOUND'">Sound recording</xsl:when>
			<xsl:when test="$format = 'STAND'">Standard</xsl:when>
			<xsl:when test="$format = 'STAT'">Statute</xsl:when>
			<xsl:when test="$format = 'THES'">Thesis</xsl:when>
			<xsl:when test="$format = 'UNPD'">Unpublished work</xsl:when>
			<xsl:when test="$format = 'VIDEO'">Video recording</xsl:when>
			<xsl:when test="$format = 'ELEC'">Web page</xsl:when>
			<xsl:when test="$format = 'XERXES_BookReview'">Book review</xsl:when>
			<xsl:when test="$format = 'XERXES_Image'">Image</xsl:when>
			<xsl:when test="$format = 'XERXES_Kit'">Kit</xsl:when>
			<xsl:when test="$format = 'XERXES_MixedMaterial'">Mixed material</xsl:when>
			<xsl:when test="$format = 'XERXES_PhysicalObject'">Physical object</xsl:when>
			<xsl:when test="$format = 'XERXES_Review'">Review</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$format" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

</xsl:stylesheet>
