<?php PHP_SAPI === 'cli' or http_response_code(404) && exit(1);

// Constants
define('FILE_SOURCE_CODE', '');
define('CSP_CODE', '');
define('DATA_PROVIDER_CODE', '');

/**
 * Generates a header row for the file.
 *
 * @return string Header row
 */
function createHeader($filename) {
    return 'HDR' . str_replace('.','',$filename) . date('YmdHis') . str_repeat(' ', 870) . "\n";
}

/**
 * Generates a trailer row for the file.
 *
 * @param int $recordCount Number of data records in the file
 * @return string Trailer row
 */
function createTrailer($filename,$recordCount) {
    $seq = explode('.',$filename);
    return 'TRL' . $seq[1] . date('YmdHis') . str_pad($recordCount, 7, '0', STR_PAD_LEFT) . str_repeat(' ', 874) . "\n";
}

/**
 * Validates and pads data fields based on provided specifications.
 *
 * @param string $data Data to be formatted
 * @param int $length Length of the field
 * @param string $type 'N' for numeric, 'X' for alphanumeric
 * @param bool $mandatory Whether the field is mandatory
 * @param string $padType 'L' for left, 'R' for right (default: right)
 * @return string Formatted data field
 */
function formatField($data, $length, $type = 'X', $mandatory = true, $padType = 'R') {
    if ($mandatory && trim($data) === '') {
        throw new Exception("Mandatory field is empty.");
    }

    $padChar = $type === 'N' ? '0' : ' ';
    $padDirection = $padType === 'R' ? STR_PAD_LEFT : STR_PAD_RIGHT;

    return str_pad(substr($data, 0, $length), $length, $padChar, $padDirection);
}

/**
 * Creates a formatted data row based on input data array.
 * Ensures validation and correct formatting as per specifications.
 *
 * @param array $data Data array corresponding to each field
 * @return string Formatted data row
 * @throws Exception If mandatory fields are missing or data is improperly formatted
 */
function createDataRow($data) {
    $formattedRow = 
        formatField($data['public_number'], 20, 'X', true, 'L') .
        formatField($data['service_status_code'], 1, 'X', true) .
        formatField($data['pending_flag'], 1, 'X', true) .
        formatField($data['cancel_pending_flag'], 1, 'X', true) .
        formatField($data['customer_name'], 40, 'X', true, 'L') .
        formatField($data['customer_name2'], 40, 'X', false, 'L') .
        formatField($data['long_name'], 80, 'X', false, 'L') .
        formatField($data['customer_title'], 12, 'X', false, 'L') .
        formatField($data['finding_name1'], 40, 'X', false, 'L') .
        formatField($data['finding_name2'], 40, 'X', false, 'L') .
        formatField($data['finding_title'], 12, 'X', false, 'L') .
        formatField($data['service_building_type'], 6, 'X', false, 'L') .
        formatField($data['service_building_1st_nr'], 5, 'X', false) .
        formatField($data['service_building_1st_suffix'], 1, 'X', false) .
        formatField($data['service_building_2nd_nr'], 5, 'X', false) .
        formatField($data['service_building_2nd_suffix'], 1, 'X', false) .
        formatField($data['service_building_floor_type'], 2, 'X', false) .
        formatField($data['service_building_floor_nr'], 4, 'X', false) .
        formatField($data['service_building_floor_nr_suffix'], 1, 'X', false) .
        formatField($data['service_building_property'], 40, 'X', false, 'L') .
        formatField($data['service_building_location'], 30, 'X', false, 'L') .
        formatField($data['service_street_house_nr1'], 5, 'X', false) .
        formatField($data['service_street_house_nr1_suffix'], 3, 'X', false) .
        formatField($data['service_street_house_nr2'], 5, 'X', false) .
        formatField($data['service_street_house_nr2_suffix'], 1, 'X', false) .
        formatField($data['service_street_name1'], 25, 'X', true, 'L') .
        formatField($data['service_street_type1'], 8, 'X', false) .
        formatField($data['service_street_suffix1'], 6, 'X', false) .
        formatField($data['service_street_name2'], 25, 'X', false, 'L') .
        formatField($data['service_street_type2'], 4, 'X', false) .
        formatField($data['service_street_suffix2'], 2, 'X', false) .
        formatField($data['service_address_locality'], 40, 'X', true, 'L') .
        formatField($data['service_address_state'], 3, 'X', true) .
        formatField($data['service_address_postcode'], 4, 'N', true) .
        formatField($data['directory_building_type'], 6, 'X', false) .
        formatField($data['directory_building_1st_nr'], 5, 'X', false) .
        formatField($data['directory_building_1st_suffix'], 1, 'X', false) .
        formatField($data['directory_building_2nd_nr'], 5, 'X', false) .
        formatField($data['directory_building_2nd_suffix'], 1, 'X', false) .
        formatField($data['directory_building_floor_type'], 2, 'X', false) .
        formatField($data['directory_building_floor_nr'], 4, 'X', false) .
        formatField($data['directory_building_floor_nr_suffix'], 1, 'X', false) .
        formatField($data['directory_building_property'], 40, 'X', $data['list_code'] == 'LE' || $data['list_code'] == 'SA', 'L') .
        formatField($data['directory_building_location'], 30, 'X', false) .
        formatField($data['directory_street_house_nr1'], 5, 'X', false) .
        formatField($data['directory_street_house_nr1_suffix'], 3, 'X', false) .
        formatField($data['directory_street_house_nr2'], 5, 'X', false) .
        formatField($data['directory_street_house_nr2_suffix'], 1, 'X', false) .
        formatField($data['directory_street_name1'], 25, 'X', $data['list_code'] == 'LE' || $data['list_code'] == 'SA', 'L') .
        formatField($data['directory_street_type1'], 8, 'X', false) .
        formatField($data['directory_street_suffix1'], 6, 'X', false) .
        formatField($data['directory_street_name2'], 25, 'X', false, 'L') .
        formatField($data['directory_street_type2'], 4, 'X', false) .
        formatField($data['directory_street_suffix2'], 2, 'X', false) .
        formatField($data['directory_address_locality'], 40, 'X', $data['list_code'] == 'LE' || $data['list_code'] == 'SA', 'L') .
        formatField($data['directory_address_state'], 3, 'X', $data['list_code'] == 'LE' || $data['list_code'] == 'SA') .
        formatField($data['directory_address_postcode'], 4, 'N', $data['list_code'] == 'LE' || $data['list_code'] == 'SA') .
        formatField($data['list_code'], 2, 'X', true) .
        formatField($data['usage_code'], 1, 'X', true) .
        formatField($data['type_of_service'], 5, 'X', false, 'L') .
        formatField($data['customer_contact_name1'], 40, 'X', false, 'L') .
        formatField($data['customer_contact_name2'], 40, 'X', false, 'L') .
        formatField($data['customer_contact_nr'], 20, 'X', false, 'L') .
        formatField(CSP_CODE, 3, 'X', true) .
        formatField(DATA_PROVIDER_CODE, 6, 'X', true) .
        formatField($data['transaction_date'], 14, 'N', true) .
        formatField($data['service_status_date'], 14, 'N', true) .
        formatField($data['alternate_address_flag'], 1, 'X', true) .
        formatField($data['prior_public_number'], 20, 'X', false, 'L') .
        "\n"; // Including the record delimiter as part of the length

    return $formattedRow;
}

/**
 * Dynamically creates an array of dummy data records for transaction records.
 *
 * @return array An array of associative arrays, each representing a transaction record with all required and optional fields.
 */
function createDummyData() {
    $typeOfServiceOptions = [
        'DODAT', 'DOM2M', 'DOIOT', 'FAX', 'FCALL', 'FIXED', 'MOBIL', 'MODEM', 
        'ONE3', 'PAGER', 'PAYPH', 'PRVPY', 'PREM', 'SATEL', 'VMFIX', 'VMMOB', 'VMPDM', 'VMMVH'
    ];
    $listCodes = ['LE', 'UL', 'SA'];
    $localities = [
        ['suburb' => 'Parramatta', 'state' => 'NSW', 'postcode' => '2150'],
        ['suburb' => 'Melbourne', 'state' => 'VIC', 'postcode' => '3000'],
        ['suburb' => 'Brisbane', 'state' => 'QLD', 'postcode' => '4000']
    ];

    $dummyData = [];

    for ($i = 0; $i < 10; $i++) {
        $listCode = $listCodes[array_rand($listCodes)];
        $directoryMandatory = ($listCode == 'LE' || $listCode == 'SA');
        $locality = $localities[array_rand($localities)];

        $dummyData[] = [
            'public_number' => '04' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            'service_status_code' => ($i % 2 === 0) ? 'C' : 'D',
            'pending_flag' => 'F',
            'cancel_pending_flag' => 'F',
            'customer_name' => "Customer " . $i,
            'customer_name2' => "Customer2 " . $i,
            'long_name' => "Long Customer Name " . $i . " With Even Longer Suffix",
            'customer_title' => ($i % 3 === 0) ? 'Dr.' : (($i % 3 === 1) ? 'Mr.' : 'Ms.'),
            'finding_name1' => "Finding Name " . $i,
            'finding_name2' => "Secondary Finding Name " . $i,
            'finding_title' => 'Title ' . $i,
            'service_building_type' => 'Type' . $i,
            'service_building_1st_nr' => strval($i * 100),
            'service_building_1st_suffix' => ($i % 2 === 0) ? 'A' : 'B',
            'service_building_2nd_nr' => strval(($i * 100) + 5),
            'service_building_2nd_suffix' => ($i % 2 === 0) ? 'C' : 'D',
            'service_building_floor_type' => 'Fl',
            'service_building_floor_nr' => strval($i),
            'service_building_floor_nr_suffix' => ($i % 2 === 0) ? '' : 'S',
            'service_building_property' => "Property " . $i,
            'service_building_location' => ($i % 2 === 0) ? 'Front' : 'Back',
            'service_street_house_nr1' => strval($i * 10),
            'service_street_house_nr1_suffix' => ($i % 2 === 0) ? '' : 'S',
            'service_street_house_nr2' => strval(($i * 10) + 2),
            'service_street_house_nr2_suffix' => ($i % 2 === 0) ? 'N' : '',
            'service_street_name1' => "Main St " . $i,
            'service_street_type1' => 'Rd',
            'service_street_suffix1' => ($i % 2 === 0) ? 'E' : 'W',
            'service_street_name2' => "Second St " . $i,
            'service_street_type2' => 'Ave',
            'service_street_suffix2' => ($i % 2 === 0) ? 'N' : 'S',
            'service_address_locality' => $locality['suburb'],
            'service_address_state' => $locality['state'],
            'service_address_postcode' => $locality['postcode'],
            'directory_building_type' => 'Office',
            'directory_building_1st_nr' => '123',
            'directory_building_1st_suffix' => 'A',
            'directory_building_2nd_nr' => '124',
            'directory_building_2nd_suffix' => 'B',
            'directory_building_floor_type' => 'FL',
            'directory_building_floor_nr' => '6',
            'directory_building_floor_nr_suffix' => '',
            'directory_building_property' => $directoryMandatory ? "Dir Property " . $i : '',
            'directory_building_location' => 'Corner',
            'directory_street_house_nr1' => '500',
            'directory_street_house_nr1_suffix' => 'B',
            'directory_street_house_nr2' => '502',
            'directory_street_house_nr2_suffix' => '',
            'directory_street_name1' => $directoryMandatory ? "Directory Main St " . $i : '',
            'directory_street_type1' => 'Blvd',
            'directory_street_suffix1' => 'N',
            'directory_street_name2' => 'Aux St',
            'directory_street_type2' => 'Way',
            'directory_street_suffix2' => 'S',
            'directory_address_locality' => $directoryMandatory ? $locality['suburb'] : '',
            'directory_address_state' => $directoryMandatory ? $locality['state'] : '',
            'directory_address_postcode' => $directoryMandatory ? $locality['postcode'] : '',
            'list_code' => $listCode,
            'usage_code' => ($i % 4 === 0) ? 'R' : (($i % 4 === 1) ? 'B' : (($i % 4 === 2) ? 'G' : 'C')),
            'type_of_service' => $typeOfServiceOptions[array_rand($typeOfServiceOptions)],
            'customer_contact_name1' => "Contact Name " . $i,
            'customer_contact_name2' => "Contact Last Name " . $i,
            'customer_contact_nr' => '04000000' . strval($i),
            'transaction_date' => date('YmdHis', strtotime('-' . $i . ' days')),
            'service_status_date' => date('YmdHis', strtotime('-' . ($i + 1) . ' days')),
            'alternate_address_flag' => ($i % 2 === 0) ? 'T' : 'F',
            'prior_public_number' => str_pad($i + 202345678, 20, '0', STR_PAD_LEFT)
        ];
    }

    return $dummyData;
}

/**
 * Generates a compliant IPND upload file name based on a sequence number.
 * The file name follows the structure: IPND<TT><XXXXX>.<NNNNNNN>
 * 
 * @param int $sequence The sequence number for the file name.
 * @return string The formatted file name.
 */
function ipndUploadFilename($sequence) {
    $fileType = "UP"; // FileType for Upload file
    $fileSource = FILE_SOURCE_CODE; // Example file source, adjust as necessary
    $formattedSequence = str_pad($sequence, 7, "0", STR_PAD_LEFT); // Ensuring 7 digits with leading zeros

    // Construct the file name
    $filename = "IPND{$fileType}{$fileSource}.{$formattedSequence}";

    return strtoupper($filename); // Ensure all letters are upper case as specified
}

$dummyData = createDummyData();
// Now $dummyData contains 10 varied records



$fname = ipndUploadFilename(6);
// Generate the file content
$fileContent = createHeader($fname);
foreach ($dummyData as $data) {
    $fileContent .= createDataRow($data);
}
$fileContent .= createTrailer($fname,count($dummyData));

// Output to a file
file_put_contents($fname, $fileContent);
echo "File created with dummy data: ".$fname.PHP_EOL;
