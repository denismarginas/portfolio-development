<?php
$str = '( $var) ';
echo "Input: [" . $str . "]\n";
echo "Hex: " . bin2hex($str) . "\n";
echo "trim(..., '()'): [" . trim($str, '()') . "]\n";
echo "trim then trim('()'): [" . trim(trim($str), '()') . "]\n";
echo "trim('()') then trim: [" . trim(trim($str, '()')) . "]\n";
