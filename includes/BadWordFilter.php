<?php
class BadWordFilter {
    private static $badWords = [
        // English General Profanity
        'fuck', 'shit', 'ass', 'bitch', 'damn', 'crap', 'piss', 'motherfucker',

        // English Sexual/Explicit Content
        'pussy', 'cunt', 'dildo', 'faggot', 'slut', 'whore',

        // English Insults
        'idiot', 'moron', 'dumb', 'stupid', 'retard',

        // Tagalog General Swear Words
        'putangina', 'tarantado', 'gago', 'tanga', 'bobo', 'siraulo', 'lintik',

        // Tagalog Sexual/Explicit Content
        'puke', 'betlog', 'titi', 'kepyas', 'kantot', 'biyak', 'laplap',

        // Tagalog Insults
        'ulol', 'engot', 'tangan-tanga', 'gunggong',

        // Tagalog Other Derogatory Terms
        'leche', 'pakshet', 'bwisit'
    ];

    // Function to check if text contains bad words
    public static function containsBadWords($text) {
        $text = strtolower($text); // Convert to lowercase for case-insensitive checking
        
        foreach (self::$badWords as $word) {
            // Check for exact word matches using word boundaries
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $text)) {
                return true;
            }
            
            // Check for common character substitutions (e.g., @ for a, 1 for i)
            $pattern = str_replace(
                ['a', 'i', 'e', 'o', 's'],
                ['(a|@|4)', '(i|1|!)', '(e|3)', '(o|0)', '(s|5|\$)'],
                $word
            );
            if (preg_match('/\b' . $pattern . '\b/i', $text)) {
                return true;
            }
        }
        return false;
    }

    // Function to get the detected bad word (useful for logging)
    public static function getDetectedBadWord($text) {
        $text = strtolower($text);
        
        foreach (self::$badWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $text)) {
                return $word;
            }
        }
        return null;
    }

    // Function to filter/censor bad words (replaces with asterisks)
    public static function censorText($text) {
        foreach (self::$badWords as $word) {
            $replacement = str_repeat('*', strlen($word));
            $text = preg_replace('/\b' . preg_quote($word, '/') . '\b/i', $replacement, $text);
        }
        return $text;
    }
}
?> 