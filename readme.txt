=== Time Based Content ===
Contributors: eliorivero
Tags: publish, post, page
Stable tag: 1.0.1
Requires at least: 5.3
Tested up to: 6.6.2
License: GPLv2 or later

Displays a different front page according to the time of the day.

== Description ==

Displays a different home page based on the pages the user specified to show at different times of the day.

If there is a time period where no page has been assigned, it will use the page you select in Settings > Reading in the Your homepage displays option.

There are four ranges to specify a page:
- morning
- noon
- afternoon
- night
However, each hour selection covers the entire day so you don't have to select all four: you can select three ranges, or two, or one. 

== Installation ==

1. Install this plugin in the WP Admin by going to Plugins > Add New and searching for "time based content".

2. Once it's installed, activate it.

3. Ensure you have four pages for this plugin, one for each moment of the day:

    - one for the morning
    - another for noon
    - one more for the afternoon
    - and a last one for the night
  
    You'll probably want to have a default page to display if the time ranges you specify pages don't cover the entire day. If you don't need to specify four pages you can specify three or two or one. Just start from the top because those takes precedence when deciding which page to show.

4. Go to the WordPress Reading settings page at WP Admin > Settings > Reading

5. Set Your homepage displays to A static page and select any page you'd like to display whenever you're not displaying one of the specific pages for the times of the day. For example, if you specify that the night page will be displayed until 12:00 am, and the morning page will start displaying at 07:00 am, then this page will display betwen that time when no other page is assigned.

6. Go to the plugin settings page at WP Admin > Settings > Time Based Content

7. Select the time ranges and the page to display at each time.

== Frequently Asked Questions ==

= How do I contribute to this plugin? =

Contributions are welcome at [the plugin repo on GitHub](https://github.com/eliorivero/just-in-time-content).

== Screenshots ==

1. Settings to specify pages for different times of the day

== Changelog ==

= 1.0.1 =

* Update paramaters passed to get the hours in WP Admin so it doesn't throw an error.

= 1.0.0 =

* Release date: Jan 24th, 2021
