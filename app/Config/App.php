<?php

declare(strict_types=1);

namespace App\Config;

use App\Core\Env;

final class App
{
    public string $appName;
    public string $baseURL;
    public string $environment;
    public bool $debug;
    public string $defaultYear;
    public string $defaultMonth;
    public string $defaultAcriss;

    public function __construct()
    {
        $this->appName = Env::get('app.name', Env::get('APP_NAME', 'Pricing Management Portal')) ?? 'Pricing Management Portal';
        $this->baseURL = Env::get('app.baseURL', Env::get('APP_URL', 'http://127.0.0.1:8000')) ?? 'http://127.0.0.1:8000';
        $this->environment = Env::get('CI_ENVIRONMENT', Env::get('APP_ENV', 'production')) ?? 'production';
        $this->debug = filter_var(Env::get('app.debug', Env::get('APP_DEBUG', 'false')), FILTER_VALIDATE_BOOLEAN);
        $this->defaultYear = Env::get('pricing.defaultYear', '2026') ?? '2026';
        $this->defaultMonth = Env::get('pricing.defaultMonth', '2') ?? '2';
        $this->defaultAcriss = Env::get('pricing.defaultAcriss', 'CCAR,ECAR,FFAR') ?? 'CCAR,ECAR,FFAR';
    }
}
