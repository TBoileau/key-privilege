<?php

ini_set("display_errors",1);
ini_set("memory_limit","512M");
error_reporting(E_ALL);
set_time_limit(0);
date_default_timezone_set('Europe/Paris');

require __DIR__ . "/../vendor/autoload.php";

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\String\Slugger\AsciiSlugger;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env.local');

header('Content-Type: text/html; charset=ISO-8859-1');

$serveur = new nusoap_server;

$serveur->register('loadProduits');
$serveur->register('loadDetailsProduits');
$serveur->register('loadCategories');
$serveur->register('loadMarques');
$serveur->register('loadUnivers');
$serveur->register('loadUniversCat');
$serveur->register('loadProprietes');
$serveur->register('loadValeurs');
$serveur->register('loadSelections');
$serveur->register('loadSelectionSynchro');
$serveur->register('loadSelectionHisto');
$serveur->register('loadSelectionProduit');
$serveur->register('loadFichiers');
$serveur->register('loadImages');

$database = parse_url($_ENV["DATABASE_URL"]);

$PDO = new \PDO(
    sprintf("mysql:host=%s;dbname=%s", $database["host"], trim($database["path"], "/")),
    $database["user"],
    $database["pass"]
);

$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$PDO->query("SET CHARACTER SET utf8;");
$PDO->query("SET FOREIGN_KEY_CHECKS=0;");

function loadImages() {
    $images = array();

    if (!$folder_handle = opendir(sprintf("%s/../public/shop/products", __DIR__))) {
        return $images;
    } else{
        while(false !== ($filename = readdir($folder_handle))) {
            if( strcmp($filename, ".")!=0 && strcmp($filename, "..")!=0 ) {
                $images[] = $filename;
            }
        }
        closedir($folder_handle);
    }
    return $images;
}

function loadFichiers($file) {
    $zip = new ZipArchive();

    $zip->open(sprintf("%s/../public/shop/sync/%s/%s", __DIR__, date("d-m-Y"), $file));

    $count = $zip->count();

    $zip->extractTo(sprintf("%s/../public/shop/products", __DIR__));

    $zip->close();

    return $count;
}

function loadUnivers($file) {
    $slugger = new AsciiSlugger();

    global $PDO;
    $numberOfUniversesProcessed = 0;
    $resource = fopen(
        sprintf(
            "%s/../public/shop/sync/%s/%s",
            __DIR__,
            date("d-m-Y"),
            $file
        ),
        "r"
    );
    if ($resource !== FALSE) {
        while (($data = fgetcsv($resource, 0, ";")) !== FALSE) {
            $statement = $PDO->prepare("SELECT COUNT(*) as numberOfUniverses FROM universe WHERE id = ?");
            $statement->execute([$data[0]]);

            if(intval($statement->fetch(\PDO::FETCH_OBJ)->numberOfUniverses) === 0){
                $PDO->prepare("INSERT INTO universe SET id = ?, name = ?, slug = ?")->execute([$data[0], $data[3], $slugger->slug($data[3])->lower()->toString()]);
            }else{
                $PDO->prepare("UPDATE universe SET name = ?, slug = ? WHERE id = ?")->execute([$data[3], $slugger->slug($data[3])->lower()->toString(), $data[0]]);
            }

            $numberOfUniversesProcessed++;
        }
        fclose($resource);
    }
    return $numberOfUniversesProcessed;
}

function loadCategories($file) {
    $slugger = new AsciiSlugger();
    global $PDO;
    $numberOfCategoriesProcessed = 0;
    $resource = fopen(
        sprintf(
            "%s/../public/shop/sync/%s/%s",
            __DIR__,
            date("d-m-Y"),
            $file
        ),
        "r"
    );
    if ($resource !== FALSE) {
        $PDO->query("TRUNCATE TABLE category");

        while (($data = fgetcsv($resource, 0, ";")) !== FALSE) {
            $PDO->prepare("INSERT INTO category SET id = ?, name = ?, lft = ?, rgt = ?, lvl = ?, root_id = 1, slug = ?")->execute([
                $data[0],
                $data[1],
                $data[2],
                $data[3],
                $data[4],
                sprintf("%s-%s", $data[0], $slugger->slug($data[1])->lower()->toString()),
            ]);

            $numberOfCategoriesProcessed++;
        }
        fclose($resource);
    }
    $PDO->query("UPDATE category AS a INNER JOIN category AS b ON (b.lvl=a.lvl-1 AND a.rgt<b.rgt AND a.lft>b.lft) SET a.parent_id = b.id");
    return $numberOfCategoriesProcessed;
}

function loadMarques($file) {
    global $PDO;
    $numberOfBrandsProcessed = 0;
    $resource = fopen(
        sprintf(
            "%s/../public/shop/sync/%s/%s",
            __DIR__,
            date("d-m-Y"),
            $file
        ),
        "r"
    );
    if ($resource !== FALSE) {
        while (($data = fgetcsv($resource, 0, ";")) !== FALSE) {

            $statement = $PDO->prepare("SELECT COUNT(*) as numberOfBrands FROM brand WHERE id = ?");
            $statement->execute([$data[0]]);

            if(intval($statement->fetch(\PDO::FETCH_OBJ)->numberOfBrands) === 0){
                $PDO->prepare("INSERT INTO brand SET id = ?, name = ?")->execute([$data[0], $data[1]]);
            }else{
                $PDO->prepare("UPDATE brand SET name = ? WHERE id = ?")->execute([$data[1], $data[0]]);
            }

            $numberOfBrandsProcessed++;
        }
        fclose($resource);
    }
    return $numberOfBrandsProcessed;
}

function loadUniversCat($file) {
    global $PDO;
    $numberOfUniversesProcessed = 0;
    $resource = fopen(
        sprintf(
            "%s/../public/shop/sync/%s/%s",
            __DIR__,
            date("d-m-Y"),
            $file
        ),
        "r"
    );
    if ($resource !== FALSE) {
        $PDO->query("TRUNCATE TABLE universe_categories");
        while (($data = fgetcsv($resource, 0, ";")) !== FALSE) {
            $PDO->prepare("INSERT INTO universe_categories SET universe_id = ?, category_id = ?")->execute([$data[0], $data[3]]);
            $numberOfUniversesProcessed++;
        }
        fclose($resource);
    }
    return $numberOfUniversesProcessed;
}

function loadDetailsProduits($file) {
    $slugger = new AsciiSlugger();
    $fields = [1 => "name", 2 => "description", 4 => "image"];
    global $PDO;

    $numberOfDataUpdated = 0;

    $resource = fopen(
        sprintf(
            "%s/../public/shop/sync/%s/%s",
            __DIR__,
            date("d-m-Y"),
            $file
        ),
        "r"
    );

    $products = [];

    if ($resource !== FALSE) {

        while (($data = fgetcsv($resource, 0, ";")) !== FALSE) {
            if(isset($fields[$data[1]])){
                $products[$data[0]][$fields[$data[1]]] = $data[2];
                $numberOfDataUpdated++;
            }
        }

        fclose($resource);
    }

    foreach($products as $id => $product){
        $product["slug"] = sprintf("%s-%s", $id, $slugger->slug($product["name"])->lower()->toString());
        $product["id"] = $id;
        $PDO->prepare("UPDATE product SET name = :name, slug=:slug, description = :description, image = :image WHERE id=:id")->execute($product);
    }

    $PDO->query("UPDATE product SET image = REPLACE(image,'Images/','')");

    $PDO->query("
        UPDATE category AS a 
        INNER JOIN (
            SELECT MAX(id) AS product, category_id 
            FROM product 
            GROUP BY category_id
        ) AS b ON (b.category_id = a.id) 
        SET last_product_id = product
    ");

    return $numberOfDataUpdated;
}

function loadProduits($file){
    global $PDO;
    $numberOfProductsAdded = 0;
    $numberOfProductsUpdated = 0;
    $resource = fopen(
        sprintf(
            "%s/../public/shop/sync/%s/%s",
            __DIR__,
            date("d-m-Y"),
            $file
        ),
        "r"
    );

    if ($resource !== FALSE) {
        $PDO->query("UPDATE product SET active = 0");

        while (($data = fgetcsv($resource, 0, ";")) !== FALSE) {

            $statement = $PDO->prepare("SELECT COUNT(*) as numberOfProducts FROM product WHERE id = ?");
            $statement->execute([$data[0]]);

            $amount = 0;

            eval(sprintf("\$amount = %s;", str_replace("[CHAMP]",$data[16],$data[15])));

            if(intval($statement->fetch(\PDO::FETCH_OBJ)->numberOfProducts) === 0){

                $PDO->prepare("
					INSERT INTO product
					SET 
                        id = ?,
                        brand_id = ?,
                        category_id = ?,
                        updated_at = ?,
                        reference = ?,
                        active=1,
                        amount = ?,
                        name = '',
                        slug=?,
                        description = '',
                        image = ''
                ")->execute([
                    $data[0],
                    $data[1],
                    $data[4],
                    $data[7],
                    $data[9],
                    $amount,
                    $data[0]
                ]);

                $numberOfProductsAdded++;
            }else{
                $PDO->prepare("
					UPDATE product
					SET 
                        brand_id = ?,
                        category_id = ?,
                        updated_at = ?,
                        reference = ?,
                        active=1,
                        amount = ?
                    WHERE id = ?
                ")->execute([
                    $data[1],
                    $data[4],
                    $data[7],
                    $data[9],
                    $amount,
                    $data[0]
                ]);

                $numberOfProductsUpdated++;
            }
        }

        fclose($resource);
    }
    $PDO->prepare("
        UPDATE category AS c1
        INNER JOIN (
            SELECT c.id, IFNULL(p.nb, 0) as nb
            FROM category AS c
            LEFT JOIN category AS cc ON c.lft >= cc.lft AND c.rgt <= cc.rgt
            LEFT JOIN (SELECT COUNT(id) as nb, category_id FROM product GROUP BY category_id) AS p ON (p.category_id = cc.id)
            GROUP BY c.id
        ) AS c2 ON (c1.id = c2.id)
        SET c1.number_of_products = c2.nb
    ")->execute([]);

    return ["ADD"=>$numberOfProductsAdded,"UPDATE"=>$numberOfProductsUpdated];
}

function loadProprietes($file)
{
    return 0;
}

function loadValeurs($file)
{
    return 0;
}

function loadSelections($file)
{
    return 1;
}

function loadSelectionProduit($file)
{
    return 1;
}

function loadSelectionHisto($file)
{
    return 1;
}

function loadSelectionSynchro($file)
{
    return 1;
}

@$serveur->service(file_get_contents("php://input"));
