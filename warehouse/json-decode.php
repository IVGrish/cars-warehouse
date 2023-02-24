<?php
require_once '../database-connect.php';

$json = file_get_contents('https://bfdev.ru/test/json.txt');
$warehouse = json_decode($json, true);


$data = array();
$i = 0;
foreach ($warehouse['NA_SKLADE'] as $merchandise => $stock_quantity) {
    $data[$i]['merchandise'] = $merchandise;
    $keys = count($stock_quantity);
    foreach ($stock_quantity as $key => $quantity) {
        $data[$i]['SKLAD_' . $keys - $key . '_QUANTITY'] = $quantity['QUANTITY'];
    }
    $i++;
}

send_to_warehouse($data);

echo "<br>" . get_residual_amount(1)->residual;
echo "<br>" . get_residual_amount(2)->residual;
echo get_residual_amount(3)->residual;

foreach (item_code() as $item) {
    foreach ($item as $code => $remainder) {
        echo "<br>$code - $remainder";
    }
}

create_json();

function create_json(): void
{
    echo "<br>";
    echo json_encode(merge_items(item_code(), merge_store()));
}

function merge_items($items, $stores): array
{
    $new_array = array();

    foreach ($items as $full_code => $item) {
        $total1 = count($items);
        $total2 = count($stores);

        if ($total1 === $total2) {
            for ($x = 0; $x < $total1; $x++) {
                $new_array[$full_code] = $item + $stores[$x];
            }
        }
    }

    return $new_array;
}

function merge_store(): array
{
    $store1 = get_residual_amount(1)->store;
    $store2 = get_residual_amount(2)->store;

    $total1 = count($store1);
    $total2 = count($store2);

    $new_array = array();
    if ($total1 === $total2) {
        for ($x = 0; $x < $total1; $x++) {
            $new_array[$x][] = $store1[$x];
            $new_array[$x][] = $store2[$x];
        }
    }

    return $new_array;
}

function item_code(): array
{
    global $db;

    $select_stmt = $db->prepare("SELECT merchandise, (SKLAD_1_QUANTITY + SKLAD_2_QUANTITY) as sum FROM warehouse");

    $select_stmt->execute();
    $items = $select_stmt->fetchAll();
    $select_stmt->closeCursor();

    $total = count($items);

    $abbreviated_code = array();
    for ($x = 0; $x < $total; $x++) {
        preg_match('#^(.+?)-#', $items[$x]['merchandise'], $matches);
        $abbreviated_code[$items[$x]['merchandise']][$matches[1]] = $items[$x]['sum'];
    }
    return $abbreviated_code;
}

function get_residual_amount($id): StdClass
{
    global $db;

    $select_stmt = $db->prepare("SELECT SKLAD_{$id}_QUANTITY FROM warehouse");
    try {
        $select_stmt->execute();
        $quantity_array = $select_stmt->fetchAll();
        $select_stmt->closeCursor();

        $total = count($quantity_array);
        $residual = 0;
        $store = array();

        for ($x = 0; $x < $total; $x++) {
            $residual += $quantity_array[$x]["SKLAD_{$id}_QUANTITY"];
            $store[$x]['SKLAD_ID'] = $id;
            $store[$x]['QUANTITY'] = $quantity_array[$x]["SKLAD_{$id}_QUANTITY"];
        }
    } catch (PDOException) {
        $info = $select_stmt->errorInfo();
        echo "<br>Unable to select info from database cause of $info[2]";
    } finally {
        if(!isset($residual) && !isset($srore)) {
            $residual = $store= null;
        }
        $obj = new stdClass();
        $obj->residual = $residual;
        $obj->store = $store;
        return $obj;
    }
}

function send_to_warehouse(array $data): void
{
    global $db;

    $insert_stmt = $db->prepare("INSERT INTO warehouse ( `merchandise`, `SKLAD_1_QUANTITY`, `SKLAD_2_QUANTITY` ) "
        . " VALUES ( :merchandise, :SKLAD_1_QUANTITY, :SKLAD_2_QUANTITY )");

    $db->beginTransaction();

    foreach($data as $row) {
        try {
            $insert_stmt->execute($row);
        } catch (PDOException) {
            $info = $insert_stmt->errorInfo();
            echo "Unable to write info to database cause of $info[2]";
            break;
        }
    }

    $db->commit();
}