<?
echo '200';

include "function.php";

$lod_file = "redirect.txt";
write($lod_file, "w", 'Начали');
write_mass($lod_file, "a", $_REQUEST);

?>