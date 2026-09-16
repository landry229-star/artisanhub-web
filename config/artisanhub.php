<?php
// Toutes les villes du Bénin (liste plate pour les selects simples)
$villes = [];
foreach (require __DIR__ . '/benin_villes.php' as $dept => $communes) {
    foreach ($communes as $c) $villes[] = $c;
}
sort($villes);

return [
    'cities' => $villes,

    'cities_by_dept' => require __DIR__ . '/benin_villes.php',

    'categories' => [
        'menuiserie'    => ['label' => 'Menuiserie',     'icon' => '🪵'],
        'couture'       => ['label' => 'Couture',        'icon' => '🧵'],
        'maconnerie'    => ['label' => 'Maçonnerie',     'icon' => '🏗️'],
        'poterie'       => ['label' => 'Poterie',        'icon' => '🏺'],
        'bijouterie'    => ['label' => 'Bijouterie',     'icon' => '💎'],
        'forge'         => ['label' => 'Forge',          'icon' => '🔨'],
        'peinture'      => ['label' => 'Peinture',       'icon' => '🎨'],
        'tissage'       => ['label' => 'Tissage',        'icon' => '🧶'],
        'informatique'  => ['label' => 'Informatique',   'icon' => '💻'],
        'immobilier'    => ['label' => 'Immobilier',     'icon' => '🏠'],
        'restauration'  => ['label' => 'Restauration',   'icon' => '🍽️'],
        'batiment'      => ['label' => 'Bâtiment',       'icon' => '🏗️'],
        'mecanique'     => ['label' => 'Mécanique',      'icon' => '🔧'],
        'electricite'   => ['label' => 'Électricité',    'icon' => '⚡'],
        'coiffure'      => ['label' => 'Coiffure',       'icon' => '💇'],
        'livraison'     => ['label' => 'Livraison',      'icon' => '🚴'],
        'autre'         => ['label' => 'Autre',           'icon' => '🧰'],
    ],

    'delivery_fees' => [
        'same_city'     => 1000,
        'south_benin'   => 1500,
        'long_distance' => 3000,
    ],

    'commission_rate' => 0.10,
];
