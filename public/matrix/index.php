<?php

$matrix = [
    [0, 0, 0],
    [0, 0, 0],
    [0, 0, 0]
];

$row = 0;
$col = intval(3 / 2);
$num = 1;

while ($num <= 3 * 3) {
    $matrix[$row][$col] = $num;

    $nextRow = ($row - 1 < 0) ? 3 - 1 : $row - 1;
    $nextCol = ($col + 1) % 3;

    if ($matrix[$nextRow][$nextCol] != 0) {
        $row = ($row + 1) % 3;
    } else {
        $row = $nextRow;
        $col = $nextCol;
    }

    $num++;
}

echo "<table border='1'>";
for ($i = 0; $i < 3; $i++) {
    echo "<tr>";
    for ($j = 0; $j < 3; $j++) {
        echo "<td>" . sprintf("%3d", $matrix[$i][$j]) . "</td>";
    }
    echo "</tr>";
}
echo "</table><br>";

$n = count($matrix);
$stats = [];

for ($col = 0; $col < $n; $col++) {
    $columnValues = array_column($matrix, $col);
    $stats[] = [
        'index' => $col + 1,
        'max' => max($columnValues),
        'min' => min($columnValues)
    ];
}

echo "<table border='1'>";
echo "<tr><th>Kolom Index</th><th>Nilai Maximum</th><th>Nilai Minimum</th></tr>";
foreach ($stats as $stat) {
    echo "<tr><td>" . $stat['index'] . "</td><td>" . $stat['max'] . "</td><td>" . $stat['min'] . "</td></tr>";
}
echo "</table>";
