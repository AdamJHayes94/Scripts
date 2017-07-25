<?php
ini_set('display_errors', '1');
require_once '../app/Mage.php';
umask(0);
Mage::app('default');

$customers = Mage::getModel('customer/customer')->getCollection()
						->addAttributeToSelect(array('firstname','lastname','email'));

$customerArray = array();
foreach($customers as $customer) {

	$orderCollection = Mage::getModel('sales/order')->getCollection()
    	->addFieldToFilter('customer_id', array('eq' => array($customer->getId())))
    	->addAttributeToSelect('grand_total')
		->setOrder('created_at', 'desc');
	
	$highestOrderValue = 0;
	$totalValue = 0;
	if(count($orderCollection) != 0) {
		foreach($orderCollection as $order) {
			if($order->getGrandTotal() > $highestOrderValue) {
				$highestOrderValue = $order->getGrandTotal();
			}
			$totalValue += $order->getGrandTotal();
		}
		
	}
	$customer = Mage::getModel('customer/customer')->load($customer->getId());
	$address = $customer->getDefaultBillingAddress();
	
	
	$customerArray[] = array(
		'id'				=> $customer->getId(),
		'firstname'			=> $customer->getFirstname(),
		'lastname'			=> $customer->getLastname(),
		'email'				=> $customer->getEmail(),
		'average order value'	=> $totalValue / count($orderCollection),
		'highest order value' 	=> number_format($highestOrderValue,2),
		'number of orders' 	=> count($orderCollection),
	);
}
$fp = fopen('customer-export.csv', 'w');
foreach ($customerArray as $customer) {
	fputcsv($fp, $customer);
}
fclose($fp);