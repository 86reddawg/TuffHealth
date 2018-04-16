# TuffHealth
Web app to help count calories and track other health related data

Setup:
Import the health.sql file into mysql which will create the database structure and populate it with some data.
Data in the stats and user tables should be edited manually at the moment.

The bulk food database has been left in, but it's in a state of rewrite currently.  The end goal is to have
all solid foods in units of grams and all liquids in units of oz.

There is no config file currently, so database login info should be manually programmed in ajax_refresh.php, get_food.php, and functions.php

The diary has been left with open access ($loggedin=True).  When done editing the diary for the day, click on the lock
icon to lock the day.  The main graphing page ignores any day that hasn't been locked.  Use the heartbeat icon to add daily
weight (it doesn't currently show stored values).  Eventually this popup will be where blood pressure and other stats can
be entered.  To create a new food item or modify existing in the database, click on the shopping cart icon.

When the diary date is unlocked, you can use the plus icon by each meal to add food.  Use the minus icon to the right to remove
any food items.  Click on the name of the food to modify the servings.  The serving size input can handle simple math (for
instance, a serving size of 1/2 gets processed to 0.5)

The calendar icon with a plus inside under each meal heading allows the user to copy that day's meal to another day.

The main graph page allows for zoom with the mouse by drawing a box around the date range inside the graph.  Clicking on
a specific day opens another window with the food diary for that day.