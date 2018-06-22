<?php
header("Content-type: image/png");

$id = filter_input(INPUT_GET, 'mid', FILTER_SANITIZE_NUMBER_INT);

if (file_exists($id . ".png")) {
    readfile($id . ".png");
} else {
    readfile("0.png");
}