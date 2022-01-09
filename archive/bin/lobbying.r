#!/usr/bin/env Rscript

library(RMySQL)
con <- dbConnect(MySQL(), user = 'root', password = '', host = 'localhost', dbname='ottwatch')

rs = dbSendQuery(con, " select left(lobbydate,7) month,count(1) activities from lobbying where lobbydate < '2015-01-01' and lobbydate >= '2012-09' group by left(lobbydate,7) ")
data = fetch(rs, n=-1)
r = data[,0]
x = data[,1]
y = data[,2]

#print(data);
#print(x);
#print(y);
barplot(y,main="Lobbying activities per month (2012-09 to 2014-12)", names.arg=x)

#as.numeric(x);
#as.numeric(y);

#plot(data);


# plot(colnames(data),x,y)



# Define the cars vector with 5 values
#cars <- c(1, 3, 6, 4, 9, 11, 11, 4)

# Graph the cars vector with all defaults
#plot(cars)


