<?php

$data = json_decode(file_get_contents("dataset.json"), true);
$dataset = $data["dataset"];
$meta = $data["meta"];

$population = sizeof($dataset);

$minYear = $meta["min_year"];
$maxYear = $meta["max_year"];

$start = microtime(true);

const LOOKAHEAD_DISTANCE_PERCENTILE = 4;
$lookAheadDistance = ceil((LOOKAHEAD_DISTANCE_PERCENTILE / 100) * ($maxYear - $minYear));

$aliveYearlyData = array_fill($minYear, $maxYear - $minYear + 1, 0);
$iterations = 0;

$y = selectInitialYear($dataset, $minYear, $maxYear, $iterations, $lookAheadDistance); //Detailed explanation is in the README
$biggestYear = NULL;
$biggestPopulation = 0;

$previousPopulation = 0;

$checkedYearsCounter = $aliveYearlyData; //It's just an empty array with all years as the indeces, that's why we just copy it

$direction = -1;

/**
 * Lookahead distance calculation could still be further optimized and fine tuned
 */

$directionString = $direction > 0 ? "up" : "down";
echo "start on year {$y} going {$directionString}" . PHP_EOL . PHP_EOL;

while(true)
{
    if($y < $minYear || $y > $maxYear)
    {
        break;
    }

    $checkedYearsCounter[$y]++;

    //If we came back to the same year three times, it means the algorithm has changed direction, and went back to the highest point
    if($checkedYearsCounter[$y] > 2)
    {
        break;
    }

    calculateYear($y, $dataset, $aliveYearlyData, $iterations);

    $bigger = $aliveYearlyData[$y] > $biggestPopulation;
    if($bigger)
    {
        $biggestYear = $y;
        $biggestPopulation = $aliveYearlyData[$y];
    }

    if($previousPopulation > $aliveYearlyData[$y])
    {
        $biggerYear = NULL;
        for($lY = $y + (1 * $direction); $lY >= $minYear && $lY <= $maxYear && ($direction > 0 ? ($lY <= $y + ($lookAheadDistance * $direction)) : ($lY >= $y + ($lookAheadDistance * $direction))); $lY = $lY + (1 * $direction))
        {
            if($lY >= $minYear && $lY <= $maxYear)
            {
                calculateYear($lY, $dataset, $aliveYearlyData, $iterations);
                
                if($aliveYearlyData[$lY] > $aliveYearlyData[$y])
                {
                    $y = $lY;
                    continue 2;
                }
            }
        }

        $direction *= -1;
        $directionString = $direction > 0 ? "up" : "down";
        echo "switch on year {$y} going {$directionString}" . PHP_EOL . PHP_EOL;
    }

    $previousPopulation = $aliveYearlyData[$y];
    $y += $direction;
}

$answer = array_keys($aliveYearlyData, max($aliveYearlyData));

$time = (microtime(true) - $start) * 1000;

//Answer

echo PHP_EOL . "Answer:" . PHP_EOL;
print_r($answer);

echo PHP_EOL . "True answer:" . PHP_EOL;
print_r($meta["answer"]);

//Iterations

echo PHP_EOL . "Iterations:" . PHP_EOL;
echo $iterations;

echo PHP_EOL . "Base Iterations:" . PHP_EOL;
echo $meta["iterations"];

//Answer

echo PHP_EOL . "Time:" . PHP_EOL;
echo round($time, 3) . "ms";

echo PHP_EOL . "Base time:" . PHP_EOL;
echo round($meta["time"], 3) . "ms";

echo PHP_EOL;

function calculateYear(int $year, array $dataset, array &$outputArray, int &$i): void
{
    if($outputArray[$year] === 0)
    {
        foreach($dataset as $personData)
        {
            if($year >= $personData["birth_year"] && $year <= $personData["death_year"])
            {
                $outputArray[$year]++;
            }
    
            $i++;
        }
    }
}

function selectInitialYear(array $dataset, int $minYear, int $maxYear, int &$i, int $lookAheadDistance): int
{
    $overpassYear = 0;

    $yearlyData = array_fill($minYear, $maxYear - $minYear + 1, ["born" => 0, "died" => 0]);
    foreach($dataset as $personData)
    {
        $yearlyData[$personData["birth_year"]]["born"]++;
        $yearlyData[$personData["death_year"]]["died"]++;
        $i++;
    }

    foreach($yearlyData as $y => $yearData)
    {
        if($yearData["died"] > $yearData["born"])
        {
            for($n = $y + 1; $n <= $y + $lookAheadDistance; $n++)
            {
                $aheadData = $yearlyData[$n];
                if($aheadData["died"] < $aheadData["born"])
                {
                    continue 2;
                }

                $overpassYear = $y;
                break 2;
            }
        }
    }

    return $overpassYear;
}

/**
 * The chance for someone to be born or to die is constant, if we assume the randomness is perfect (everything is equally distributed)
 * 
 * x  - year
 * y  - amount born/dead in the given year
 * n0 - total generated population
 * n1 - minimum year
 * n2 - maximum year
 * n  - current population
 * 
 * Births:
 * The yearly births follow a constant function y = n0 / (n2 - n1), which is the probability of birth for each year,
 * with an average error of +-2.2%
 * Because the birth rate is not dependent on the amount of people alive (in our scenario),
 * the number of people born each year remains static through all the years in the selected range
 * 
 * Deaths:
 * Yearly deaths ARE dependent on the amount of people alive
 */