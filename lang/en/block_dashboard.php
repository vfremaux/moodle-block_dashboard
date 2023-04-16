<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_dashboard
 * @category   blocks
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *  Exporter of dashboard data snapshot
 */

// Capabilitites.
$string['dashboard:addinstance'] = 'Can add an instance';
$string['dashboard:myaddinstance'] = 'Can add an instance to My Page';
$string['dashboard:configure'] = 'Can configure the block';
$string['dashboard:systempathaccess'] = 'Can configure output in system data path';
$string['dashboard:export'] = 'Export data (web service)';

// Privacy.
$string['privacy:metadata'] = 'The Dashboard block does not store any personal data about any user.';

$string['backtocourse'] = 'Back to course';
$string['backtoview'] = 'Back to block view';
$string['bar'] = 'Bars';
$string['bigresult'] = '"Big Results" protection';
$string['blockname'] = 'Dashboard';
$string['checktohide'] = 'Check box to hide title';
$string['choicevalue'] = 'Choice';
$string['cachefilterqueryerror'] = 'Error in cache query';
$string['cacheddata'] = 'Data from query cache';
$string['cleararea'] = 'Clear all file area';
$string['cleandisplayuptocolumn'] = 'Do not clean after column index';
$string['configbands'] = 'Hide show time bands';
$string['configbigresult'] = 'Enable Big Result option';
$string['configcache'] = 'Result Cache Settings';
$string['configcaching'] = 'Cache enabling';
$string['configcachingttl'] = 'Cache TTL';
$string['configcharset'] = 'Charset';
$string['configcleandisplay'] = 'Clean table display';
$string['configcoloredvalues'] = 'Color control values';
$string['configcolorfield'] = 'Color control field';
$string['configcolors'] = 'Background colors';
$string['configcolouring'] = 'Colouring output data';
$string['configcopy'] = 'Import the full configuration from a dashboard element';
$string['configcronfrequency'] = 'Frequency';
$string['configcronhour'] = 'Hour';
$string['configcronmin'] = 'Minutes';
$string['configcronmode'] = 'Cron refresh mode';
$string['configcrontime'] = 'Hour';
$string['configdashboardcron'] = 'Dashboard cron status';
$string['configdashboardparams'] = 'Dashboard data settings';
$string['configdata'] = 'GoogleMaps Markers Data';
$string['configdatatitles'] = 'Data titles';
$string['configdatatypes'] = 'Data types';
$string['configdisplay'] = 'Setup elements for display';
$string['configenablehorizsums'] = 'Enable horiz sums';
$string['configenablevertsums'] = 'Enable vertical sums';
$string['configeventmapping'] = 'Data mapping for events';
$string['configexplicitscaling'] = 'Explicit scaling';
$string['configexportcharset'] = 'Charset for CSV exports';
$string['configdescription'] = 'Description';
$string['configfileformat'] = 'File format';
$string['configfileheaders'] = 'File output headers';
$string['configfilelocation'] = 'Generated file location';
$string['configfilelocationadmin'] = 'Generated file location admin extension';
$string['configfileoutput'] = 'File output fields';
$string['configfileoutputformats'] = 'Field value formatting';
$string['configfilepathadminoverride'] = 'Generated file special path';
$string['configfilesqlouttable'] = 'Ouput SQL tablename (SQL file format)';
$string['configfilterdefaults'] = 'Default filter values';
$string['configfilterlabels'] = 'Filter labels';
$string['configfilteroptions'] = 'Filter options';
$string['configfilters'] = 'Filters';
$string['configformatting'] = 'Output data formatting';
$string['configgmdata'] = 'GoogleMaps Data';
$string['configgraphheight'] = 'Graph height';
$string['configgraphtype'] = 'Graph type';
$string['configgraphwidth'] = 'Graph Width';
$string['confighierarchic'] = 'Hierarchic rendering';
$string['confighidetitle'] = 'Hide block title';
$string['confighorizformat'] = 'Horiz keys formatting';
$string['confighorizkey'] = 'Horiz key';
$string['confighorizlabel'] = 'Horizontal labels';
$string['confighorodatefiles'] = 'Horodate files';
$string['configimportexport'] = 'Configuration import/export';
$string['configisrunning'] = 'Cron is running ? ';
$string['configlastcron'] = 'Last cron stamp ';
$string['configlat'] = 'Latitude';
$string['configlayout'] = 'Publish data ';
$string['configlineartable'] = 'Linear table settings';
$string['configlng'] = 'Longitude';
$string['configlocation'] = 'Location of the map';
$string['configlowerbandunit'] = 'Lower band time scale';
$string['configmakefile'] = 'Enable file generation';
$string['configmaptype'] = 'Map type';
$string['confignumsums'] = 'Summarizers';
$string['confignumsumsformats'] = 'Sums formatting';
$string['confignumsumslabels'] = 'Sums labels';
$string['configoutputfields'] = 'Output fields';
$string['configoutputfieldslabels'] = 'Output fields labels';
$string['configoutputformats'] = 'Output data formatting';
$string['configpagesize'] = 'Result paging size';
$string['configparams'] = 'User parameters are values that are given by the dashboard end user to alter the query behaviour or output range.';
$string['configparent'] = 'Parent';
$string['configparentserie'] = 'Parent serie';
$string['configquery'] = 'Query';
$string['configreminderonsep'] = '<span style="font-size:1.3em;color:#808080">Never forget the field separator is <b>necessarily</b> a ;</span>';
$string['configqueryrotate'] = 'Rotate results ';
$string['configrotatecolumns'] = 'Rotate colums ';
$string['configrotatepivot'] = ' around column ';
$string['configrotatenewkeys'] = ' with new PKeys ';
$string['configsendadminnotification'] = 'Send admin notification on cron';
$string['configserieslabels'] = 'Series labels';
$string['configshowdata'] = 'Show data';
$string['configshowfilterqueries'] = 'Show filter queries (debug)';
$string['configshowgraph'] = 'Show graph';
$string['configshowlegend'] = 'Show legend';
$string['configshowlowerband'] = 'Show the lower band';
$string['configshownumsums'] = 'Show summators';
$string['configshowquery'] = 'Show query (debug)';
$string['configsortable'] = 'Sortable table';
$string['configspliton'] = 'Split table on serie';
$string['configsplitsumsonsort'] = 'Split-sums column';
$string['configsplitsumsonsort_help'] = '';
$string['configsummators'] = 'Summarizers';
$string['configsums'] = 'Local sums settings';
$string['configtable'] = 'Table settings';
$string['configtablesplit'] = 'Data table split field';
$string['configtablesplit_help'] = 'If set, distinct values of this field will make separate subtables';
$string['configtabletype'] = 'Data table type';
$string['configtabular'] = 'Tabular layout extra settings';
$string['configtabulartable'] = 'Tabular table settings';
$string['configtarget'] = 'Target db';
$string['configtickspacing'] = 'Y tick spacing';
$string['configtimelinecolouring'] = 'Time line items colouring';
$string['configtimeunits'] = 'Time units';
$string['configtitle'] = 'Bloc title';
$string['configtreeoutput'] = 'Tree output series';
$string['configtreeoutputformats'] = 'Tree output series formatting';
$string['configtreeview'] = 'Tabular table settings';
$string['configtreeview'] = 'Tree view settings';
$string['configupperbandunit'] = 'Upper band time scale';
$string['configure'] = 'Configure';
$string['configverticalformats'] = 'Vertical keys formatting';
$string['configverticalkeys'] = 'Vertical keys';
$string['configverticallabels'] = 'Vertical labels';
$string['configxaxis'] = 'X axis serie';
$string['configxaxisfield'] = 'X axis ticks serie';
$string['configxaxislabel'] = 'X axis label';
$string['configyaxisbounds'] = 'Y Axis bounds (min, max)';
$string['configyaxislabel'] = 'Y axis label';
$string['configyaxisscale'] = 'Y scale type';
$string['configyaxistickangle'] = 'X labels angle';
$string['configymax'] = 'Y axis max';
$string['configymin'] = 'Y axis min';
$string['configyseries'] = 'Data series';
$string['configyseriesformats'] = 'Data series formatting';
$string['configzoom'] = 'Zoom';
$string['configqmin'] = 'Q min';
$string['configmapprovider'] = 'Map provider';
$string['configmapprovider_desc'] = 'OpenLayer is a free map system. Google Maps needs a paied key for displaying maps.';
$string['crontraceon'] = 'Dashboard cron trace';
$string['crontraceon_desc'] = 'If enable, generates a special <dataroot>/dashboards.log file for tracing croned executions.';
$string['csv'] = 'CSV records';
$string['csvfieldseparator'] = 'CSV Field separator';
$string['csvfieldseparator_desc'] = 'Sitewide value for CSV field separator';
$string['csvlineseparator'] = 'CSV Line separator';
$string['csvlineseparator_desc'] = 'Sitewide value for CSV Line separator';
$string['tab'] = 'Tabulation';
$string['coma'] = 'Coma';
$string['colon'] = 'Colon';
$string['semicolon'] = 'Semicolon';
$string['csvwithoutheader'] = 'CSV without heading line';
$string['daily'] = 'daily';
$string['dashboard_big_result_threshold'] = ' "Big Result" security trap threshold ';
$string['dashboard_big_result_threshold_desc'] = ' "Big Result" security trap threshold ';
$string['dashboard_cron_enabled'] = 'Cron activation';
$string['dashboard_cron_enabled_desc'] = 'Cron activation';
$string['dashboard_cron_freq'] = 'Weekly frequency';
$string['dashboard_cron_freq_desc'] = 'Weekly frequency';
$string['dashboard_cron_hour'] = 'Hour';
$string['dashboard_cron_hour_desc'] = 'Hour for the automated generation';
$string['dashboard_cron_min'] = 'Minutes ';
$string['dashboard_cron_min_desc'] = 'Minutes for the automated generation';
$string['dashboard_enable_isediting_security'] = 'Activates the editing secured mode.';
$string['dashboard_enable_isediting_security_desc'] = 'Enable the isediting security. In secured mode, Queries are not executed while course is in editing mode to prevent users loosing control over block setup.';
$string['dashboard_extra_db_db'] = 'Extra database (Postgre) : database ';
$string['dashboard_extra_db_db_desc'] = 'Extra database (Postgre) : database ';
$string['dashboard_extra_db_host'] = 'Extra database (Postgre) : hostname ';
$string['dashboard_extra_db_host_desc'] = 'Extra database (Postgre) : hostname ';
$string['dashboard_extra_db_password'] = 'Extra database (Postgre) : password ';
$string['dashboard_extra_db_password_desc'] = 'Extra database (Postgre) : password ';
$string['dashboard_extra_db_port'] = 'Extra database (Postgre) : port ';
$string['dashboard_extra_db_port_desc'] = 'Extra database (Postgre) : port ';
$string['dashboard_extra_db_user'] = 'Extra database (Postgre) : login ';
$string['dashboard_extra_db_user_desc'] = 'Extra database (Postgre) : login ';
$string['dashboard_output_encoding'] = 'Output encoding';
$string['dashboard_output_field_separator'] = ' Output field separator ';
$string['dashboard_output_line_separator'] = 'Output line separator';
$string['dashboardlayout'] = 'Dashboard layout';
$string['dashboards'] = 'Dashboards';
$string['dashboardstoragearea'] = 'Dashboard File Storage Area';
$string['datalocations'] = 'Geo Locations';
$string['datarefresh'] = 'Data refresh settings';
$string['datatitles'] = 'Marker titles';
$string['datatypes'] = 'Data Types';
$string['daterangevalue'] = 'Date Range';
$string['datevalue'] = 'Date';
$string['day'] = 'Day';
$string['dofilter'] = 'Filter';
$string['donut'] = 'Donut';
$string['dropconfig'] = 'Copy here the configuration string';
$string['plugindist'] = 'Plugin distribution';
$string['emulatecommunity'] = 'Emulate community version';
$string['emulatecommunity_desc'] = 'If enabled, the plugin will behave as the public community version. This might loose features !';
$string['editingnoexecute'] = ' You are in editing mode, the dashboard cannot execute queries';
$string['enabled'] = ' enabled';
$string['eventdesc'] = 'Event desc';
$string['eventend'] = 'Event end serie';
$string['eventlink'] = 'Link series';
$string['eventstart'] = 'Event start serie';
$string['eventtitles'] = 'Title data serie';
$string['eventtaskprocessed'] = 'Task processed event';
$string['eventtaskempty'] = 'Empty result task event';
$string['exportall'] = 'Export all data';
$string['exportconfig'] = 'Get the current config';
$string['exportdataastable'] = 'Export data as table';
$string['exportfiltered'] = 'Export filtered data';
$string['extradbparams'] = 'Extra DB parameters';
$string['filegenerated'] = 'File has been successfully generated';
$string['fileoutput'] = 'Data Export settings';
$string['filters'] = 'Filters';
$string['filesview'] = 'View filearea content';
$string['filegenerationfailed'] = 'File failed to be generated';
$string['filternotretrievable'] = 'Filter not retrievable';
$string['friday'] = 'Friday';
$string['from'] = 'from';
$string['generalparams'] = 'Access to dashboard settings';
$string['generatedexports'] = 'Generated exports';
$string['globalcron'] = 'Global cron settings';
$string['googlelocationerror'] = 'Google location error';
$string['googlemap'] = 'Google Map';
$string['googleparams'] = 'GoogleMaps settings';
$string['graphparams'] = 'Graph settings';
$string['guestsnotallowed'] = 'Guests are not allowed';
$string['hour'] = 'Hour';
$string['hours'] = 'Hours';
$string['importconfig'] = 'Import config';
$string['instancecron'] = 'Instance cron settings';
$string['invalidorobsoletefilterquery'] = 'Invalid or obsolete filterquery.';
$string['invalidorobsoletequery'] = 'Invalid or obsolete query.';
$string['line'] = 'Lines';
$string['linear'] = 'Linear';
$string['listvalue'] = 'Value List';
$string['licenseprovider'] = 'Pro License provider';
$string['licenseprovider_desc'] = 'Input here your provider key';
$string['licensekey'] = 'Pro license key';
$string['licensekey_desc'] = 'Input here the product license key you got from your provider';
$string['log'] = 'Logarithmic';
$string['maptypehybrid'] = 'Hybrid view';
$string['maptyperoadmap'] = 'Road map';
$string['maptypesatellite'] = 'Satellite view';
$string['maptypeterrain'] = 'Terrain';
$string['mins'] = 'Min';
$string['monday'] = 'Monday';
$string['month'] = 'Month';
$string['newdashboard'] = 'New dashboard';
$string['nodata'] = 'No data available';
$string['node'] = 'Data Node';
$string['nofiles'] = 'No files';
$string['noquerystored'] = 'No query stored';
$string['norefresh'] = 'No refresh';
$string['notretrievable'] = 'No data retrievable. You may be in editing mode and no previous data has been cached. This mode is forced to prevent loosing control of dashboard setup on strangling queries.';
$string['obsoletequery'] = 'Query seems being written for old Moodle 1.9 database.';
$string['outputfilegeneration'] = 'Output file generation';
$string['outputfiltered'] = 'Generate output file (filtered)';
$string['outputparams'] = 'Query output settings';
$string['pageexport'] = 'Page export';
$string['paramas'] = 'Provide param as ';
$string['paramasvar'] = 'query variable';
$string['paramassql'] = 'sql where';
$string['paramascol'] = 'output column';
$string['pie'] = 'Pie';
$string['plotgraphparams'] = 'Plotted graph settings';
$string['pluginname'] = 'Dashboard';
$string['publishinblock'] = 'in the block space';
$string['publishinpage'] = ' in separate page';
$string['querydesc'] = 'Query definition';
$string['queryparams'] = 'Query user parameters';
$string['rangevalue'] = 'Range';
$string['saturday'] = 'Saturday';
$string['savechangesandconfig'] = 'Save and continue configuration';
$string['savechangesandview'] = 'Save and view';
$string['securityparams'] = 'Security and performance settings';
$string['selectnone'] = '(none)';
$string['setup'] = 'Setup';
$string['sqlinserts'] = 'SQL INSERTs';
$string['sqlparamdefault'] = 'Default value';
$string['sqlparamlabel'] = 'Label';
$string['sqlparamtype'] = 'Type';
$string['sqlparamvalues'] = 'Values';
$string['sqlparamvar'] = 'SQL Variable Name';
$string['subtotal'] = 'Subtotal';
$string['sumsandfiltersparams'] = 'Summators settings';
$string['sunday'] = 'Sunday';
$string['tablecolormapping'] = 'Output table color mapping';
$string['tabular'] = 'Tabular';
$string['tabularparams'] = 'Tabular layout extra settings';
$string['task_exportdata'] = 'Data automated export';
$string['textvalue'] = 'Text';
$string['thursday'] = 'Thursday';
$string['timegraph'] = 'Time curves';
$string['timeline'] = 'Timeline';
$string['timelinecolorfield'] = 'Timeline colouring field';
$string['timelineparams'] = 'Timeline extra settings';
$string['to'] = 'to';
$string['toomanyrecordsusepaging'] = 'This query has too many results. Paging for this query has been forced';
$string['total'] = 'Total';
$string['treeview'] = 'Tree view';
$string['treeviewparams'] = 'Tree view extra settings';
$string['tuesday'] = 'Tuesday';
$string['viewdashboard'] = 'View dashboard';
$string['wednesday'] = 'Wednesday';
$string['week'] = 'Week';
$string['year'] = 'Year';
$string['sums'] = 'Summators';

$string['configbands_help'] = 'A time line graph has two timebands one over the other. The second timeband has more precision.';
$string['configtimeunits_help'] = 'Setup the time units and scale for each band.';

$string['configdelayedrefresh'] = 'Delayed refresh for dashboard data';

$string['configdelayedrefresh_help'] = '
<p>Output to file can be delayed to a suitable time by programming refresh scheduling</p>
<p>You can choose to schedule your output using the general settings for cron activation, or
choose an instance level schedule</p>
';

$string['configmakefile_help'] = '
<p>When data is refreshed in cache, you can choose or not to generate an output file with refreshed data</p>
';

$string['configsummators_help'] = '
<p>Series on which calculate a global sum</p>
';

$string['configfileoutput_help'] = '
File can use a distinct set of output field than what is required for display. If empty, the output columns will have
same definitions as the display.
';

$string['configfileheaders_help'] = '
You can define the effective headers of the exported CSV file for each output column. If empty, the output SQL field aliases will
be used as default values.
';

$string['configfilesqlouttable_help'] = '
<p>In case file format is outputing an SQL inserts, youl\'ll need tell the table name in which records go in.</p>
';

$string['configfilepathadminoverride_help'] = 'Administrators may need to generate the files outside the Moodle controlled area';

$string['configformatting_help'] = '
<p>This parameters allow to give a formatting instruction to data, based on the typical "sprintf"
    syntax.</p>
<p>When formatting a semi-column separated list, the formatting list should present an identical number
    of formatting statements. An empty formatting string will pass through the original information.</p> 

<h4>Example:</h4>
<p>Using a query that defines a frequentation ratio</p>
<pre>
</pre>
<p>Say we want to displays some results as float float using one decimal point formatting.</p>
<p>Parameters "output fields" and "output formatting", would be:</p>
<pre>mois;ratio</pre>
<pre>;%.1f</pre>
';

$string['configfilters_help'] = '
<h3>Filters</h3>
<p>Filters allow giving dashboard end users capability of filtering information at view time. When filters are added, 
form selects allow users to filter the data from the modalities that were found in real data. (Unused values WILL NOT appear in filtering list).<br/>
Filters can accept multiple seletion mode and be preset to some values using options.</p>
<p>Filter can be defined as a list (;) of column definitions that need to be present in the 
query field list</p>
<p>Filters should be aliased columns, and mention the complete column definition including aliasing
clause</p>
<h4>Example :</h4>
<p>Using the query</p>

<pre>
    SELECT
       DATE_FORMAT(FROM_UNIXTIME(l.time), \'%Y\') as year,
       DATE_FORMAT(FROM_UNIXTIME(l.time), \'%m\') as month,
       count(l.id) as queries
    FROM
        mdl_log l
    GROUP BY
        year,month
</pre>

<p>Filter value should mention:</p>
<pre>DATE_FORMAT(FROM_UNIXTIME(l.time), \'%Y\') as year</pre>

<p>To setup distinct filters for resp. year and month, you should write:</p>
<pre>DATE_FORMAT(FROM_UNIXTIME(l.time), \'%Y\') as year<b>;</b>DATE_FORMAT(FROM_UNIXTIME(l.time), \'%Y-%m\') as month</pre>

<p>Beware : when using two filters, each of them provides its value list independently. Thus some filtering combination
could result in "no values" ar all.</p>

<h4>Query configuration</h4>

<p>Filters operate on query results depending on a  "&lt;%%FILTERS%%&gt;" marker that can complete or replace a WHERE clause.</p>
<p><u>Valid location samples:</u></p>
<pre>
    SELECT
      data1,data2
    FROM
       table1 t1,
       table2 t2
       <span style="color:green">&lt;%%FILTERS%%&gt;</span>
    ORDER BY
       data1
</pre>
<pre>
    SELECT
      data1,data2
    FROM
       table1 t1,
       table2 t2
    WHERE
        t1.id = t2.t1key
       <span style="color:green">&lt;%%FILTERS%%&gt;</span>
    ORDER BY
       data1
</pre>

<p><u>Invalid location samples:</u></p>
<pre>
    SELECT
      data1,data2
    FROM
       table1 t1,
       table2 t2
    GROUP BY
        data2
       <span style="color:red">&lt;%%FILTERS%%&gt;</span>
    ORDER BY
       data1
</pre>
';

$string['configfilterdefaults_help'] = '
<h3>Default option for filters</h3>

<p>Filters can be setup with a default seletion.</p>
<p>When setup, the data will be filtered at first rendering of the dashboard in page. This may be usefull for
queries that would output a lot of results. In the case a preselection is made, some options could moreover
avoid to browse back to the "all data" situation.</p>

<p>Note that filtering values are ordered.</p>

<h4>Several filters</h4>

<p>When several filters are setup using  ";" separator, then default values should also be defined as a list
(";" separated). Non used values should be left as "empty strings".</p>

<h4>Special values</h4>

<p>Some values allow having a dynamic effect in defaults:</p> 
<ul>
    <li>"LAST" : preset the filtering with the last value in list. (applied to ASC ordered dates, will stand for most recent).</li>
    <li>"FIRST" : preset the filtering with the last value in list. (applied to ASC ordered dates, will stand for oldest).</li>
</ul>
';

$string['configxaxis_help'] = '
    Defines serie for Xaxis
';

$string['configfilteroptions_help'] = '
<h3>Filter options</h3>
<p>Some special options can help to enrich or solved query struture isues. Each option string is a string with one char
enablers that may be present (enabled) or absent (disabled).</p>
<h4>Values</h4>
<ul>
    <li><b>m</b> : (multiple) makes the selector multiple so that set of possible values or ranges can be asked for.</li>
    <li><b>s</b> : (single) avoid using the "*" wildcard. When enabled the default is forced to "FIRST" if undefined.
    Un filtre en mode "s" exclut le précédent.</li>
    <li><b>x</b> : (crossthrough) some queries strutures (f.e. UNION) do not admit the query transform usually processed to get filter values.
    You may try to disable this processing with the "x" option on. Sometimes it works.</li>
</ul>

<h4>Several filters</h4>

<p>When several filters are setup using  ";" separator, then default values should also be defined as a list
(";" separated). Non used values should be left as "empty strings".</p>

<h4>Example</h4>

<p>Say we use filters:</p>
<pre>year;month;day</pre>

<p>options</p>

<pre>s;m;</pre>

<p>will allow choosing only one year for data, a month selection, and one day or all day range.</p>
';

$string['configmaptype_help'] = '';

$string['configeventmapping_help'] = '';

$string['configgmdata_help'] = '
<h3>Geolocated information</h3>

<p>Plotting geolocated information onto a GoogleMap assumes geographic information is available either as gelocation coordinates
    or address information. The dashboard block can handle the transposition of human readable address elements into a gelocation
    coordinate and will cache this information.</p>
<p>Geocoding is subject to Google Geocoding API terms of service. Free unregistered conversion rate is limited to 2500 requests per
day. Read the <a href="http://code.google.com/intl/fr/apis/maps/documentation/geocoding/" target="_blank" >Geocoding API of Google</a>
for more information.</p>

<h4>Geographic data settings</h4>

<p>Geolocated information are plotted on map as graphical markers. Markers can be defined as :</p>
<ul>
<li>A quadruplet: Title, Latitude, Longitude, Marker Class</li>
<li>A sextuplet: Title, Address, Post code, City, Region Code, Marker Class</li>
</ul>

<p>Information fields have following specification:</p>

<ul>
<li><i>Title</i>: Textual label of the marker</li>
<li><i>Latitude</i>: floating point latitude</li>
<li><i>Longitude</i>: floating point longitude</li>
<li><i>Address</i>: road information</li>
<li><i>Post code</i>: Official post or zip code</li>
<li><i>City</i>: City name</li>
<li><i>Marker Class</i>: A classname, that will bind to a graphical icon</li>
</ul>

<p>Setting fields let you bind required information to request output fields. Setting outputs will usually accept one or
more field (or alias) names separated by semicolons (";").</p>
<ul>
<li><i>Title input</i>: Query output fieldname providing the textual label</li>
<li><i>Location input</i>: 
<ul>
<li><u>Case 1</u>: THE output column name providing the geolocation couple as a comma pair of floating point
values : "lat,lng" (ex : 47.098456,1.4534456)</li>
<li><u>Case 2</u>: A semicolon separated list of query output fieldnames (or aliases) that provide in order:
address, post code, city, and region code (*)</li>
</ul>
</li>
<li><i>Marker type input</i>: The query output fieldname that provides a class label</li>
</ul>

<p>(*) Some constant values can be given for city, post code and region code, using quoted values in place of field name: </p>
<pre>address;cp;city;"GB"</pre>
<p>will always provide the value "GB" as region code.</p>
';

$string['confighierarchic_help'] = '
Hierarchic rendering
';

$string['configoutputfields_help'] = '
<h3>Data output</h3>

<p>Specifies which output fields will be used in the output table (display data).</p>
<p>Fields must be mentionned using real or aliased column identity. SQL statement must be represented by a named alias.
Multiple output columns must be separated by semi-columns (;).</p>
<h4>Example:</h4>
<p>If query is: </p>
<pre>
SELECT
   YEAR(FROM_UNIXTIME(time)) as year,
   count(*) access
FROM
   mdl_log
GROUP BY
year
</pre>
<p>Output columns could be defined as:</p>
<pre>year;access</pre>

<h4>Special features on data output</h4>

<p>When a column name of the output is mentionned as</p> 
<pre>S(<i>column_name</i>)</pre> 
<p>output values will be accumulated in order of the
display. </p>
';

$string['configquery_help'] = '
<p>Dashboard will produce a vue based on a data query submitted to the database.</p>
<p>This query:</p>
<ul>
    <li>needs define output fields (using AS aliasing)</li>
    <li>can use JOIN or complex UNIONs in some cases</li>
    <li>can use aggregating operators and GROUP BY clauses</li>
    <li>should NOT have an ORDER BY if data is intended to be displayed in data tables</li>
</ul>

<h3>Placeholder for filters</h3>
<p>If some data filtering is to be used, then a &lt;%%FILTERS%%&gt; tag needs to be inserted as placeholder in
the original query as WHERE clause or for completing a WHERE clause</p>
';

$string['configsplitsumonsort_help'] = '
<h3>Subsums on data tables</h3>

<p>When data can be sorted,  one column can serve as criteria for subsplitting summators. Each summator will have
    a subtotal calculated and displayed in an intermediate row in the table when separation value changes.</p>
<p>The criteria must be an output column alias.</p>
<p>Note that only linear table can use this feature.</p>
';

$string['configtabletype_help'] = '
<h3>Data presentation table type</h3>

<p>This selector allows choosing the type of table that will be used for presenting data :</p>
<ul><li><b>Linear</b>: Data re linearily displayed as records</li>
<ul><li><b>Tabular</b>: data are presented within bidimensional tabular array. Extra params will need setup:
    <ul><li>the unique column driving horizontal dimension</li>
        <li>Colomuns driving the vertical dimension.</li>
        <li>Data used for filling cells</li>
    </ul>
    </li>
    <li><b>Tree view</b>: If data have a hierachical organisation embedd (id,parent)
    this mode draws a hierarchical tree representation of records.</li>
</ul>
';

$string['configyseries_help'] = '
<h3>Graphical data serie</h3>

<p>Data series will be plotted on graph display. Depending on the graph type, you will be able to specify one or more data series for plotting.
Data series MUST be output column names (or aliases) and are given as a semi-column (";") separated list.</p>
<p>Names of series can be defined in the textarea at the right. Labels will be separated by semi-columns (";").</p>

<h4>Special features on data series</h4>

<p>When a serie is mentionned as :</p>
<pre>S(<i>serie_name</i>)</pre>
<p>output values will be accumulated in order of the display. </p>
';

$string['configbigresult_help'] = '
<h3>Big results security bypass</h3>
<p>Statistic queries may have dramatic performance impact moreover when having erroneous joins. To avoid GUI break when setting up queries,
    a security was added to force result pagination and results size limiting in edition mode.</p>
<p>This might be problematic in some extreme cases, f.e. when producing curves whit a lot of data. </p>

<p>When enabled, the security is disengaged and big results an be produced for the dasboard layout.</p>
<p>We may encourage strongly to use result caching to preserve performance for other users.</p>
';

$string['configpagesize_help'] = '
<h3>Page size</h3>
<p>When not null, forces results to be paged with page size results per page</p>.
';

$string['configcaching_help'] = '
<h3>Caching</h3>
<p>Enabling cache will allow dashboard to fetch data in a pre-stored result in a cache table, thus
saving a lot of database power.</p>
<p>This is particularily useful on statistics results consuming a lot of records to produce a small
amount of output data. The cron automation will allow to shedule at appropriate time the refresh
of the cache, combined with cache TTL value.</p>
';

$string['configcolouring_help'] = '
<h3>Colouring output results</h3>
<p>The colouring settings allows to map colours to data value range in one column or result.</p>
<p>On the left side textarea, you may declare a set of colours (one per line). In the field located
in the middle, you will enter an alias output field name. This output field will be processed for
coulouring data. At this moment only one output column can be proessed this way.</p>
<p>On the right most textarea, you\'ll have to give one corresponding data test for each colour.
This data test can be any arithmetical expression using %% marker for the effective result value.</p>
<h4>Example</h4>

<pre><code>
    #FF0000                             %% < 0
    #00FF00                             %% > 0
    #0000FF     ->    outputcolumn  ->  %% == 0
</code></pre>

<p>would colour in red negative values, in green positive values and let zeroes in blue. </p>
<p>Note colour is applied to text background.</p>
 
';

$string['configtabular_help'] = '
<h3>Tabular results</h3>

<p>Tabular display show query results in a bidimensional table, using a special output field to produce
columns and a set of fields to produce hierachic organised row captions.</p>
<p>The printed data in cell is the value of the specified "output field", or in case it has been defined as
a list, a n-uplet of values comming from correeponding output fields.
';

$string['configspliton_help'] = '
<h3>Tabular table split</h3>

<p>You can choose a field (column alias name) that will be used to split the output table in several subtables. the data is forced ordering
on the specified field, and eah time a new value il encountered, a table splitter is added reminding column names.</p>
';

$string['configsums_help'] = '
<h3>Tabular table row and column sums</h3>

<p>When enabling this feature, each row (resp. columns) will have an extra column (resp. row)  with the
sum of the cell value of the entire row. Note in ae of multiplet in cell, only the first numerial field is summated.</p>
';

$string['configparentserie_help'] = '
<p>This is the output field (resul column alias) that should contain a "parent" information, relative to the natural id of the result.
The natural id refers to the first field of the result row.</p>
';

$string['configtreeoutput_help'] = '
<p>this field contains the list of columns providing the tree node caption.</p>
';

$string['configzoom_help'] = '
<p>Google zoom factor. This helps to focus the appropriate geographic zone in the Google viewport.</p>
';

$string['configlocation_help'] = '
<p>The central point of the map shown in viewport. You may retrieve directly those values using GoogleMaps interface.</p>
';

$string['configxaxisfield_help'] = '
<p>The output field that will provide ategory information for the X axis. This is essentially used for :</p>
<ul>
<li>Timerange line graphs</li>
<li>Bargraphs with categroy axis</li>
</ul>
';

$string['configexplicitscaling_help'] = '
<p>Usually, JQPlot will automate the graph axis scale and viewport bounds. You may force some bounding and grid parameters here.
JQPlot may not always follow your recomendations exaly in some cases (unknown reasons).</p>
';

$string['configfilelocation_help'] = '
<p>When cron data refresh is programmed on a cached dashboard, a file output an be scheduled. This setting will define the file
path the file has to be generated in.</p>
<p><b>Beware :</b> This path needs to bind to an existing storage context.</p>
';

$string['configfilelocationadmin_help'] = '
<p>If you see this option, you probably are a site administrator. 
This allows to add a special path start from dataroot in order to store the genrated file in a non standard location in moodledata.</p>';

$string['confighorodatefiles_help'] = 'When enabled, a timestamp will be added at the end of each generated filename';

$string['configqueryrotate_help'] = '
# Result rotation

Because SQL queries cannot make the output column set dynamic, SQL queries cannot query for matricial results. Results
rotation is a way to transform a flat result into a dynamic bidimensional matrix. This is usefull e.g. for feeding 
several curves in a multiple serie graph from a single flat query source. rotation will convert some field result rows
into an expansion of the results columns, remapping the pivot column data to form a bidimensional matrix.

';

$string['plugindist_desc'] = '<p>This plugin is the community version and is published for anyone to use as is and check the plugin\'s
core application. A "pro" version of this plugin exists and is distributed under conditions to feed the life cycle, upgrade, documentation
and improvement effort.</p>
<p>Please contact one of our distributors to get "Pro" version support.</p>
<ul><li><a href="http://service.activeprolearn.com/local/shop/front/view.php?id=1">ActiveProLearn SAS</a></li>
<li><a href="http://www.edunao.com">Edunao SAS</a></li></ul>';

$string['configserieslabels_help'] = '
# Y series labels

When plotting data series into graphs, Y series will by default be named with the X serie values. You may use this field to
provide alternate labels for on screen printing. Labels should be given as a semicolon separated list in the order of
the X serie values.
';

$string['tablecolormapping_help'] = '
You may choose to colorize results (textual) in one output column using value filters mapped to HTML color definitions :
  - in the left side text area enter an evaluation expression replacing value by %% (f.e. %% == 0).
  - in the right side text area, enter the html colour code.
';

$string['configparent_help'] = '
Give the alias of the query output field that designates the hierarchic binding. Often named "parent".
';
