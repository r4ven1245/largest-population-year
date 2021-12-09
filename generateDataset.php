<?php

$firstnames = json_decode(file_get_contents("./data/firstnames.json"), true);
$lastnames = json_decode(file_get_contents("./data/lastnames.json"), true);

$firstnamesLength = sizeof($firstnames);
$lastnamesLength = sizeof($lastnames);

//Config
const POPULATION_TO_GENERATE = 50000;
const MIN_YEAR = 1100;
const MAX_YEAR = 2000;

//Generate the dataset
$dataset = [];
for($i = 0; $i < POPULATION_TO_GENERATE; $i++)
{
    $randomBirthYear = random_int(MIN_YEAR, MAX_YEAR - 1);
    $randomAge = random_int(1, MAX_YEAR - $randomBirthYear);
    $randomDeathYear = $randomBirthYear + $randomAge;

    $randomFirstname = $firstnames[random_int(0, $firstnamesLength - 1)];
    $randomLastname = $lastnames[random_int(0, $lastnamesLength - 1)];
    $randomFullname = "{$randomFirstname} {$randomLastname}";

    $dataset[] = [
        "id" => $i,
        "name" => $randomFullname,
        "birth_year" => $randomBirthYear,
        "death_year" => $randomDeathYear,
    ];
}

$start = microtime(true);

//Calculate true answer to the question
$aliveYearlyData = array_fill(MIN_YEAR, MAX_YEAR - MIN_YEAR + 1, 0);
foreach($dataset as $personData)
{
    for($y = MIN_YEAR; $y <= MAX_YEAR; $y++)
    {
        if($y >= $personData["birth_year"] && $y <= $personData["death_year"])
        {
            $aliveYearlyData[$y]++;
        }
    }
}

$time = (microtime(true) - $start) * 1000;

$largestAliveYearKeys = array_keys($aliveYearlyData, max($aliveYearlyData)); //As the number of people alive can be the same, we find all correct answers

$outputData = [
    "meta" => [
        "answer" => $largestAliveYearKeys,
        "iterations" => POPULATION_TO_GENERATE * (MAX_YEAR - MIN_YEAR + 1), //In the current configuration, Big-O notation would be - O(101n)
        "min_year" => MIN_YEAR,
        "max_year" => MAX_YEAR,
        "time" => $time,
    ],
    "dataset" => $dataset,
];

file_put_contents("dataset.json", json_encode($outputData));