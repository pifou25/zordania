<?php
/**
 * Script permettant de déplacer des villages sur une carte avec écart minimum entre eux
 */

// Configuration de la carte
$width = 500;
$height = 500;
$minMinDistance = 8; // Distance minimale entre les villages
$minMaxDistance = 15; // Distance minimale entre les villages
$imageFilename = __DIR__."/map_place_village.png";
$sqlFilename = __DIR__."/map_place_village.sql";

function generateMaxVillages(int $width, int  $height, int  $minDistance, int  $maxDistance): array
{
    // Créer une liste de toutes les cases possibles
    $possiblePositions = [];
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $possiblePositions[] = ['x' => $x, 'y' => $y];
        }
    }
    echo "Map initialisée\n";
    $villages = [];
    $count = 1;
    while (!empty($possiblePositions)) {
        // Choisir une distance aléatoire
        $distance = mt_rand($minDistance, $maxDistance);
        $minDistanceSquared = $distance * $distance;
        // Choisir une case aléatoire parmi les positions possibles
        $randomIndex = array_rand($possiblePositions);
        $selectedPosition = $possiblePositions[$randomIndex];

        // Ajouter le village à la liste
        $villages[] = $selectedPosition;

        // Supprimer les cases autour de la position sélectionnée
        $possiblePositions = array_filter($possiblePositions, function ($position) use ($selectedPosition, $minDistanceSquared) {
            $dx = $position['x'] - $selectedPosition['x'];
            $dy = $position['y'] - $selectedPosition['y'];
            return ($dx * $dx + $dy * $dy >= $minDistanceSquared);
        });

        // Réindexer le tableau pour éviter les décalages dans array_rand
        $possiblePositions = array_values($possiblePositions);
        echo "Village N°" . $count . " ajouté" . " (" . $selectedPosition['x'] . ", " . $selectedPosition['y'] . ") - Place restante : " . count($possiblePositions) . "\n";
        $count++;
    }
    echo "Nombre total de villages : " . count($villages) . "\n";
    return $villages;
}

function createMapImage(int $width, int $height, array $villages, string $filename): void
{
    // Créer une image blanche
    $image = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    // Remplir l'image avec du blanc
    imagefill($image, 0, 0, $white);

    // Dessiner les villages
    foreach ($villages as $village) {
        imagesetpixel($image, $village['x'], $village['y'], $black);
    }

    // Enregistrer l'image
    imagepng($image, $filename);
    imagedestroy($image);
}

function generateSQL(array $villages, string $filename): void
{
    $file = fopen($filename, "w");

    // Vérifier si le fichier est bien ouvert
    if ($file) {
        // Suppression des villages sur la carte
        // Pour toutes les cases hormis la premier ligne on met le type de la case au dessus
        fwrite($file, "UPDATE zrd_map m INNER JOIN zrd_map m2  ON  m2.map_x = m.map_x AND m2.map_y-1 = m.map_y SET m.map_type = m2.map_type WHERE m.map_type in (6,7) AND m.map_y>0;" . PHP_EOL);
        // Pour les cases de la premiere ligne on met le type de la case en dessous
        fwrite($file, "UPDATE zrd_map m INNER JOIN zrd_map m2  ON  m2.map_x = m.map_x AND m2.map_y+1 = m.map_y SET m.map_type = m2.map_type WHERE m.map_type in (6,7) AND m.map_y=0;" . PHP_EOL);
        // Insertion des nouveaux villages
        foreach ($villages as $village) {
            fwrite($file, "UPDATE zrd_map set map_type=6 where map_y={$village['y']} and map_x={$village['x']};" . PHP_EOL);
        }
        // Déménagement des joueurs existants
        fwrite($file, "UPDATE zrd_mbr SET mbr_mapcid=(SELECT map_cid FROM zrd_map WHERE map_type=6 ORDER BY RAND() LIMIT 1);" . PHP_EOL);
        fwrite($file, "UPDATE zrd_map SET map_type=7 WHERE map_cid IN (SELECT mbr_mapcid FROM zrd_mbr);" . PHP_EOL);
        // Retour à la maison des legions 
        fwrite($file, "UPDATE zrd_leg SET leg_cid= (SELECT mbr_mapcid FROM zrd_mbr WHERE mbr_mid=leg_mid), leg_dest = 0, leg_etat = IF(leg_etat > 3, 3, leg_etat)" . PHP_EOL);


        // Les village sont mis sur terre
        fwrite($file, "UPDATE zrd_map SET map_rand = 1 WHERE map_type in (6,7) AND map_rand NOT IN (1,2,3);" . PHP_EOL);

        // Les "plages" sont a recalculer suite aux déplacements des villages
        // Suppression des plages existantes
        fwrite($file, "UPDATE zrd_map SET map_rand = 0 WHERE map_type =2;" . PHP_EOL);
        // Plage au Nord  (1 = Haut)
        fwrite($file, "UPDATE zrd_map m INNER JOIN zrd_map m2  ON  m2.map_x = m.map_x AND m2.map_y = m.map_y-1 AND m2.map_type != 2 SET m.map_rand = m.map_rand | 1 WHERE m.map_type = 2;" . PHP_EOL);
        // Plage au Sud  (2 = Bas)
        fwrite($file, "UPDATE zrd_map m INNER JOIN zrd_map m2  ON  m2.map_x = m.map_x AND m2.map_y = m.map_y+1 AND m2.map_type != 2 SET m.map_rand = m.map_rand | 2 WHERE m.map_type = 2;" . PHP_EOL);
        // Plage a l'Est  (4 = Droite)
        fwrite($file, "UPDATE zrd_map m INNER JOIN zrd_map m2  ON  m2.map_x = m.map_x+1 AND m2.map_y = m.map_y AND m2.map_type != 2 SET m.map_rand = m.map_rand | 4 WHERE m.map_type = 2;" . PHP_EOL);
        // Plage a l'Oust  (8 = Gauche)
        fwrite($file, "UPDATE zrd_map m INNER JOIN zrd_map m2  ON  m2.map_x = m.map_x-1 AND m2.map_y = m.map_y AND m2.map_type != 2 SET m.map_rand = m.map_rand | 8 WHERE m.map_type = 2;" . PHP_EOL);



        // Fermer le fichier
        fclose($file);
    } else {
        echo "Impossible d'ouvrir le fichier '$filename'.\n";
    }
}




// Générer autant de villages que possible
$villages = generateMaxVillages($width, $height, $minMinDistance, $minMaxDistance);
// Générer l'image
createMapImage($width, $height, $villages, $imageFilename);
// Générer les requêtes de mise à jour
generateSQL($villages, $sqlFilename);

