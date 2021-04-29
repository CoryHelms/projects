The purpose of this file was to create a twitter bot that would tweet thank you messages to donors when donating during a live event hosted by the Terre Haute Symphony Orchestra in Terre Haute, Indiana.

The scraper function finds the names of all the people who have donated from the donation page's HTML. It then formulates the string for the tweet and passes it to the tweetbot function.

The tweetbot function serves as the connection between the python code and Twitter's API. From here the tweet that has been formulated in the scraper function is tweeted out from the account information set in the tweetbot function.

The twint function serves as a way to prevent duplicate tweets. Using the twint extension we are able to scrape our account and check for matches in names. This prevents the scraper function from creating another tweet for a donor who has already received a thank you.
