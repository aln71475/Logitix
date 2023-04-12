This first section will explain about the get_events.php file.

The first thing we did in this file is import the config.php file which contains database connection. Then there I definde three variable firstcall, total_calls and now_calling. The firstcall will store boolean and is defined to know the first time we call the API. The total_calls will contain information about total API calls we need to make to get all the data from API (because API returns data in pagination). And the now_calling will contain information about the no. of current call that we will make. All there variables are used in the while loop.

So in an infinite while loop the first condition will check if now_calling > total_calls if true it will break out of while loop.Then after this condition we have code for calling the API and saving it in a variable named someArray. Then with if condition we will check if in the API response we got any data if yes with the help of for loop save it to the database.

After this we have a condition which will check if this was the first call to the API, if yes then store the information in total_calls and that will be total_entries/no. of pages (this data we got from the first API call).Then we increment now_calling variable and page variable. This page variable is used to know which page we will call in the next API call. Then I've made a sleep timer of 1 second so our script don't deplete their resources.


This second section will explain about the get_event_listings.php file.

First we import config file which contains information about the database connection. Then we get all the records of events that we store using the get_events.php script. And with the while loop we send event id of each event to the listings API and save it one by one using the for loop. Then there is a 1 second sleep timer.