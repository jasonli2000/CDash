<xsl:stylesheet
xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0'>

<xsl:template name="builds">
    <xsl:param name="type"/>
				
							 <div class="section">
    
				 
   <xsl:if test="count($type/build)=0">
              <h4>No <xsl:value-of select="name"/> Builds</h4>
   </xsl:if>
   
    <xsl:if test="count($type/build)>0">
          <h4><xsl:value-of select="$type/name"/></h4>
   
			<ul>
            <li>
												  <h5>
												  <table width="97%" cellpadding="0" cellspacing="0">
														<tr class="sectionheader">
														<td width="8%">U</td>
														<td width="8%">C</td>
														<td width="8%">E</td>
														<td width="8%">W</td>
														<td width="8%">TP</td>
														<td width="8%">TF</td>
														<td width="8%">TNR</td>
														</tr>
														</table>
					         </h5>
			
			   <xsl:for-each select="$type/build">														
																							
											<a href="test2" class="buildlink">
			          <table width="97%" height="32" cellpadding="0" cellspacing="0">
													<tr class="sectionbuildodd">
														<td width="100%" style="text-align: left;" colspan="7" >
														<xsl:value-of select="site"/>-<b><xsl:value-of select="buildname"/></b>-<xsl:value-of select="builddate"/></td>
														</tr>
												<tr  class="sectionbuildeven" valign="middle">
														<td width="8%"><xsl:value-of select="update"/></td>
														<td width="8%">
														 <xsl:attribute name="class">
															<xsl:choose>
																	<xsl:when test="configure > 0">
																			error
																			</xsl:when>
																		<xsl:when test="string-length(configure)=0">
																			tr-odd
																			</xsl:when>     
																	<xsl:otherwise>
																		normal
																		</xsl:otherwise>
															</xsl:choose>
													</xsl:attribute>
														<xsl:value-of select="configure"/></td>
														<td width="8%">
														 <xsl:attribute name="class">
																<xsl:choose>
																		<xsl:when test="compilation/error > 0">
																				error
																				</xsl:when>
																			<xsl:when test="string-length(compilation/error)=0">
																				tr-odd
																				</xsl:when>     
																		<xsl:otherwise>
																			normal
																			</xsl:otherwise>
																</xsl:choose>
														</xsl:attribute>
														<xsl:value-of select="compilation/error"/></td>
														<td width="8%">
														 <xsl:attribute name="class">
															<xsl:choose>
																	<xsl:when test="compilation/warning > 0">
																			warning
																			</xsl:when>
																		<xsl:when test="string-length(compilation/warning)=0">
																			tr-odd
																			</xsl:when>   
																	<xsl:otherwise>
																		normal
																		</xsl:otherwise>
															</xsl:choose>
													</xsl:attribute>
														<xsl:value-of select="compilation/warning"/></td>
														<td width="8%">
														<xsl:attribute name="class">
        						<xsl:choose>
																<xsl:when test="test/fail > 0">
																		warning
																		</xsl:when>
																			<xsl:when test="string-length(test/fail)=0">
																		tr-odd
																		</xsl:when>       
																<xsl:otherwise>
																	normal
																	</xsl:otherwise>
														</xsl:choose>
												</xsl:attribute>
														<xsl:value-of select="test/pass"/>
														</td>
														<td width="8%">
														 <xsl:attribute name="class">
														<xsl:choose>
																<xsl:when test="test/fail > 0">
																		warning
																		</xsl:when>
																<xsl:when test="string-length(test/fail)=0">
																		tr-odd
																		</xsl:when>  
																<xsl:otherwise>
																	normal
																	</xsl:otherwise>
														</xsl:choose>
												</xsl:attribute>
										<xsl:value-of select="test/fail"/>							
														</td>
														<td width="8%">
														<xsl:attribute name="class">
																<xsl:choose>
																		<xsl:when test="test/notrun > 0">
																				error
																				</xsl:when>
																		<xsl:when test="string-length(test/notrun)=0">
																				tr-odd
																				</xsl:when>    
																		<xsl:otherwise>
																			normal
																			</xsl:otherwise>
																</xsl:choose>
														</xsl:attribute>
														<xsl:value-of select="test/notrun"/>		
														</td>
														</tr>
					    </table>
												</a>
  </xsl:for-each>
              </li>
            </ul>
				
		</xsl:if>
</div>
</xsl:template>
    
				
    <xsl:output method="html"/>
    <xsl:template match="/">
      <html>
       <head>
  
		     <title><xsl:value-of select="cdash/title"/></title>
         <meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=1;"/>
								<style type="text/css" media="screen">@import "iphone.css";</style>
									<script type="application/x-javascript" src="jquery-1.1.4.js"></script>
									<script type="application/x-javascript" src="jquery-iphone.js"></script>
									<script type="application/x-javascript" src="iphone.js"></script>
									
									</head><body orient="landscape">

    <h1 id="pageTitle">CDash</h1>
    <a href="http://cdash.org/iphone" class="home"></a>
			 <a class="showPage button" href="#loginForm">Login</a>
				<a class="showPage title">CDash by Kitware Inc.</a>
     
   	 <ul id="projects" title="Project" selection="true" class="nobg">
        <li>        
          <h3><a href="http://www.itk.org"><xsl:value-of select="cdash/dashboard/projectname"/></a></h3>
										
                <div class="news-details">
																<div><xsl:value-of select="cdash/dashboard/datetime"/></div>
                <div><a>
																<xsl:attribute name="href">project.php?project=<xsl:value-of select="cdash/dashboard/projectname"/>&amp;date=<xsl:value-of select="cdash/dashboard/previousdate"/>
																</xsl:attribute>[Previous]</a>
																<a>
																<xsl:attribute name="href">project.php?project=<xsl:value-of select="cdash/dashboard/projectname"/>&amp;date=<xsl:value-of select="cdash/dashboard/nextdate"/>
																</xsl:attribute>[Next]</a>
																</div>
																</div>
       
							
<xsl:for-each select="cdash/buildgroup">
  <xsl:call-template name="builds">
  <xsl:with-param name="type" select="."/>
  </xsl:call-template>
</xsl:for-each>

     </li>
     </ul>
    <form id="loginForm" class="dialog" method="post" action="/login">
        <fieldset>
            <h1>Login</h1>
            <label class="inside" id="username-label" for="username">Username...</label> 
            <input id="username" name="side-username" type="text"/>

            <label class="inside" id="password-label" for="password">Password...</label>
            <input id="password" name="side-password" type="password"/>
            
            <input class="submitButton" value="Login" type="submit"/>
            <input name="processlogin" value="1" type="hidden"/>
            <input name="returnpage" value="/iphone" type="hidden"/>
        </fieldset>
    </form>
				
        </body>
      </html>
    </xsl:template>
</xsl:stylesheet>
