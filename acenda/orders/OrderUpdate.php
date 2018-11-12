<?php
    $username = "371f4ec2-2c19-481b-83c1-0a1e2bcfd92f";
    $password = "Brand2018!";

    $json = file_get_contents('php://input');
    // $json = '{"acenda":{"store":{"id":4617,"name":"brandsdistribution-store"},"subscription":{"id":1513,"credentials":{"email":"johna@torreycommerce.com","password":"test","api_key":"test","web_host":"test"}},"event_payload":[{"Order":{"id":"13","date_modified":"2018-03-12 18:24:17","order_number":"3708573","status":"open","email":"gavinm@torreycommerce.com","ip":"209.209.231.100, 209.209.231.100","shipping_first_name":"Gavin","shipping_last_name":"Mandelbaum","shipping_phone_number":"+18583373775","shipping_street_line1":"2385 CALLE DEL ORO","shipping_street_line2":"","shipping_city":"LA JOLLA","shipping_state":"CA","shipping_zip":"92037","shipping_country":"US","shipping_method":2830234,"shipping_rate":"0.00","shipping_rate_original":"0.00","tax_percent":7.75,"tax_shipping":false,"tax_included":false,"returns_pending":0,"returns_rma_numbers":"","returnable_items":0,"giftlist_present":false,"subtotal":"50.00","tax":"3.88","tax_original":"3.88","total":"53.88","charge_amount":"0.00","unsettled":"-53.88","transaction_status":"","fulfillment_status":"pending","fraud_check":false,"fraud_results":{"maxmind":"7.29"},"marketplace_id":"13","calculate_tax":false,"card_type":null,"payment_method_nonce":null,"discount_collection":null,"bd_order_id":"","items":[{"id":13,"date_created":"2018-03-12 18:24:16","date_modified":"2018-03-12 18:24:16","product_id":3106,"variant_id":10128,"wishlist_id":null,"registry_id":null,"status":"open","vendor":"acenda","tracking":null,"name":"BD Test Product - X - Red","sku":"252808","barcode":"Test3","quantity":-1,"price":50,"ordered_quantity":-1,"ordered_price":50,"backorder":"0","fulfilled_quantity":0,"fulfillment_status":"pending","marketplace_name":"","marketplace_item_id":null,"marketplace_tax_allocation":null,"marketplace_ship_allocation":null,"warnings":[]}],"billing_first_name":"Gavin","billing_last_name":"Mandelbaum","billing_phone_number":"+18583373775","billing_street_line1":"2385 CALLE DEL ORO","billing_street_line2":"","billing_city":"LA JOLLA","billing_state":"CA","billing_country":"US","billing_zip":"92037","cancellation_window":"30","fraud_score":0,"payments":[],"coupons":[]}}]}}';
    $obj = json_decode($json, true);

    file_put_contents('webhook.log',"---Start---\n",FILE_APPEND);
    file_put_contents('webhook.log',json_encode($obj['acenda'])."\n",FILE_APPEND);
    file_put_contents('webhook.log',"---Ends---\n",FILE_APPEND);

    $order_data = $obj['acenda']['event_payload'];
    for ($i = 0; $i < count($order_data); $i++) {
        $order = $order_data[$i]['Order'];
        if ($order['bd_order_id'] == '') {
            if (count($order['items'])) {
                $items = $order['items'];
$xmlstr = <<<XML
<?xml version='1.0' standalone='yes'?>
<root>
</root>
XML;
                $xml = new SimpleXMLElement($xmlstr);
                $xmlOperation = $xml->addChild("operation");
                $xmlOperation->addAttribute("type", "unlock");
                                
                for ($j = 0; $j < count($items); $j++) {
                    $xmlModel = $xmlOperation->addChild("model");
                    $xmlModel->addAttribute("stock_id", $items[$j]['sku']);
                    $xmlModel->addAttribute("quantity", $items[$j]['quantity']);
                }
                $xmlText = $xml->asXML();

                $url = "https://us.brandsdistribution.com/restful/ghost/orders/sold";

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml','Accept: application/xml'));
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlText);
                $data = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $json = json_encode(simplexml_load_string($data));
                $resObj = json_decode($json, true);
                file_put_contents('response.log', $json . "\n", FILE_APPEND);
                $order_id = $resObj['@attributes']['order_id'];

$rec_xml_string = <<<XML
<?xml version='1.0' standalone='yes'?>
<root>
</root>
XML;
                $rec_xml = new SimpleXMLElement($rec_xml_string);
                $elemOrderId = $rec_xml->addChild("order_id", $order_id);
                $elemOrderList = $rec_xml->addChild("order_list");

                $elemOrder = $elemOrderList->addChild("order");

                $elemKey = $elemOrder->addChild("key", $order_id);
                $date = date_create_from_format("Y-m-d H:i:s", $order['date_modified']);
                $elemDate = $elemOrder->addChild('date', date_format($date, 'Y/m/d H:i:s') . " -0800");
                $elemRecipientDetails = $elemOrder->addChild('recipient_details');

                $elemRecipient = $elemRecipientDetails->addChild('recipient', $order['shipping_first_name'] . " " . $order['shipping_last_name']);
                $elemCareof = $elemRecipientDetails->addChild('careof','');
                $elemCfpiva = $elemRecipientDetails->addChild('cfpiva', '12345558');
                $elemCustomerKey = $elemRecipientDetails->addChild('customer_key', $order_id);
                $elemNote = $elemRecipientDetails->addChild('notes', '');
                
                $elemAddress = $elemRecipientDetails->addChild('address');
                $elemStreetType = $elemAddress->addChild('street_type', 'Via');
                $street = explode(' ', $order['shipping_street_line1']);
                $address = $street[1];
                for ($k = 2; $k < count($street); $k++) {
                    $address = $address .  " " . $street[$k];
                }
                $elemStreetName = $elemAddress->addChild('street_name', $street[0]);
                $elemAddressNumber = $elemAddress->addChild('address_number', $address . " " . $order['shipping_street_line2']);
                $elemZip = $elemAddress->addChild('zip', $order['shipping_zip']);
                $elemCity = $elemAddress->addChild('city', $order['shipping_city']);
                $elemProvince = $elemAddress->addChild('province', $order['shipping_state']);
                $elemCountry = $elemAddress->addChild('countrycode', $order['shipping_country']);

                $elemPhone = $elemRecipientDetails->addChild('phone');
                $phoneNumber = dividePhoneNumber($order['shipping_phone_number'], $order['shipping_country']);
                echo "<br><br>" . json_encode($phoneNumber) . "<br>";
                $elemPrefix = $elemPhone->addChild('prefix', "+" . $phoneNumber[0]);
                $elemNumber = $elemPhone->addChild('number', $phoneNumber[1]);

                $elemItemList = $elemOrder->addChild('item_list');
                for ($j = 0; $j < count($items); $j++) {
                    $elemItem = $elemItemList->addChild("item");
                    $elemStockId = $elemItem->addChild("stock_id", $items[$j]['sku']);
                    $elemQuantity = $elemItem->addChild("quantity", $items[$j]['quantity']);
                }
                $xmlText = $rec_xml->asXML();

                file_put_contents('response.log', $xmlText . "\n", FILE_APPEND);

                $url = "https://us.brandsdistribution.com/restful/ghost/orders/0/dropshipping";

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml','Accept: application/xml'));
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlText);
                $data = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                // $json = json_encode(simplexml_load_string($data));
                file_put_contents('response.log', $data."\n", FILE_APPEND);

            }
        }
    }

    function dividePhoneNumber($str, $country) {
        $json = '{"BD": "880", "BE": "32", "BF": "226", "BG": "359", "BA": "387", "BB": "+1-246", "WF": "681", "BL": "590", "BM": "+1-441", "BN": "673", "BO": "591", "BH": "973", "BI": "257", "BJ": "229", "BT": "975", "JM": "+1-876", "BV": "", "BW": "267", "WS": "685", "BQ": "599", "BR": "55", "BS": "+1-242", "JE": "+44-1534", "BY": "375", "BZ": "501", "RU": "7", "RW": "250", "RS": "381", "TL": "670", "RE": "262", "TM": "993", "TJ": "992", "RO": "40", "TK": "690", "GW": "245", "GU": "+1-671", "GT": "502", "GS": "", "GR": "30", "GQ": "240", "GP": "590", "JP": "81", "GY": "592", "GG": "+44-1481", "GF": "594", "GE": "995", "GD": "+1-473", "GB": "44", "GA": "241", "SV": "503", "GN": "224", "GM": "220", "GL": "299", "GI": "350", "GH": "233", "OM": "968", "TN": "216", "JO": "962", "HR": "385", "HT": "509", "HU": "36", "HK": "852", "HN": "504", "HM": " ", "VE": "58", "PR": "+1-787 and 1-939", "PS": "970", "PW": "680", "PT": "351", "SJ": "47", "PY": "595", "IQ": "964", "PA": "507", "PF": "689", "PG": "675", "PE": "51", "PK": "92", "PH": "63", "PN": "870", "PL": "48", "PM": "508", "ZM": "260", "EH": "212", "EE": "372", "EG": "20", "ZA": "27", "EC": "593", "IT": "39", "VN": "84", "SB": "677", "ET": "251", "SO": "252", "ZW": "263", "SA": "966", "ES": "34", "ER": "291", "ME": "382", "MD": "373", "MG": "261", "MF": "590", "MA": "212", "MC": "377", "UZ": "998", "MM": "95", "ML": "223", "MO": "853", "MN": "976", "MH": "692", "MK": "389", "MU": "230", "MT": "356", "MW": "265", "MV": "960", "MQ": "596", "MP": "+1-670", "MS": "+1-664", "MR": "222", "IM": "+44-1624", "UG": "256", "TZ": "255", "MY": "60", "MX": "52", "IL": "972", "FR": "33", "IO": "246", "SH": "290", "FI": "358", "FJ": "679", "FK": "500", "FM": "691", "FO": "298", "NI": "505", "NL": "31", "NO": "47", "NA": "264", "VU": "678", "NC": "687", "NE": "227", "NF": "672", "NG": "234", "NZ": "64", "NP": "977", "NR": "674", "NU": "683", "CK": "682", "XK": "", "CI": "225", "CH": "41", "CO": "57", "CN": "86", "CM": "237", "CL": "56", "CC": "61", "CA": "1", "CG": "242", "CF": "236", "CD": "243", "CZ": "420", "CY": "357", "CX": "61", "CR": "506", "CW": "599", "CV": "238", "CU": "53", "SZ": "268", "SY": "963", "SX": "599", "KG": "996", "KE": "254", "SS": "211", "SR": "597", "KI": "686", "KH": "855", "KN": "+1-869", "KM": "269", "ST": "239", "SK": "421", "KR": "82", "SI": "386", "KP": "850", "KW": "965", "SN": "221", "SM": "378", "SL": "232", "SC": "248", "KZ": "7", "KY": "+1-345", "SG": "65", "SE": "46", "SD": "249", "DO": "+1-809 and 1-829", "DM": "+1-767", "DJ": "253", "DK": "45", "VG": "+1-284", "DE": "49", "YE": "967", "DZ": "213", "US": "1", "UY": "598", "YT": "262", "UM": "1", "LB": "961", "LC": "+1-758", "LA": "856", "TV": "688", "TW": "886", "TT": "+1-868", "TR": "90", "LK": "94", "LI": "423", "LV": "371", "TO": "676", "LT": "370", "LU": "352", "LR": "231", "LS": "266", "TH": "66", "TF": "", "TG": "228", "TD": "235", "TC": "+1-649", "LY": "218", "VA": "379", "VC": "+1-784", "AE": "971", "AD": "376", "AG": "+1-268", "AF": "93", "AI": "+1-264", "VI": "+1-340", "IS": "354", "IR": "98", "AM": "374", "AL": "355", "AO": "244", "AQ": "", "AS": "+1-684", "AR": "54", "AU": "61", "AT": "43", "AW": "297", "IN": "91", "AX": "+358-18", "AZ": "994", "IE": "353", "ID": "62", "UA": "380", "QA": "974", "MZ": "258"}';
        $data = json_decode($json, true);

        $str = substr($str, 1, strlen($str) - 1);
        $phoneCode = $data[$country];
        $number = substr($str, strlen($phoneCode) + 1, strlen($str) - strlen($phoneCode) - 1);
        return array($phoneCode, $number);
    }
?>
