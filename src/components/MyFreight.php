<?php

namespace app\components;

use app\models\Pickup;
use Yii;
use yii\helpers\FileHelper;

/**
 * Class MyFreight
 * @package app\components
 */
class MyFreight
{

    /**
     * @param Pickup $pickup
     * @return bool
     */
    public static function upload($pickup)
    {
        $xml = static::xml($pickup);
        $file = Yii::$app->runtimePath . '/my-freight/' . date('Y-m-d-H-i-s') . '_pickup-' . $pickup->id . '.xml';
        FileHelper::createDirectory(dirname($file), 0777);
        file_put_contents($file, $xml);

        $success = false;
        $host = YII_DEBUG ? 'ftp.qa.teamwilberforce.com' : 'ftp.teamwilberforce.com';
        $ftp = ftp_connect($host, 21, 30);
        if ($ftp) {
            if (ftp_login($ftp, 'afi', 'ETBQz7vD7dY84Gp') && ftp_pasv($ftp, true)) {
                if (ftp_put($ftp, date('Y-m-d-H-i-s') . '_pickup-' . $pickup->id . '.xml', $file, FTP_ASCII)) {
                    $success = true;
                }
            }
        }
        unlink($file);
        return $success;
    }

    /**
     * @param Pickup $pickup
     * @return string
     */
    public static function xml($pickup)
    {
        $xml = [
            '@attributes' => [
                'xmlns' => 'http://www.myfreight.com.au/myfreight/0.8.1',
            ],
            'Consignment' => [
                'Header' => [
                    'SiteCode' => 'AFI',
                    'DespatchDate' => date('Y-m-d'),
                    'Payer' => 'S',
                    'Reference' => 'pickup-' . $pickup->id,
                ],
                'Sender' => [
                    'Name' => 'AFI Branding',
                    //'Code' => '',
                    'Address' => [
                        'AddressLine1' => '33 Lakewood Boulevard',
                        'Locality' => 'Carrum Downs',
                        'Region' => 'VIC',
                        'Postcode' => '3201',
                        'Country' => 'Australia',
                    ],
                ],
                'Receiver' => [],
                'Items' => [],
            ],
        ];

        // carrier
        if ($pickup->carrier && $pickup->carrier->my_freight_code) {
            $xml['Consignment']['Header']['CarrierServiceCode'] = $pickup->carrier->my_freight_code;
        }

        // delivery instructions
        $instructions = [];
        $email = '';
        $job = $pickup->getFirstJob();
        if ($job) {
            $email = $job->contact->email;
        }
        foreach ($pickup->packages as $package) {
            if ($package->address && $package->address->instructions) {
                $instructions[md5($package->address->instructions)] = $package->address->instructions;
            }
        }
        if ($instructions) {
            $xml['Consignment']['Header']['SpecialInstructions'] = implode("\n", $instructions);
        }

        // packages
        foreach ($pickup->packages as $package) {
            if (!$xml['Consignment']['Receiver']) {
                $address = [];
                $address[] = $package->address ? 'ATTN: ' . trim($package->address->contact) : '';
                foreach (explode("\n", trim($package->address->street)) as $street) {
                    $address[] = $street;
                }
                $xml['Consignment']['Receiver'] = [
                    'Name' => $package->address ? trim($package->address->name) : '',
                    'Phone' => $package->address ? trim($package->address->phone) : '',
                    'Email' => trim($email),
                    'Address' => [
                        'AddressLine1' => isset($address[0]) ? trim($address[0]) : '',
                        'AddressLine2' => isset($address[1]) ? trim($address[1]) : '',
                        'AddressLine3' => isset($address[2]) ? trim($address[2]) : '',
                        'AddressLine4' => isset($address[3]) ? trim($address[3]) : '',
                        'Locality' => $package->address ? trim($package->address->city) : '',
                        'Region' => $package->address ? trim($package->address->state) : '',
                        'Postcode' => $package->address ? trim($package->address->postcode) : '',
                        'Country' => $package->address ? trim($package->address->country) : 'Australia',
                    ],
                ];
            }
            $xml['Consignment']['Items']['Item'][] = [
                'Quantity' => 1,
                'ItemType' => trim($package->type),
                'Width' => trim($package->width),
                'Length' => trim($package->length),
                'Height' => trim($package->height),
                'TotalDeadWeight' => trim($package->dead_weight),
            ];
        }
        return YdArray2Xml::createXML('Consignments', $xml)->saveXML();
    }
}