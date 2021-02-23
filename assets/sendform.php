<?php

// get value from array
function array_get(array $array, $key, $default = null)
{
	return (array_key_exists($key, $array)) ? $array[$key] : $default;
}

// get string value from array
function array_get_string(array $array, $key, $default = '', $trim = true)
{
	$val = array_get($array, $key);
	if (is_string($val)) {
		return ($trim) ? trim($val) : $val;
	}
	return $default;
}

// takes a form array and validates it
function validateForm(array $form)
{
	// array of error messages
	$errors = array();

	// for each field in the form
	foreach ($form as $field)
	{
		// if the field doesnt require any validation, skip it
		if (!isset($field['validation'])) continue;

		// for each validation type
		foreach ($field['validation'] as $validate)
		{
			// if the field is empty, there is nothing to check
			if (trim($field['value']) === '') break;

			switch($validate)
			{
				case 'email':
					if(!filter_var($field['value'], FILTER_VALIDATE_EMAIL)) {
						$errors[] = '"' . $field['value'] . '" is an invalid email address.';
					}
				break;
				case 'number':
					if(!preg_match('/^[0-9 ]+$/', $field['value'])) {
						$errors[] = '"' . $field['value'] . '" is an invalid number.';
					}
				break;
				case 'alpha':
					if(!preg_match('/^[a-zA-Z ]+$/', $field['value'])) {
						$errors[] = '"' . $field['value'] . '" contains invalid characters. This field only accepts letters and spaces.';
					}
				break;
			}
		}

		// if field is required and empty
		if (isset($field['required']) && trim($field['value']) === '') {
			// overwrite errors with only the required error message
			$errors[] = '"' . $field['name'] . '" is a required field.';
		}
	}

	// return an array of errors
	return $errors;
}

// define form
$FORM = array(
		'type' => array(
			'name' => 'Type',
		),
		'charm-catalog-id-a' => array(
			'name' => 'Charm Catalog ID (A#)',
			'validation' => array('default'),
			'required' => true,
		),
		'pendant-catalog-id-b' => array(
			'name' => 'Pendant Catalog ID (B#)',
			'validation' => array('default'),
		),
		'magnet-catalog-id-c' => array(
			'name' => 'Magnet Catalog ID (C#)',
			'validation' => array('default'),
		),
		'name' => array(
			'name' => 'Name',
			'validation' => array('default'),
			'required' => true,
		),
		'address-line-1' => array(
			'name' => 'Address Line 1',
			'validation' => array('default'),
			'required' => true,
		),
		'address-line-2' => array(
			'name' => 'Address Line 2',
			'validation' => array('default'),
		),
		'special-address-instructions' => array(
			'name' => 'Special Address Instructions',
			'validation' => array('default'),
		),
		'city' => array(
			'name' => 'City',
			'validation' => array('default'),
			'required' => true,
		),
		'state' => array(
			'name' => 'State',
		),
		'zip-code' => array(
			'name' => 'Zip Code',
			'validation' => array('default'),
			'required' => true,
		),
		'email' => array(
			'name' => 'Email',
			'validation' => array('default'),
			'required' => true,
		),
		'additional-notes' => array(
			'name' => 'Additional Notes',
		),
);

// populate form values from POST
foreach ($FORM as $field_name => &$field_data) {
	
	$value_type = gettype($_POST[$field_name]);

	if ($value_type == 'string')
	{
		// get the string value from POST data
		$field_data['value'] = array_get_string($_POST, $field_name);
	}

	elseif ($value_type == 'array')
	{
		// these are multiple checkbox values
		// we need to concat them into a string
		$concat = '';
		
		foreach (array_get($_POST, $field_name) as $item) {
			$concat .= $item . '; ';
		}

		$field_data['value'] = $concat;
	}
}

// unset referenced variable to avoid quirks
unset($field_data);

$AJAX = array_get_string($_POST, 'request_method') === 'ajax';
$ERRORS = validateForm($FORM);
$SUCCESS = count($ERRORS) ? false : true;


// send email
if ($SUCCESS)
{
	// Full Name: $fullname \nEmail Address: $emailaddress
	$formcontent = '';
	$emailtemplate = "<html><head><title>Order Form</title></head><body><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"200\" style=\"background:#98826a; align:center\"><tr><td style=\"vertical-align:top; padding:0; width:200px\"></td><td style=\"vertical-align:top; padding:0;\"><table style=\"width:650px; margin:70px auto; border-collapse:collapse; font:13px sans-serif;\"><tr><td style=\"vertical-align:top; padding:0; height:70px\">&nbsp;</td></tr><tr><td style=\"vertical-align:top; padding:0; padding:0\"><table style=\"width:100%; border-collapse:collapse; border:1px solid #241f1c; border-bottom-color:#6c5d53; border-top-color:#6c5d53; font-size:13px\"><tr style=\"background:white\"><!-- top stripe --><td background=\"http://formbakery.com/stripe.png\" style=\"vertical-align:top; padding:0; background:url(http://formbakery.com/stripe.png) left top no-repeat;\" colspan=\"2\">&nbsp;</td></tr><tr style=\"background:white;\"><td colspan=\"2\" style=\"vertical-align:top; padding:0; padding:40px; font-size:21px; font-weight:bold\">Order Form</td></tr><tr style=\"background:white\"><td style=\"vertical-align:top; padding:0; padding:0 0 0 40px; padding-bottom:20px; width:120px; font-weight:bold\">Type:</td><td style=\"vertical-align:top; padding:0; padding-right:40px; padding-bottom:20px\">{$FORM['type']['value']}</td></tr><tr style=\"background:white\"><td style=\"vertical-align:top; padding:0; padding:0 0 0 40px; padding-bottom:20px; width:120px; font-weight:bold\">Charm Catalog ID (A#):</td><td style=\"vertical-align:top; padding:0; padding-right:40px; padding-bottom:20px\">{$FORM['charm-catalog-id-a']['value']}</td></tr><tr style=\"background:white\"><td style=\"vertical-align:top; padding:0; padding:0 0 0 40px; padding-bottom:20px; width:120px; font-weight:bold\">Pendant Catalog ID (B#):</td><td style=\"vertical-align:top; padding:0; padding-right:40px; padding-bottom:20px\">{$FORM['pendant-catalog-id-b']['value']}</td></tr><tr style=\"background:white\"><td style=\"vertical-align:top; padding:0; padding:0 0 0 40px; padding-bottom:20px; width:120px; font-weight:bold\">Magnet Catalog ID (C#):</td><td style=\"vertical-align:top; padding:0; padding-right:40px; padding-bottom:20px\">{$FORM['magnet-catalog-id-c']['value']}</td></tr><tr style=\"background:white\"><td style=\"vertical-align:top; padding:0; padding:0 0 0 40px; padding-bottom:20px; width:120px; font-weight:bold\">Name:</td><td style=\"vertical-align:top; padding:0; padding-right:40px; padding-bottom:20px\">{$FORM['name']['value']}</td></tr><tr style=\"background:white\"><td style=\"vertical-align:top; padding:0; padding:0 0 0 40px; padding-bottom:20px; width:120px; font-weight:bold\">Address Line 1:</td><td style=\"vertical-align:top; padding:0; padding-right:40px; padding-bottom:20px\">{$FORM['address-line-1']['value']}</td></tr><tr style=\"background:white\"><td style=\"vertical-align:top; padding:0; padding:0 0 0 40px; padding-bottom:20px; width:120px; font-weight:bold\">Address Line 2:</td><td style=\"vertical-align:top; padding:0; padding-right:40px; padding-bottom:20px\">{$FORM['address-line-2']['value']}</td></tr><tr style=\"background:white\"><td style=\"vertical-align:top; padding:0; padding:0 0 0 40px; padding-bottom:20px; width:120px; font-weight:bold\">Special Address Instructions:</td><td style=\"vertical-align:top; padding:0; padding-right:40px; padding-bottom:20px\">{$FORM['special-address-instructions']['value']}</td></tr><tr style=\"background:white\"><td style=\"vertical-align:top; padding:0; padding:0 0 0 40px; padding-bottom:20px; width:120px; font-weight:bold\">City:</td><td style=\"vertical-align:top; padding:0; padding-right:40px; padding-bottom:20px\">{$FORM['city']['value']}</td></tr><tr style=\"background:white\"><td style=\"vertical-align:top; padding:0; padding:0 0 0 40px; padding-bottom:20px; width:120px; font-weight:bold\">State:</td><td style=\"vertical-align:top; padding:0; padding-right:40px; padding-bottom:20px\">{$FORM['state']['value']}</td></tr><tr style=\"background:white\"><td style=\"vertical-align:top; padding:0; padding:0 0 0 40px; padding-bottom:20px; width:120px; font-weight:bold\">Zip Code:</td><td style=\"vertical-align:top; padding:0; padding-right:40px; padding-bottom:20px\">{$FORM['zip-code']['value']}</td></tr><tr style=\"background:white\"><td style=\"vertical-align:top; padding:0; padding:0 0 0 40px; padding-bottom:20px; width:120px; font-weight:bold\">Email:</td><td style=\"vertical-align:top; padding:0; padding-right:40px; padding-bottom:20px\">{$FORM['email']['value']}</td></tr><tr style=\"background:white\"><td style=\"vertical-align:top; padding:0; padding:0 0 0 40px; padding-bottom:20px; width:120px; font-weight:bold\">Additional Notes:</td><td style=\"vertical-align:top; padding:0; padding-right:40px; padding-bottom:20px\">{$FORM['additional-notes']['value']}</td></tr><tr style=\"background:white; padding-bottom:40px\"><td colspan=\"2\" style=\"vertical-align:top; padding:0; padding:40px\">&nbsp;</td></tr></table></td></tr><tr><td style=\"vertical-align:top; padding:0; background:#e3dedb; height:1px; line-height:1px; padding:0; border:10px solid #98826a; border-top:0; border-bottom:0\">&nbsp;</td></tr><tr><td style=\"vertical-align:top; padding:0; height:100px\">&nbsp;</td></tr></table></td><td style=\"vertical-align:top; padding:0; width:200px\"></td></tr></table></body></html>";
	
	foreach ($FORM as $field_name => $field_data) {
		$formcontent .= $field_data['name'] . ': ' . $field_data['value'] . " \n";
	}

	$formcontent = wordwrap($formcontent, 70, "\n", true);
	$recipient = 'gveltaine+chaff2treasure@gmail.com';
	$subject = 'Order Form';
	$mailheader = "Content-Type: text/html;"; 

	mail($recipient, $subject, $emailtemplate, $mailheader);
}

?>

<?php if ($AJAX): ?>
	<?php if ($SUCCESS): ?>
		PASS
	<?php else: ?>
		FAIL
	<?php endif; ?>
<?php else: ?>
	<!DOCTYPE html>
	<html>
		<head>
			<title>
				<?php if ($SUCCESS): ?>
					Form sent!
				<?php else: ?>
					Your form has errors :/
				<?php endif; ?>
			</title>
			<style type="text/css">
				body {margin:100px; font:16px/1.5 sans-serif; color:#111}
				h1 {font-size:32px; margin:0; font-weight:bold}
				h2 {font-size:18px; margin:0 0 20px 0}
				ol, li {list-style-position:inside; padding-left:0; margin-left:0}
			</style>
		</head>
		<body>
			<?php if ($SUCCESS): ?>
				<h1>Success!</h1>
				<h2>Your form was sent successfully.</h2>
			<?php else: ?>
				<h1>We found a few errors :-(</h1>
				<h2>Please fix these errors and try again</h2>
				<ol>
					<?php foreach ($ERRORS as $error): ?>
						<li><?php echo $error; ?></li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
			<a href="<?php echo $_SERVER['HTTP_REFERER'] ?>">Back to form</a>
		</body>
	</html>
<?php endif; ?>
