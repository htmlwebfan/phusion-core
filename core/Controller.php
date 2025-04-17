<?php

require_once __DIR__ . '/../lib/csrf.php';

class Controller {
    protected $sessionModel;
    protected $config;
    protected $layout = 'base';

    public function __construct() {
        $this->sessionModel = new SessionModel();
        $this->config = include __DIR__ . '/../config.php';
    }

    public function getConfig(){
        return $this->config;
    }

    public function getUser(){
        return $this->sessionModel->getUser();
    }

    public function isLoggedIn() {
        return $this->sessionModel->isLoggedIn();
    }

    public function render($view, $data = [], $layout = null) {
        $this->layout = $layout ?? $this->layout;

        $data['e'] = function ($value) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        };

        $data['include'] = function ($partial) use (&$data) {
            ob_start();
            extract($data);
            error_log("Including $partial with base_url: " . ($data['base_url'] ?? 'undefined'));
            include "views/partials/$partial.phtml";
            return ob_get_clean();
        };

        $data['sessionModel'] = $this->sessionModel;
        $data['config'] = $this->config;
        $data['csrf_token'] = CSRF::generateToken();
        $data['recaptcha_site_key'] = $this->config['recaptcha']['site_key'];
        $data['base_url'] = $this->getBaseUrl();

        ob_start();
        extract($data);
        include "views/$view.phtml";
        $content = ob_get_clean();

        ob_start();
        extract($data);
        include "views/layouts/{$this->layout}.phtml";
        echo ob_get_clean();
    }

    public function redirect($url) {
        $base = rtrim($this->getBaseUrl(), '/');
        $url = ltrim($url, '/');
        header("Location: $base/$url");
        exit;
    }

    public function validateRecaptcha($response) { // Changed from protected to public
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $this->config['recaptcha']['secret_key'],
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];
        $options = [
            'http' => [
                'method' => 'POST',
                'content' => http_build_query($data),
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $result = json_decode($result, true);
        return $result['success'] ?? false;
    }

    protected function getBaseUrl() {
        $urlConfig = $this->config['url'];
        $subdomain = $urlConfig['subdomain'] ? $urlConfig['subdomain'] . '.' : '';
        $tld = $urlConfig['tld'] ? '.' . $urlConfig['tld'] : '';
        $path = rtrim($urlConfig['path'], '/');
        return "{$urlConfig['protocol']}://{$subdomain}{$urlConfig['domain']}{$tld}{$path}";
    }
}