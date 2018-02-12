<?php

namespace app\components;

use app\models\Pickup;
use Yii;
use yii\base\Exception;
use yii\helpers\FileHelper;

/**
 * Class CopeFreight
 * @package app\components
 */
class CopeFreight
{

    public static $host = 'esmart.cope.com.au';
    public static $port = 22;
    public static $username = 'import';
    public static $password = '53cr3t';
    private static $_sftp;

    /**
     * @param Pickup $pickup
     * @throws Exception
     */
    public static function upload($pickup)
    {
        $data = static::xml($pickup);
        $remoteFile = '/home/import/xmlimport/upload/' . date('YmdHis') . '_pickup-' . $pickup->id . '.xml';

        if (!$pickup->carrier_ref) {
            $pickup->carrier_ref = static::getConnoteNo($pickup);
            $pickup->save(false);
        }

        static::uploadFile($remoteFile, $data);
    }

    /**
     * @param Pickup $pickup
     * @return string
     */
    private static function getConnoteNo($pickup)
    {
        return 'AFI' . sprintf('%07d', $pickup->id);
    }

    /**
     * @param Pickup $pickup
     * @return string
     */
    public static function xml($pickup)
    {
        $xml = [
            'userId' => 'AFI3511',

            'connote' => [
                'customer' => '003511',
                'connoteNo' => static::getConnoteNo($pickup),
                'connoteDate' => date('Y-m-d'),

                'senderName' => 'AFI Branding',
                'senderAddress1' => '33 Lakewood Boulevard',
                'senderAddress2' => '',
                'senderAddress3' => '',
                'senderSuburb' => 'Carrum Downs',
                'senderState' => 'VIC',
                'senderPostcode' => '3201',
                'senderContact' => 'James Nowak',
                'senderPhone' => '0408006438',

                'receiverName' => '',
                'receiverAddress1' => '',
                'receiverAddress2' => '',
                'receiverAddress3' => '',
                'receiverSuburb' => '',
                'receiverPostcode' => '',
                'receiverState' => '',
                'receiverContact' => '',
                'receiverPhone' => '',

                'pickupDate' => '',
                'pickupTime' => '',
                'deliveryDate' => '',
                'deliveryTime' => '',
                'totalQuantity' => '',
                'totalPallets' => '',
                'totalWeight' => '',
                'totalVolume' => '',

                'senderReference' => 'pickup-' . $pickup->id,
                'description' => '',
                'specialInstructions' => '',
                'notes' => '',
                'jobType' => $pickup->carrier && $pickup->carrier->cope_freight_code ? $pickup->carrier->cope_freight_code : '', // ROAD
                'serviceType' => 'TAIL1',
                'priorityType' => '',
                'vehicleType' => '',
                'freightLines' => [],
            ],
        ];

        // delivery instructions
        $instructions = [];
        foreach ($pickup->packages as $package) {
            if ($package->address && $package->address->instructions) {
                $instructions[md5($package->address->instructions)] = $package->address->instructions;
            }
        }
        if ($instructions) {
            $xml['connote']['specialInstructions'] = implode("\n", $instructions);
        }

        // receiver
        if ($pickup->packages) {
            $package = $pickup->packages[0];
            if ($package->address) {
                $address = [];
                foreach (explode("\n", trim($package->address->street)) as $street) {
                    $address[] = $street;
                }
                $xml['connote']['receiverName'] = trim($package->address->name);
                $xml['connote']['receiverAddress1'] = trim($address[0]);
                $xml['connote']['receiverAddress2'] = isset($address[1]) ? trim($address[1]) : '';
                $xml['connote']['receiverAddress3'] = isset($address[2]) ? trim($address[2]) : '';
                $xml['connote']['receiverSuburb'] = trim($package->address->city);
                $xml['connote']['receiverPostcode'] = trim($package->address->postcode);
                $xml['connote']['receiverState'] = trim($package->address->state);
                $xml['connote']['receiverContact'] = trim($package->address->contact);
                $xml['connote']['receiverPhone'] = trim($package->address->phone);
            }

        }

        // packages
        foreach ($pickup->packages as $package) {
            $xml['connote']['freightLines']['freightLine'][] = [
                'itemCode' => trim($package->type),
                'scanCode' => '',
                'freightCode' => '',
                'itemReference' => 'package-' . $package->id,
                'description' => 'carton',
                'quantity' => 1,
                'pallets' => '',
                'labels' => '',
                'totalWeight' => '',
                'totalVolume' => '',
                'length' => $package->length / 100,
                'width' => $package->width / 100,
                'height' => $package->height / 100,
                'weight' => $package->dead_weight,
            ];
        }
        return YdArray2Xml::createXML('transportData', $xml)->saveXML();
    }

    public static function sftp()
    {
        if (!static::$_sftp) {
            $ssh = @ssh2_connect(static::$host, static::$port);
            if (!$ssh)
                throw new Exception('Could not connect to ' . static::$host . ' on port ' . static::$port . '.');
            if (!@ssh2_auth_password($ssh, static::$username, static::$password))
                throw new Exception('Could not authenticate with username ' . static::$username . ' and password ' . static::$password . '.');
            static::$_sftp = @ssh2_sftp($ssh);
            if (!static::$_sftp)
                throw new Exception("Could not initialize SFTP subsystem.");
        }
        return static::$_sftp;
    }

    public static function uploadFile($remote_file, $data)
    {
        $sftp = static::sftp();
        $stream = @fopen('ssh2.sftp://' . intval($sftp) . $remote_file, 'w');
        if (!$stream)
            throw new Exception("Could not open file: $remote_file");
        if (@fwrite($stream, $data) === false)
            throw new Exception("Could not send data.");
        @fclose($stream);
    }

}

