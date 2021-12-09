# Year with the highest population
## Credit

Firstname and lastname datasets used are from [dominictarr/random-name]

## Usage

```sh
php populationYear.php
```

## Dataset Generation

To generate a new full dataset, configure `generateDataset.php`:

```php
const POPULATION_TO_GENERATE = 50000;
const MIN_YEAR = 1900;
const MAX_YEAR = 2000;
```

After configuration, generate the dataset:

```sh
php generateDataset.php
```

## Year selection algorithm

![Alt text](/img/graph_1.jpg?raw=true "Graph")

The chance for someone to be born or to die is constant, if we assume the randomness is perfect (everything is equally distributed)

- x - year
- y - amount born/dead in the given year
- n0 - total generated population
- n1 - minimum year
- n2 - maximum year
- n - current population

### Births

The yearly births follow a constant function y = n0 / (n2 - n1), which is the probability of birth for each year,
with an average error of +-2.2%
Because the birth rate is not dependent on the amount of people alive (in our scenario),
the number of people born each year remains static through all the years in the selected range

### Deaths

While the chance is constant, the amount of people alive dictates the amount of yearly deaths
Which in turn, allows us to find the year, when the number of deaths overtakes the amount of births


[dominictarr/random-name]: <https://github.com/dominictarr/random-name>