import urllib.request, ssl, re, os
import tweepy

def scraper(url):
        #for Mac w/certificate workaround
        context = ssl._create_unverified_context()
        web_page = urllib.request.urlopen (url, context=context)
        contents = web_page.read().decode(errors="replace")
        web_page.close()

        #scraping donor names
        donors = re.findall('(?<=<h6 class="mb-0">).+?(?=</h6>)',contents)

        #scraping donation timestamp
        donation_date = re.findall('(?<=datetime=").+?(?=T)',contents)

        #setting empty variable
        tweet_string = ""
        tweets = twint()

        #checking for non-anonymous donors and correct dates
        for i in range(len(donation_date)):
                if "-04-" in donation_date[i]:
                        if donors[i] != "Anonymous" and donors[i] not in tweets:
                                tweet_string += "Thank you for your donation, " + donors[i] + "!"
                                tweetbot(tweet_string) #function call
                                tweet_string = ""

def tweetbot(tweet):
        # personal details 
        consumer_key ="vRJ5QXzfT3leDbgfys4edrjhM"
        consumer_secret ="JjlYnlnctEVE4rpAuvkY0zyb0vUJehCZeuHZmuvx4XKhC0vdTa"
        access_token ="109746402-TkmZ71gRtJAAvSo2hAJ1jLonyCVkV1YKiPet1TAS"
        access_token_secret ="HPgxca9QWezCWAaiuCVrEGYKcm2jgakDonQSUauktMnEX"

        # authentication of consumer key and secret 
        auth = tweepy.OAuthHandler(consumer_key, consumer_secret) 

        # authentication of access token and secret 
        auth.set_access_token(access_token, access_token_secret) 
        api = tweepy.API(auth) 

        # update the status 
        api.update_status(status = tweet)
        print("Tweet Sent:", tweet)

def twint():
        #creating tweets.txt file
        command = "touch tweets.txt"
        os.system(command)

        #twint command to scrape twitter account
        command = "twint -u helmsysays --since '2020-05-04 00:00:00' -o tweets.txt"
        os.system(command)

        #opening .txt file to read and compare contents to avoid duplicating tweets
        file = open("tweets.txt", "r")
        contents = file.read()
        file.close()
        return contents

##main
url = "https://thso.networkforgood.com/projects/82009-symphony-fund"
scraper(url)
