<?php namespace Lamoni\IPSuite;

abstract class IPSuite
{

    static public function ConvertIPv4CIDRToMask($cidr)
    {
        $subnetMask = str_pad("", $cidr, "1");

        $subnetMask = str_pad($subnetMask, 32, "0");

        $subnetMask = array_map(
            function($x) {
               return bindec($x);
            },
            str_split($subnetMask, 8)
        );

        return implode(".", $subnetMask);

    }

    static public function ConvertIPv4ToDecimal($ip)
    {

        return ip2long($ip);

    }

    static public function ConvertDecimalToIPv4($dec)
    {

        return long2ip($dec);

    }

    static public function ConvertIPv4CIDRToDecimal($ipAndCidr)
    {

        list($ip, $subnetMask) = explode("/", $ipAndCidr);

        $ip = static::ConvertIPv4ToDecimal($ip);

        $subnetMask = static::ConvertIPv4CIDRToMask($subnetMask);

        $subnetMask = static::ConvertIPv4ToDecimal($subnetMask);

        return compact('ip', 'subnetMask');

    }

    static public function GetIPv4NetworkAddress($ipAndCidr)
    {

        $ipAndCidr = static::ConvertIPv4CIDRToDecimal($ipAndCidr);

        return static::ConvertDecimalToIPv4($ipAndCidr['ip'] & $ipAndCidr['subnetMask']);

    }

    static public function GetIPv4BroadcastAddress($ipAndCidr)
    {

        $ipAndCidr = static::ConvertIPv4CIDRToDecimal($ipAndCidr);

        return static::ConvertDecimalToIPv4($ipAndCidr['ip'] | ~$ipAndCidr['subnetMask']);

    }

    static public function IsIPv4SubnetWithinSupernet($ipAndCidrSub, $ipAndCidrSuper)
    {

        $ipAndCidrSubMin   = static::ConvertIPv4ToDecimal(
            static::GetIPv4NetworkAddress($ipAndCidrSub)
        );

        $ipAndCidrSubMax   = static::ConvertIPv4ToDecimal(
            static::GetIPv4BroadcastAddress($ipAndCidrSub)
        );

        $ipAndCidrSuperMin   = static::ConvertIPv4ToDecimal(
            static::GetIPv4NetworkAddress($ipAndCidrSuper)
        );

        $ipAndCidrSuperMax   = static::ConvertIPv4ToDecimal(
            static::GetIPv4BroadcastAddress($ipAndCidrSuper)
        );

        if ($ipAndCidrSuperMin <= $ipAndCidrSubMin && $ipAndCidrSuperMax >= $ipAndCidrSubMax) {
            return true;
        }

        return false;

    }

    static public function IsIPv4AddressWithinSubnet($ipOnly, $ipAndCidrSuper)
    {

        return static::IsIPv4SubnetWithinSupernet("{$ipOnly}/32", $ipAndCidrSuper);

    }

    static public function GetIPv4NumberOfHostAddresses($ipAndCidr)
    {

        $ipAndCidrNetwork   = static::ConvertIPv4ToDecimal(
            static::GetIPv4NetworkAddress($ipAndCidr)
        );


        $ipAndCidrBroadcast = static::ConvertIPv4ToDecimal(
            static::GetIPv4BroadcastAddress($ipAndCidr)
        );

        return $ipAndCidrBroadcast - $ipAndCidrNetwork - 1;

    }


    /***********************************************
     * IPV6
     ***********************************************/



    static public function ConvertIPv6CIDRToMask($cidr)
    {

        $subnetMask = str_pad("", $cidr, "1");

        $subnetMask = str_pad($subnetMask, 128, "0");

        $subnetMask = array_map(
            function($x) {
                return dechex(bindec($x));
            },
            str_split($subnetMask, 16)
        );

        return implode(":", $subnetMask);

    }

    static public function ConvertIPv6ToBinary($ip)
    {

        return inet_pton($ip);

    }

    static public function ConvertBinaryToIPv6($bin)
    {

        return static::ConvertIPv6PackedToAddress($bin);

    }

    static public function ConvertIPv6PackedToAddress($packed)
    {

        return inet_ntop($packed);

    }

    static public function ConvertIPv6CIDRToBinary($ipAndCidr)
    {

        list($ip, $subnetMask) = explode("/", $ipAndCidr);

        $ip = static::ConvertIPv6ToBinary($ip);

        $subnetMask = static::ConvertIPv6ToBinary(
            static::ConvertIPv6CIDRToMask($subnetMask)
        );

        return compact('ip', 'subnetMask');

    }

    static public function GetIPv6NetworkAddress($ipAndCidr)
    {

        $ipAndCidr = static::ConvertIPv6CIDRToBinary($ipAndCidr);

        return static::ConvertBinaryToIPv6($ipAndCidr['ip'] & $ipAndCidr['subnetMask']);

    }

    static public function GetIPv6BroadcastAddress($ipAndCidr)
    {

        $ipAndCidr = static::ConvertIPv6CIDRToBinary($ipAndCidr);

        return static::ConvertBinaryToIPv6($ipAndCidr['ip'] | ~$ipAndCidr['subnetMask']);

    }

    // CHECK - not sure if binary comparisons work like this...
    static public function IsIPv6SubnetWithinSupernet($ipAndCidrSub, $ipAndCidrSuper)
    {

        $ipAndCidrSubMin   = static::ConvertIPv6ToBinary(
            static::GetIPv6NetworkAddress($ipAndCidrSub)
        );

        $ipAndCidrSubMax   = static::ConvertIPv6ToBinary(
            static::GetIPv6BroadcastAddress($ipAndCidrSub)
        );

        $ipAndCidrSuperMin   = static::ConvertIPv6ToBinary(
            static::GetIPv6NetworkAddress($ipAndCidrSuper)
        );

        $ipAndCidrSuperMax   = static::ConvertIPv6ToBinary(
            static::GetIPv6BroadcastAddress($ipAndCidrSuper)
        );

        if ($ipAndCidrSuperMin <= $ipAndCidrSubMin && $ipAndCidrSuperMax >= $ipAndCidrSubMax) {

            return true;

        }

        return false;

    }

    static public function IsIPv6AddressWithinSubnet($ipOnly, $ipAndCidrSuper)
    {

        return static::IsIPv6SubnetWithinSupernet("{$ipOnly}/128", $ipAndCidrSuper);

    }

    static public function NormalizeIPv6Address($ipv6)
    {

        if (strpos($ipv6, "::") === false && substr_count($ipv6, ":") !== 7) {
            throw new \Exception("Invalid IPv6 address");
        }

        if (strpos($ipv6, "::") !== false) {

            $padding = ":";

            $paddingRequired = 9 - substr_count($ipv6, ":");

            while (substr_count($padding, ":") < $paddingRequired) {

                $padding .= "0000:";

            }

            $ipv6 = str_replace("::", $padding, $ipv6);
        }

        $ipv6 = explode(":", $ipv6);

        foreach ($ipv6 as &$field) {

            while (strlen($field) < 4) {

                $field = "0{$field}";

            }

        }

        return implode(":", $ipv6);
    }



}