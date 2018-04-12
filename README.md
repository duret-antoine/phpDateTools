# phpDateTools
A library of functions oriented on date and time calculation specifically on (not)working days

## getWorkedTimeBetween()
Enables to calculate the worked time between 2 timestanps considering :
- **Fixed date** holidays
- **Variable date** holidays
- **Working day hours** : this is a parameter in witch you can customize as many working periode in a day as you want
- **Daylignt Saving Time (DST)** (depending on the working day periode)

### Note
* The holidays dates are fixed for France right now and no parameter enables to customize it for now
* Working hours definition is fixed for everyweek and limited to minutes
* The result is written in folliwing format : **h:m:s**

