Welcome!

Considerations: 

The project was created as a composer package with a sample index.php using the package. 

To run it: 

composer install

then:

php -S localhost:8000

This will serve the file to your local network.

The "endpoint" accepts the following parameters: latitude, longitude and orderby.
Example GET: http://localhost:8000/?latitude=8.628205935832389&longitude=-79.03427124023438&orderby=proximity

When no latitude or longitude is provided, it defaults to: [38.718515, -9.144147].
When no orderby is provided, it defaults to "proximity"

---------------------------------

I've developed a GUI using React, it's included in the folder "react". 
You can build it using:

npm install 
npm run build 

and run using npm run preview

A live version is deployed here:
http://hotel-search-react.s3-website-us-east-1.amazonaws.com/

-----------------------------------

The composer package is in the "hotel-search" folder. You can check the source code within /src.
There's also a live version deployed in here:
http://hotel-search-env-1.eba-ckhwursa.us-east-1.elasticbeanstalk.com/

Thanks for your time! 