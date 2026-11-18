<?php

// Claves LADA (código de área) vigentes en México, usadas para validar
// que el teléfono de contacto corresponde a una zona real del país.
// Fuente: Plan Técnico Fundamental de Numeración (IFT). Lista ampliable:
// para dar soporte a más ciudades basta con agregar la clave al arreglo
// correspondiente (dos o tres dígitos).
return [
    'two_digit' => ['55', '33', '81'],

    'three_digit' => [
        // Noroeste
        '664', '686', '646', '612', '624', '662', '644', '631',
        // Norte / Noreste
        '656', '614', '618', '871', '844', '867', '899', '833', '834',
        // Bajío / Centro
        '442', '415', '461', '462', '473', '477', '444', '449', '492', '722', '771',
        // Occidente
        '322', '312', '443', '452',
        // Centro-Sur / Oriente
        '246', '222', '271', '229', '228', '272', '777',
        // Sur / Sureste
        '921', '961', '967', '993', '981', '951',
        // Península de Yucatán
        '999', '983', '998', '984',
    ],
];
