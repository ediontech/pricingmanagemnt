<?php

return [
    ['GET', '#^/$#', 'index'],
    ['GET', '#^/api/dashboard$#', 'dashboard'],
    ['POST', '#^/api/bulk-update$#', 'bulkUpdate'],
    ['POST', '#^/api/copy-rates$#', 'copyRates'],
    ['GET', '#^/api/integrity$#', 'integrity'],
    ['GET', '#^/api/history$#', 'history'],
    ['POST', '#^/api/lock$#', 'toggleLock'],
];
