import matplotlib.pyplot as plt
import numpy as np
import requests
import urllib.request
from urllib.request import Request, urlopen
from bs4 import BeautifulSoup
import scipy

def download_data (page_number):
    
    ##Download the data
    ##url = 'https://www.foxsports.com/mlb/stats?season=2019&category=BATTING&group=1&sort=7&time=0&pos=0&qual=1&sortOrder=0&splitType=0&page=1&statID=0'
    url = 'https://www.foxsports.com/mlb/stats?season=2019&category=BATTING&group=1&sort=7&time=0&pos=0&qual=1&sortOrder=0&splitType=0&page='+str(page_number)+'&statID=0'
    print ('#Download file :', url)
    req = Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    html = urlopen(req).read()
    
    #Save to the Disk 
    fileName = 'Data/' + str(page_number) +  '.html'
    f = open(fileName,'w')
    f.write(html.decode('utf-8'))
    f.close()

        
def parse_data(fileName):
   ##Parse the data
    soup = BeautifulSoup(open(fileName, 'r'), 'lxml')
    
    
    ##This is for batting 
    rows = soup.find_all('tr') #attrs={'class' : 'wisbb_text wisbb_fixedColumn wisbb_tableTitle sorter-ordinal-text'})
    
    homeruns = []
    average = []
    atBat = []
    slugs = []
        
    for i in range(1, len(rows)):
        
        row = rows[i]
        columns = row.find_all('td')
        
        print (columns[0].get_text(), columns[8].get_text(), columns[14].get_text(), columns[3].get_text())
        
        
        
        homeruns.append(columns[8].get_text())
        average.append(columns[14].get_text())
        atBat.append(columns[3].get_text())
        slugs.append(columns[16].get_text())
        
    print(homeruns)
    print(average)
    print(atBat)
    print(slugs)
        
    return homeruns,average,atBat,slugs

#This simple for loop saves all 26 pages on the drive, in a folder called data
for i in range(1,27,1):
    download_data (i)

#This is a loop that gets all the data that I want for the data
homeruns = []
averages = []
atBat = []
slugs = []

for i in range(1,27,1):
    fileName = 'Data/' + str(i) +  '.html'

    tuples= parse_data(fileName)
    for j in tuples[0]:
        if int(j) == 1: 
            j = 0
        homeruns.append(int(j))
    for k in tuples[1]:
        if k == "-":
            k = 0
        averages.append(float(k))    
    for a in tuples[2]:
        atBat.append(int(a))
    for s in tuples[3]:
        if s == "-":
            s = 0
        slugs.append(float(s))

#data visualization
#scatter plot: average VS homeruns
##players that have batted at least 25 times
x = averages ##Batting average on x-axis
y = homeruns ##Homeruns on y-axis
z = atBat ##list of player at bats to get data from players who have played in multiple games not just outlier pinch hitters

# iterate over the lists to choose items that fit my parameters
counter = 0

for i in atBat:
    if i >= 25:
        plt.scatter(x[counter], y[counter])
    counter += 1

plt.title('Homeruns vs. Batting Average')
plt.xlabel('Batting Average')
plt.ylabel('Homeruns')
plt.show()

##Players who have batted at least 200 times and therefore have more accurate data/ most are starters or started in many games then got injured
x = averages ##Batting average on x-axis
y = homeruns ##Homeruns on y-axis
z = atBat ##list of player at bats to get data from players who have played in multiple games not just outlier pinch hitters

# iterate over the lists to choose items that fit my parameters
counter = 0

for i in atBat:
    if i >= 200:
        plt.scatter(x[counter], y[counter])
    counter += 1

plt.title('Homeruns vs. Batting Average')
plt.xlabel('Batting Average')
plt.ylabel('Homeruns')
plt.show()

#scatter plot: slugging percentage VS homeruns
##Players who have batted at least 25 times and therefore have more accurate data/ most are starters or started in many games then got injured
##calculates slg comapred to homeruns
x = slugs ##Slugging Percentage (SLG)
y = homeruns ##Homeruns on y-axis
z = atBat ##list of player at bats to get data from players who have played in multiple games not just outlier pinch hitters

# iterate over the lists to choose items that fit my parameters
counter = 0

for i in atBat:
    #plt.scatter(x,y)
    if i >= 25:
        plt.scatter(x[counter], y[counter])
    counter += 1

#print(len(x))
#print(len(y))

plt.title('Homeruns vs. Slugging Percentage (SLG)')
plt.xlabel('Slugging Percentage')
plt.ylabel('Homeruns')
plt.show()


#line of regression graphs
#line of best fit graphs
#batting average VS homeruns
def average_value (x):
    av = 0.0
    num = 0.0
    for i in range(0, len(x)):
        av += float(x[i])
        num += 1.0
    if num > 0.0:
        return av/num
    return None


def std_deviation (x):
    av_x = average_value (x)
    if av_x != None:
        std = 0.0
        num = 0.0
        for i in range(0, len(x)):
            std += (float(x[i]) - av_x)*(float(x[i]) - av_x)
            num += 1.0
        return np.sqrt(std/num)
    return None


def second_moment (x):
    av = 0.0
    num = 0.0
    for i in range(0, len(x)):
        av += float(x[i])*float(x[i])
        num += 1.0
    if num > 0.0:
        return av/num
    return None

def linear_regression (x, y):
    
    if (len(x) != len(y)):
        return None
    
    a_x =  average_value (x)
    a_y =  average_value (y)
    s_x =  std_deviation (x)
    s_y =  std_deviation (y)
    m_x =  second_moment (x)
    m_y =  second_moment (y)
    
    
    p = []
    for i in range(0, len(x)):
        p.append(float(x[i])*float(y[i]))
    a_p = average_value (p)
    
    ##linear correlation coefficient
    r = (a_p - a_x*a_y) / np.sqrt ( (m_x-a_x*a_x) * (m_y-a_y*a_y) )
    
    
    ##best fit
    beta = r * s_y / s_x
    alpha = a_y - beta * a_x
    
    
    return alpha, beta, r


##players that have batted at least 25 times
x2 = averages ##Batting average on x-axis
y2 = homeruns ##Homeruns on y-axis
z = atBat ##list of player at bats to get data from players who have played in multiple games not just outlier pinch hitters

# iterate over the lists to choose items that fit my parameters
counter = 0

for i in atBat:
    if i >= 25:
        plt.scatter(x2[counter], y2[counter])
        x.append(x2[counter])
        y.append(y2[counter])
    counter += 1
    
#alpha, beta, 
alpha, beta, r = linear_regression (x, y)
#print ('alpha : ', alpha)
#print ('beta : ', beta)
print ('correlation coefficient : ', r)

best_fit_average = np.arange(min(x), max(x), (max(x)- min(x)) / 10000.0)
best_fit_y = alpha + beta * best_fit_average
plt.plot(best_fit_average, best_fit_y, color ='b', markersize=0, linewidth=3, linestyle='-', alpha = 0.5)


plt.xlim(right=.4)
#xlim(left=0)

plt.title('Homeruns vs. Batting Average')
plt.xlabel('Batting Average')
plt.ylabel('Homeruns')
plt.show()


#slugging percentage VS homeruns
def average_value (x):
    av = 0.0
    num = 0.0
    for i in range(0, len(x)):
        av += float(x[i])
        num += 1.0
    if num > 0.0:
        return av/num
    return None


def std_deviation (x):
    av_x = average_value (x)
    if av_x != None:
        std = 0.0
        num = 0.0
        for i in range(0, len(x)):
            std += (float(x[i]) - av_x)*(float(x[i]) - av_x)
            num += 1.0
        return np.sqrt(std/num)
    return None


def second_moment (x):
    av = 0.0
    num = 0.0
    for i in range(0, len(x)):
        av += float(x[i])*float(x[i])
        num += 1.0
    if num > 0.0:
        return av/num
    return None

def linear_regression (x, y):
    
    if (len(x) != len(y)):
        return None
    
    a_x =  average_value (x)
    a_y =  average_value (y)
    s_x =  std_deviation (x)
    s_y =  std_deviation (y)
    m_x =  second_moment (x)
    m_y =  second_moment (y)
    
    
    p = []
    for i in range(0, len(x)):
        p.append(float(x[i])*float(y[i]))
    a_p = average_value (p)
    
    ##linear correlation coefficient
    r = (a_p - a_x*a_y) / np.sqrt ( (m_x-a_x*a_x) * (m_y-a_y*a_y) )
    
    
    ##best fit
    beta = r * s_y / s_x
    alpha = a_y - beta * a_x
    
    
    return alpha, beta, r


##players that have batted at least 25 times
x2 = slugs ##Slugging Percentage on x-axis
y2 = homeruns ##Homeruns on y-axis
z = atBat ##list of player at bats to get data from players who have played in multiple games not just outlier pinch hitters

# iterate over the lists to choose items that fit my parameters
counter = 0

for i in atBat:
    if i >= 25:
        plt.scatter(x2[counter], y2[counter])
        x.append(x2[counter])
        y.append(y2[counter])
    counter += 1
    
#alpha, beta, 
alpha, beta, r = linear_regression (x, y)
#print ('alpha : ', alpha)
#print ('beta : ', beta)
print ('correlation coefficient : ', r)

best_fit_average = np.arange(min(x), max(x), (max(x)- min(x)) / 10000.0)
best_fit_y = alpha + beta * best_fit_average
plt.plot(best_fit_average, best_fit_y, color ='b', markersize=0, linewidth=3, linestyle='-', alpha = 0.5)


plt.xlim(right=.7)
#xlim(left=0)

plt.title('Homeruns vs. Slugging Percentage')
plt.xlabel('Slugging Percentage')
plt.ylabel('Homeruns')
plt.show()

#Histograms
##players that have batted at least 25 times
##Histogram of batting average averages
x = averages ##Batting average on x-axis
y = homeruns ##Homeruns on y-axis
z = atBat ##list of player at bats to get data from players who have played in multiple games not just outlier pinch hitters

x1= []
x2= []
# iterate over the lists to choose items that fit my parameters
counter = 0

for i in atBat:
    if i >= 25:
        x1.append(x[counter])
    counter += 1
    
plt.hist(x1, 10, facecolor='blue', alpha=0.5)

plt.title('Batting Average Histogram')
plt.xlabel('Batting Average')
plt.ylabel('Number of Players')
plt.show()

##Histogram of at bats each player in the mlb has had
x = averages ##Batting average on x-axis
y = homeruns ##Homeruns on y-axis
z = atBat ##list of player at bats to get data from players who have played in multiple games not just outlier pinch hitters

    
plt.hist(z, 10, facecolor='blue', alpha=0.5)

plt.title('At Bat Histogram')
plt.xlabel('Number Of At Bats')
plt.ylabel('Number of Players')
plt.show()

