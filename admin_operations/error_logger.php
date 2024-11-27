<?php
class ErrorLogger {
    private $logFile;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->logFile = __DIR__ . '/system_errors.log';
        
        // Create or ensure proper permissions for log file
        if (!file_exists($this->logFile)) {
            touch($this->logFile);
        }
        
        // Set permissions to allow writing (666 in octal)
        chmod($this->logFile, 0666);
        
        // Verify the file is writable
        if (!is_writable($this->logFile)) {
            throw new Exception("Error log file is not writable: " . $this->logFile);
        }
    }

    public function logError($error, $context = [], $severity = 'ERROR') {
        $timestamp = date('Y-m-d H:i:s');
        $errorMessage = $error instanceof Exception ? $error->getMessage() : $error;
        $trace = $error instanceof Exception ? $error->getTraceAsString() : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        
        // Format message with timestamp
        $logMessage = sprintf(
            "[%s] %s: %s\nContext: %s\nTrace: %s\n%s\n",
            $timestamp,
            $severity,
            $errorMessage,
            json_encode($context, JSON_PRETTY_PRINT),
            is_array($trace) ? json_encode($trace, JSON_PRETTY_PRINT) : $trace,
            str_repeat('-', 80)
        );

        // Force immediate write to file
        file_put_contents(
            $this->logFile,
            $logMessage,
            FILE_APPEND | LOCK_EX
        );

        // Also log to database
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO system_errors 
                (error_message, error_context, stack_trace, severity, user_id, ip_address)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $errorMessage,
                json_encode($context),
                is_array($trace) ? json_encode($trace) : $trace,
                $severity,
                $_SESSION['user_id'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        } catch (Exception $e) {
            // If database logging fails, at least write to file
            file_put_contents(
                $this->logFile,
                "[{$timestamp}] DATABASE_ERROR: Failed to log to database: {$e->getMessage()}\n",
                FILE_APPEND | LOCK_EX
            );
        }

        return true;
    }

    public function logDebug($message, $context = []) {
        return $this->logError($message, $context, 'DEBUG');
    }

    public function logInfo($message, $context = []) {
        return $this->logError($message, $context, 'INFO');
    }

    public function logWarning($message, $context = []) {
        return $this->logError($message, $context, 'WARNING');
    }
} 