<?php
require_once 'BadWordFilter.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['text'] ?? '';
    $containsBadWords = BadWordFilter::containsBadWords($text);
    
    echo json_encode([
        'containsBadWords' => $containsBadWords
    ]);
} 