<?php

class ErrorController extends Controller {
    public function error($code, $message) {
        http_response_code($code);
        $debugInfo = [];
        
        if ($this->config['env'] === 'development') {
            $debugInfo = [
                'file' => debug_backtrace()[1]['file'] ?? 'Unknown',
                'line' => debug_backtrace()[1]['line'] ?? 'Unknown',
                'trace' => array_slice(debug_backtrace(), 1, 5)
            ];
        }

        $this->render('error', [
            'code' => $code,
            'message' => $message,
            'debugInfo' => $debugInfo
        ]);
    }
}