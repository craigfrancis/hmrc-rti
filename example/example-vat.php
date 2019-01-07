<?php

//--------------------------------------------------
// Gateway

	list($hmrc_config, $hmrc_gateway) = hmrc_gateway_get();

	$hmrc_gateway->log_table_set($db, DB_PREFIX . 'finance_vat_rti');

//--------------------------------------------------
// Pending requests

	$requests = $hmrc_gateway->request_list('HMRC-VAT-DEC');
	if (count($requests) > 0) {

		// foreach ($requests as $request) {
		// 	$hmrc_gateway->request_delete($request);
		// }

		exit_with_error('Incomplete requests found', debug_dump($requests));

	}

//--------------------------------------------------
// Submission

	$hmrc_vat = new hmrc_vat();

	$hmrc_vat->details_set(array(
			'vat_registration_number' => preg_replace('/[^0-9]/', '', config::get('hmrc.vat_reg')), // No spaces
			'year'                    => date('Y', ($dateRangeEnd - 1)),
			'month'                   => date('m', ($dateRangeEnd - 1)),
			'sender'                  => 'Company',
			'vat_due_output'          => $totalAmountVatPay,
			'vat_due_acquisitions'    => 0,
			'vat_total'               => $totalAmountVatPay,
			'vat_reclaimed'           => 0,
			'vat_net'                 => $totalAmountVatPay,
			'total_sales'             => $totalAmountGross,
			'total_purchases'         => 0,
			'total_supplies'          => 0,
			'total_acquisitions'      => 0,
		));

//--------------------------------------------------
// Record submission

	$hmrc_submission = $hmrc_vat->request_body_get_xml();

	$sql = 'UPDATE
				' . DB_PREFIX . 'finance_vat AS fv
			SET
				fv.hmrc_submission = ?
			WHERE
				fv.id = ?';

	$parameters = array();
	$parameters[] = array('s', $hmrc_submission);
	$parameters[] = array('i', $record_id);

	$db->query($sql, $parameters);

//--------------------------------------------------
// Send and poll for response

	$request = $hmrc_gateway->request_submit($hmrc_vat);

	// debug($request);

	$k = 0;

	while ($request['status'] === NULL && $k++ < 5) {

		$loading->update('Waiting for response (try ' . $k . ')');

		$request = $hmrc_gateway->request_poll($request);

		// debug($request);

	}

	$hmrc_collection_date = '0000-00-00';

	if ($request['status'] === NULL) {

		$hmrc_response = 'Stopped waiting for a HRMC response.' . "\n\n" . debug_dump($request);

	} else {

		$hmrc_response = debug_dump($request);

		if (isset($request['response_details']['collection_date'])) {
			$hmrc_collection_date = $request['response_details']['collection_date'];
		}

	}

//--------------------------------------------------
// Record response

	$sql = 'UPDATE
				' . DB_PREFIX . 'finance_vat AS fv
			SET
				fv.paid = ?,
				fv.hmrc_response = ?
			WHERE
				fv.id = ?';

	$parameters = array();
	$parameters[] = array('s', $hmrc_collection_date);
	$parameters[] = array('s', $hmrc_response);
	$parameters[] = array('i', $record_id);

	$db->query($sql, $parameters);

//--------------------------------------------------
// Delete request (cleanup)

	$hmrc_gateway->request_delete($request);
	
?>