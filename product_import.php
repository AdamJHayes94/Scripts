<?php

ini_set('memory_limit', '1024M');
ini_set('display_errors', '1');
ini_set('max_execution_time', -1);
$time = explode(" ", microtime());
$start = $time[1] + $time[0];
echo '<pre>';


require_once '../app/Mage.php';
umask(0);
Mage::app();


$magmi_helper = Mage::helper('magmi')->initMagmi(TYPE_INSERT);
$dp = $magmi_helper->getDp();


$filename = Mage::getBaseDir().'/made_up/csv_files/new_products.csv';
$file = fopen($filename, 'r');


if($file === false)
{
    echo "[$filename] file not found!";
    exit();
}

$headers = array();
$count = 1;

$last_categories = array();

while(($data = fgetcsv($file)) !== FALSE)
{
    $category_list = array();
    foreach($data as $key => $value)
    {
        $data[$key] = trim($value);
    }
    if($count > 1)
    {
        $product_details = array(
            'store'                 => 'admin',
            'type'              => 'simple',
            'sku'               => $data[$headers['sku']],
            'fbtitems'            => $data[$headers['fbt_skus']],

        );

        echo $data[$headers['sku']] . ' imported <br />';

        $dp->ingest($product_details);
        flush_buffers();

    }
    else
    {
        $column_count =0;
        foreach($data as $key => $value)
        {
            $headers[strtolower(str_replace('-','_',str_replace('/','_',str_replace(' ','_',trim($value)))))] = $column_count;
            $column_count++;
        }
        $count++;
    }

}

$magmi_helper->endSession();

function flush_buffers()
{
    ob_end_flush();
    ob_flush();
    flush();
    ob_start();
}
