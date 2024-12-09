<?php
require_once __DIR__ . '/../configs/config.php';

try {
    // Fetch quotes specifically related to mental health, wellness, and mindfulness
    $apiUrls = array(
        'https://api.quotable.io/quotes/random?limit=20&tags=mental-health',
        'https://api.quotable.io/quotes/random?limit=20&tags=mindfulness',
        'https://api.quotable.io/quotes/random?limit=20&tags=happiness',
        'https://api.quotable.io/quotes/random?limit=20&tags=wellness',
        'https://api.quotable.io/quotes/random?limit=20&tags=healing'
    );

    // Function to fetch quotes using cURL
    function fetchQuotes($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'
        ]);
        
        $response = curl_exec($ch);
        
        if(curl_errno($ch)) {
            error_log('Curl error: ' . curl_error($ch));
            return false;
        }
        
        curl_close($ch);
        return json_decode($response, true);
    }

    $allQuotes = array();
    foreach ($apiUrls as $apiUrl) {
        $quotes = fetchQuotes($apiUrl);
        if ($quotes) {
            $allQuotes = array_merge($allQuotes, $quotes);
            echo "Successfully fetched quotes from: $apiUrl\n";
        } else {
            echo "Failed to fetch quotes from: $apiUrl\n";
        }
    }

    if (!empty($allQuotes)) {
        // Prepare the insert statement
        $query = "INSERT IGNORE INTO quotes (content, author, category) VALUES (:content, :author, :category)";
        $stmt = $pdo->prepare($query);

        foreach ($allQuotes as $quote) {
            $stmt->execute(array(
                'content' => $quote['content'],
                'author' => $quote['author'],
                'category' => implode(',', $quote['tags'])
            ));
        }
    }

    // Always add these curated mental health quotes as backup
    $mentalHealthQuotes = array(
        array(
            'content' => 'Mental health is not a destination, but a process. It\'s about how you drive, not where you\'re going.',
            'author' => 'Noam Shpancer',
            'category' => 'mental health'
        ),
        array(
            'content' => 'You don\'t have to be positive all the time. It\'s perfectly okay to feel sad, angry, annoyed, frustrated, scared and anxious.',
            'author' => 'Lori Deschene',
            'category' => 'mental health,emotions'
        ),
        array(
            'content' => 'Take your time healing, as long as you want. Nobody else knows what you\'ve been through.',
            'author' => 'Abertoli',
            'category' => 'healing,mental health'
        ),
        array(
            'content' => 'Self-care is not selfish. You cannot serve from an empty vessel.',
            'author' => 'Eleanor Brown',
            'category' => 'self-care,mental health'
        ),
        array(
            'content' => 'Your mental health is a priority. Your happiness is essential. Your self-care is a necessity.',
            'author' => 'Unknown',
            'category' => 'mental health,self-care'
        )
    );

    $query = "INSERT IGNORE INTO quotes (content, author, category) VALUES (:content, :author, :category)";
    $stmt = $pdo->prepare($query);

    foreach ($mentalHealthQuotes as $quote) {
        $stmt->execute($quote);
    }

    echo "Quotes added successfully!\n";

} catch (Exception $e) {
    error_log("Error seeding quotes: " . $e->getMessage());
    echo "Error adding quotes. Check error log for details.\n";
}
?>