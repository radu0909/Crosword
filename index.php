<?php

use function Sodium\randombytes_uniform;

function show_binary($value, $length = 8, $show_zeros = true, $new_line = false)
{
    $format = '%';
    if ($show_zeros) $format .= '0';
    $format .= $length;
    $format .= 'b';
    $format .= PHP_EOL;
    $str = sprintf($format, $value);
    $str = strrev($str);
    $str =  str_replace('0', '.', $str);
    $str = str_replace('1', '+', $str);
    echo $str;
}

function show_array($arr)
{
    echo json_encode($arr).PHP_EOL;
}

function set_first_n_bits($bits, $start, $count)
{
    return $bits | (((1 << ($start + $count)) - 1) - ((1 << $start) - 1));
}

function shift_left_at($bits, $index)
{
    return ($bits >> $index << ($index + 1)) | ($bits & ((1 << $index) - 1));
}

function contains($bits, $item)
{
    return (($bits & (1 << $item)) != 0);
}

function generate($runs, $length)
{
    $list = [];
    $initial = 0;
    $sums = [];
    $sums[0] = 0;
    for ($i = 1; $i < count($runs); $i++) $sums[$i] = $sums[$i - 1] + $runs[$i - 1] + 1;
    for ($r = 0; $r < count($runs); $r++) $initial = set_first_n_bits($initial, $sums[$r], $runs[$r]);
    generate_rec($list, set_first_n_bits(0, 0, $length) + 1, $runs, $sums, $initial, 0, 0);
    return $list;
}

function generate_rec(&$result, $max, $runs, $sums, $current, $index, $shift)
{
    if ($index == count($runs) || $current == 0)
    {
        $result[] = $current;
        return;
    }
    while ($current < $max)
    {
        generate_rec($result, $max, $runs, $sums, $current, $index + 1, $shift);
        $current = shift_left_at($current, $sums[$index] + $shift);
        $shift++;
    }

}

function check(&$arr1, &$arr2, &$count)
{
    foreach ($arr1 as $arr1_k => $arr1_v)
    {
        $or_func = function ($a, $b) { return $a & $b; };
        $and_func = function ($a, $b) {return $a | $b; };

        $allOn = array_reduce($arr1_v, $or_func, PHP_INT_MAX);
        $allOff = array_reduce($arr1_v, $and_func, 0);

        foreach ($arr2 as $arr2_k => $arr2_v)
            foreach ($arr2_v as $arr2_kk => $arr2_vv)
                if (contains($allOn, $arr2_k) && !contains($arr2_vv, $arr1_k) ||
                    (!contains($allOff, $arr2_k) && contains($arr2_vv, $arr1_k)))
                {
                    $count++;
                    unset($arr2[$arr2_k][$arr2_kk]);
                }
    }
}

function reduce(&$rows, &$columns)
{
    do{
        $count = 0;
        check($rows, $columns, $count);
        check($columns, $rows, $count);
    } while($count > 0);
}


//START |
//      |
//     \|/


$rows = [
    [3], [2, 1], [3, 2], [2, 2], [6], [1, 5], [6], [1], [2]
];

$columns = [
    [1, 2], [3, 1], [1, 5], [7, 1], [5], [3], [4], [3]
];
//Se genereaza toate combinatiile pozibile
$rows_combinations = [];
$columns_combinations = [];
for ($j = 0; $j < count($rows); $j++)
    $rows_combinations[] = generate($rows[$j], count($columns));

for ($j = 0; $j < count($columns); $j++)
    $columns_combinations[] = generate($columns[$j], count($rows));

reduce($rows_combinations, $columns_combinations); // functia care la fiecare iteratie reduce numarul de combinatii pana ajunge la rezultat

$rows_combinations = array_merge(...$rows_combinations); //obtin un array de array-uri..array merge-transforma intr-un singur array

foreach ($rows_combinations as $key => $value)
    show_binary($value, count($columns_combinations)); // afiseaza matricea(rezultatul)