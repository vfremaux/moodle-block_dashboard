Dashboard element block
##############################

the dashboard element bloc is of use with flexible Moodle
page formats allowing free setup of the page layout using blocks.

It implements a "dashboard element" capable to plot and render in
many graphic output formats any result of a complex query presented
to the Moodle database (or an eventual external PostGre schema).

Given a full query, the dashboard block mashes up : 

- Data table output
   + Linear (records) data table
   + Tabular (cross dimension) tables
   + Treeview (on hierarchical mapped results)
   
- Graphic plotting using JQPlot
   + Line graphs
   + Bar graphs
   + Pie graphs
   + Donuts graphs
   
- Geographic plotting (using GoogleMaps API)
   + Google Maps + query data plotting
   + Possibility to change icons and plor mutiple class data using course files
   + Geocoding requests to Google handled (up to 2500 per day) with static long term caching
   => Will evolve

- Time special plotting : Integrating SIMILE Timeline plugin
   + Plot on a timeline plugin of "instant" and "time duration" events
   + possibility of setting up color classes for events
   + possibility to customize "instant" pin icons from course files
   => Will evolve

- Data output additional features
  + Data output formatting (using sprintf formatting)
  + Filters (selecting output or query fields for filtering)
  + Summators : displaying computed sums of output fields
  + Cumulative mode : Produces cumulative sum of a field without extra query
  + Data colouring and marking (on tables) using comparison formulas (ex: %% == 0 )
  + Mappable Labels for ALL query field names

- Performance features 
  + Result caching (programmable retension delay)
  + Croned refresh of cache
  + Instance adjustable refresh mode or global cron settings 

# Install
#########

1. Deploy the block in Moodle/blocks as usual

2. Activate notifications to install the data model

3. Create a course, better a flexipage or paged format course if available

4. Add a Dashboard block and write a query

5. Define output fields, output modes, output labels... and discover features....

# Library locations
########################################

Libraries will work from inside the block dashboard. 

A further version might propose to move them into a "shared libs framework" local plugin for code packaging optimisation.

# Install additive for running timelines
########################################

Timeline needs to be post page loading activaed. We got some issue to stack onload events
on body element. The solution is till now to add a late call to initialisation in footer : 

Add this statement : 

    if (function_exists('timeline_initialize')) timeline_initialize();

As latest possible statement in footer.

Enjoy nice plotting... 