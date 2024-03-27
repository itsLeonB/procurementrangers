<?php

$first = 1;
$second = 1;
$fib = [];
while ($first <= 30) {
    $fib[] = $first;
    $next = $first + $second;
    $first = $second;
    $second = $next;
}

echo "Angka Fibonacci kurang dari 30: " . implode(', ', $fib) . "<br>";

$isFib = [];

for ($i = 1; $i <= 30; $i++) {
    if (in_array($i, $fib)) {
        $isFib[] = $i;
        echo "$i - Angka Fibonacci<br>";
    } else {
        echo "$i - Bukan Angka Fibonacci<br>";
    }
}

echo "Jumlah Angka Fibonacci: " . array_sum($isFib);
