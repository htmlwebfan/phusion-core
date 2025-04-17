# PHusion Core

A lightweight PHP MVC framework.

## Installation

1. Download or clone this repository.
2. Copy `config.example.php` to `config.php` and update with your database and email credentials.
3. Set up a web server (e.g., Apache/Nginx) with the project root as the document root.
4. Ensure PHP is installed (7.4 or higher recommended).

## Third-Party Libraries

- **PHPMailer**: Included in `lib/PHPMailer/` (v6.9.1). Source: https://github.com/PHPMailer/PHPMailer. License: LGPL 2.1 (see `LICENSES/PHPMailer-LGPL-2.1.txt`).
- **Parsedown**: Included in `lib/Parsedown.php` (v1.7.4). Source: https://github.com/erusev/parsedown. License: MIT (see `LICENSES/Parsedown-MIT.txt`).

## Future Setup with Composer

To manage PHPMailer and Parsedown with Composer:

1. Install Composer: https://getcomposer.org
2. Run:
   ```
   composer require phpmailer/phpmailer
   composer require erusev/parsedown
   ```
