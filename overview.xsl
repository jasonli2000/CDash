<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0'>

  <xsl:include href="header.xsl"/>
  <xsl:include href="footer.xsl"/>

  <xsl:include href="local/header.xsl"/>
  <xsl:include href="local/footer.xsl"/>

  <xsl:output method="xml" indent="yes"  doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />

  <xsl:template match="/">
    <html>
      <head>
        <title><xsl:value-of select="cdash/title"/></title>
        <meta name="robots" content="noindex,nofollow" />
        <link rel="StyleSheet" type="text/css">
          <xsl:attribute name="href"><xsl:value-of select="cdash/cssfile"/></xsl:attribute>
        </link>
        <xsl:call-template name="headscripts"/>

        <!-- Include static css -->
        <link href="nv.d3.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css"/>

        <!-- Include JavaScript -->
        <script src="javascript/cdashBuildGraph.js" type="text/javascript" charset="utf-8"></script>
        <script src="javascript/cdashAddNote.js" type="text/javascript" charset="utf-8"></script>
        <script src="javascript/d3.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="javascript/nv.d3.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="javascript/linechart.js" type="text/javascript" charset="utf-8"></script>
        <script src="javascript/bulletchart.js" type="text/javascript" charset="utf-8"></script>

        <!-- Generate line charts -->
        <script type="text/javascript">
          <xsl:for-each select='/cdash/measurement'>
            <xsl:variable name="measurement_name" select="name"/>
            <xsl:variable name="measurement_nice_name" select="nice_name"/>
            <xsl:for-each select='group'>
              var <xsl:value-of select="group_name"/>_<xsl:value-of select="$measurement_name"/> =
                <xsl:value-of select="chart"/>;
              make_line_chart("<xsl:value-of select="group_name"/>" + " " + "<xsl:value-of select="$measurement_nice_name"/>",
                              "#<xsl:value-of select="group_name"/>_<xsl:value-of select="$measurement_name"/>_chart svg",
                              <xsl:value-of select="group_name"/>_<xsl:value-of select="$measurement_name"/>);
            </xsl:for-each>
          </xsl:for-each>

          <xsl:for-each select='/cdash/coverage'>
            var <xsl:value-of select="name"/> = <xsl:value-of select="chart"/>;
            make_line_chart("<xsl:value-of select="nice_name"/> coverage",
                            "#<xsl:value-of select="name"/>_coverage_chart svg",
                            <xsl:value-of select="name"/>);
            make_bullet_chart("<xsl:value-of select="nice_name"/> coverage",
              "#<xsl:value-of select="name"/>_coverage_bullet svg",
              <xsl:value-of select="min"/>,
              <xsl:value-of select="average"/>,
              <xsl:value-of select="max"/>,
              <xsl:value-of select="current"/>,
              <xsl:value-of select="previous"/>,
              25);
          </xsl:for-each>
        </script>
      </head>

      <body bgcolor="#ffffff">

        <xsl:choose>
        <xsl:when test="/cdash/uselocaldirectory=1">
          <xsl:call-template name="header_local"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:call-template name="header"/>
        </xsl:otherwise>
        </xsl:choose>

        <table class="table-bordered table-responsive table-condensed container-fluid">
          <tr class="row">
              <th class="col-md-1"> </th>
                <xsl:for-each select='/cdash/group'>
                  <th class="col-md-2" colspan="2">
                    <xsl:value-of select="name"/>
                  </th>
                </xsl:for-each>
          </tr>

          <xsl:for-each select='/cdash/measurement'>
            <xsl:variable name="measurement_name" select="name"/>
            <tr class="row">
              <td class="col-md-1">
                <b><xsl:value-of select="nice_name"/></b>
              </td>
              <xsl:for-each select='group'>
                <td class="col-md-1">
                  <xsl:value-of select="value"/>
                </td>
                <td class="col-md-1" id="{group_name}_{$measurement_name}_chart" style="height:51px;">
                  <svg></svg>
                </td>
              </xsl:for-each>
            </tr>
          </xsl:for-each>

          <xsl:for-each select='/cdash/coverage'>
            <tr class="row" style="height:50px;">
              <td class="col-md-1"><b><xsl:value-of select="nice_name"/> coverage</b></td>
              <td class="col-md-1">
                <xsl:value-of select="current"/>%
              </td>
              <td id="{name}_coverage_chart" class="col-md-1" style="height:50px;">
                <svg></svg>
              </td>
              <td id="{name}_coverage_bullet" class="col-md-4" colspan="4" style="height:50px; width:100%;">
                <svg></svg>
              </td>
            </tr>
          </xsl:for-each>
        </table>

        <!-- FOOTER -->
        <br/>
        <xsl:choose>
        <xsl:when test="/cdash/uselocaldirectory=1">
          <xsl:call-template name="footer_local"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:call-template name="footer"/>
        </xsl:otherwise>
        </xsl:choose>

        </body>
      </html>
    </xsl:template>
</xsl:stylesheet>
