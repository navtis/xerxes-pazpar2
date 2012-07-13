<?xml version="1.0" encoding="UTF-8"?>

<!--

 author: David Walker
 author: Graham Seaman
 copyright: 2010 California State University
 version: $Id$
 package: pazpar2 
 link: http://xerxes.calstate.edu
 license: http://www.gnu.org/licenses/
 
 -->

<!DOCTYPE xsl:stylesheet  [
<!ENTITY nbsp   "&#160;">
<!ENTITY copy   "&#169;">
<!ENTITY reg    "&#174;">
<!ENTITY trade  "&#8482;">
<!ENTITY mdash  "&#8212;">
<!ENTITY ldquo  "&#8220;">
<!ENTITY rdquo  "&#8221;"> 
<!ENTITY pound  "&#163;">
<!ENTITY yen    "&#165;">
<!ENTITY euro   "&#8364;">
]>

<!-- header section and framework included by all the option pages. The
     callback to the including page is via template options-body -->

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl" exclude-result-prefixes="php">

<xsl:import href="../search/results.xsl" />  
<xsl:import href="includes.xsl" />

<xsl:template match="/*">
    <xsl:call-template name="surround" >
        <xsl:with-param name="sidebar">none</xsl:with-param>
    </xsl:call-template>
</xsl:template>

<!-- override ../includes to give full-width page contents -->
	<!-- 
		TEMPLATE: surround bd
		page body - main content
	-->
	<xsl:template name="surround_bd">
		<xsl:param name="sidebar" />
	
			<div id="bd" data-role="content">
			
				<xsl:call-template name="surround_bd_top" />
			
				<div id="yui-main">
					<div class="yui-u">
						<xsl:if test="string(//session/flash_message)">
							<xsl:call-template name="message_display"/>
						</xsl:if>
						
						<xsl:call-template name="main" />
					</div>
				</div>
				
				<xsl:if test="$sidebar != 'none' and $is_mobile != '1'">
					<xsl:call-template name="sidebar_wrapper" />
				</xsl:if>
	
			</div>
	</xsl:template>
	

<!-- override ../includes to add own js -->
<xsl:template name="javascript_include"> 
    <xsl:call-template name="jslabels" /> 
    <script src="javascript/jquery/jquery-1.6.2.min.js" language="javascript" type="text/javascript"></script> 
    <script language="javascript" type="text/javascript" src="javascript/jquery/jquery-ui-1.8.16.custom.min.js"></script>
    <link href="javascript/jquery/css/ui-lightness/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css" />
    <script src="javascript/pz2_target_checkboxes.js" language="javascript" type="text/javascript"></script> 
    <script src="javascript/pz2_options.js" language="javascript" type="text/javascript"></script>
</xsl:template>

<xsl:template name="tabs"> 
    <xsl:param name="template"/>

    <xsl:if test="config/groupby"> 
        <div class="tabs"> 
	    <ul id="tabnav"> 
		<xsl:call-template name="tab" > 
                      <xsl:with-param name="template" select="$template"/>
                </xsl:call-template>
            </ul> 
            <div style="clear:both"></div> 
        </div> 
    </xsl:if> 
</xsl:template>

<xsl:template name="tab">
	<xsl:param name="template"/>
	<xsl:for-each select="config/groupby/option"> 
        <li id="tab-{@id}">
            <xsl:choose>
                <xsl:when test="boolean(@id = $template)"> 
                    <xsl:attribute name="class">here</xsl:attribute>
                    <a>
                        <xsl:value-of select="@public" /> <xsl:text> </xsl:text> 
                    </a>
                </xsl:when>
                <xsl:otherwise>
                    <a href="{@url}" class="tab">
                        <xsl:value-of select="@public" />  
                    </a> 
                </xsl:otherwise>
            </xsl:choose>
        </li> 
    </xsl:for-each> 
</xsl:template>

<xsl:template name="user-options">
    <xsl:param name="template"/>

    <div id="user-options">
        <div id="current-options">
            <h2>Options currently selected</h2>
            <ul>
                <li><span class="title">Records to fetch:</span>
                    <xsl:choose>
                        <xsl:when test="//pazpar2options/user-options/max_records">
                            <xsl:value-of select="//pazpar2options/user-options/max_records"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="//config/max_records"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </li>
                <xsl:if test="//config/groupby/option[@id='access']">
                    <li><span class="title">User affiliation:</span>
                        <xsl:choose>
                            <xsl:when test="//pazpar2options/user-options/affiliation"><xsl:value-of select="//pazpar2options/user-options/affiliation"/></xsl:when>
                            <xsl:otherwise>
                                <xsl:text>Member of public</xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                    </li>
                </xsl:if>
                <li><span class="title">Libraries selected:</span>
                    By <xsl:value-of select="//pazpar2options/user-options/selectedby"/>
                </li>
                <li><span class="title">Libraries in use:</span>
                    <xsl:for-each select="//pazpar2options/user-options/targets/target">
                        <xsl:variable name="key" select="."/>
                        <xsl:value-of select="//all-targets/target[@target_id=$key]/display_name"/><xsl:if test="not(position() = last())">, </xsl:if>
                    </xsl:for-each>
                </li>
            </ul>
        </div>

        <div class="set-option">
            <h2>Select records to return</h2>
            <p>This option controls the maximum total number of records that can be returned from a search. The smaller the number, the faster the results are returned.</p>
            <div id="old-max-records">
                <xsl:attribute name="value">
                    <xsl:choose>
                        <xsl:when test="//pazpar2options/user-options/max_records">
                            <xsl:value-of select="//pazpar2options/user-options/max_records"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="//config/max_records"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
            </div>

            <form action="/pazpar2/options" id="max_records_form" method="post">
                <input type="hidden" id="new-max-records" name="max_records" value=''/>
                <div>
                    <span style="float:left;">Faster search </span> <div id="slider" style="float:left; width: 10em; margin: 5px 10px 5px 12px;"></div> <span style="float:left;"> More results</span>
                </div>
                <div style="clear:both"/>
                <input type="submit" name="records" value="Submit"/>
            </form>

        </div>

        <xsl:if test="//config/groupby/option[@id='access']">
            <div class="set-option">
                <h2>Select organisational membership</h2>
                <p>Giving your organisational membership allows you to see what access rights you have at different libraries.</p>
                <form>
                    <label for="affiliation">Your organisation:</label> 
                    <select id="affiliation" name="affiliation">
                        <xsl:for-each select="//config/affiliations/affiliation">
                            <option value="{@id}"><xsl:value-of select="@public"/></option>
                        </xsl:for-each>
                    </select><xsl:text>&nbsp;</xsl:text>
                    <label for="role">Your role:</label>
                    <select id="role" name="role">
                        <xsl:for-each select="//config/affiliations/affiliation/role">
                            <option value="{@id}"><xsl:value-of select="@public"/></option>
                        </xsl:for-each>
                    </select>
                </form>
            </div>
        </xsl:if>
                
        <div class="set-option">
            <h2><xsl:copy-of select="$text_header_select_library" /></h2>
            <xsl:call-template name="tabs" >
                <xsl:with-param name="template" select="$template"/>
            </xsl:call-template>
        </div>
    </div>

</xsl:template>

</xsl:stylesheet>
