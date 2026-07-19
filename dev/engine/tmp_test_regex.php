<?php
$body = "\n  width: \$var;\n  height: \$var;\n";
$name = 'var';
$value = '30px';
$result = preg_replace('/\B\$' . preg_quote($name) . '\b/', $value, $body);
echo "Body: [" . trim($body) . "]\n";
echo "Result: [" . trim($result) . "]\n";
echo "Regex: /\B\$" . preg_quote($name) . "\b/\n";
