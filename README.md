openweathermap
==============

Openweathermap to Google-Weather response

Why?
==============
Because I have already a whole project which takes the input from the (unofficial) Google-API. I basically was too lazy to rewrite the Classes I have in different Projects, so I just wrote a quick Wrapper/Converter which utilized the Openweathermap api.

Usage
==============
Take the Classes and put them in your library folder

Create an instance of Openweathermap_Map.
Feed it with latitude and longitude
Call the Google-Response function, you get a proper xml response, as if the server would answer

Example
==============
$openweather = new Openweathermap_Map();
$openweather->setLatLon($lat, $lng);
$api = simplexml_load_string($openweather->getGoogleWeatherResponse());


Is there a Service-Server
==============
No.

License
==============
MIT, Free to use, free to fork, free for commercial projects, for private, you can also take it for a walk if you want :P

Support?
==============
Help yourself policy.