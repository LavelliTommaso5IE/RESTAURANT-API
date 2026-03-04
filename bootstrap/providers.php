<?php

return [
    App\Providers\AppServiceProvider::class,
    //Server per dire a Larel di tenere in considerazione i domini, se il dominio appartiene ad un tenant, allora carica ruotes/tenant.php
    App\Providers\TenancyServiceProvider::class
];
