<?php namespace Lamoni\IPSuite;

/**
 * Class IPSuite
 * @package Lamoni\IPSuite
 */
abstract class IPSuite
{
    /**
     * Converts IPv4 CIDR to subnet mask format
     *
     * @param $cidr
     * @return string
     */
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

    /**
     * Converts IPv4 address to its decimal notation
     *
     * @param $ip
     * @return int
     */
    static public function ConvertIPv4ToDecimal($ip)
    {

        return ip2long($ip);

    }

    /**
     * Converts decimal to its IPv4 address notation
     *
     * @param $dec
     * @return string
     */
    static public function ConvertDecimalToIPv4($dec)
    {

        return long2ip($dec);

    }

    /**
     * Converts IPv4 and CIDR to decimal notation
     *
     * @param $ipAndCidr
     * @return array
     */
    static public function ConvertIPv4CIDRToDecimal($ipAndCidr)
    {

        list($ip, $subnetMask) = explode("/", $ipAndCidr);

        $ip = static::ConvertIPv4ToDecimal($ip);

        $subnetMask = static::ConvertIPv4CIDRToMask($subnetMask);

        $subnetMask = static::ConvertIPv4ToDecimal($subnetMask);

        return compact('ip', 'subnetMask');

    }

    /**
     * Gets the "network" address of a given prefix (ip/cidr)
     *
     * @param $ipAndCidr
     * @return string
     */
    static public function GetIPv4NetworkAddress($ipAndCidr)
    {

        $ipAndCidr = static::ConvertIPv4CIDRToDecimal($ipAndCidr);

        return static::ConvertDecimalToIPv4($ipAndCidr['ip'] & $ipAndCidr['subnetMask']);

    }

    /**
     * Gets the "broadcast" address of a given prefix (ip/cidr)
     *
     * @param $ipAndCidr
     * @return string
     */
    static public function GetIPv4BroadcastAddress($ipAndCidr)
    {

        $ipAndCidr = static::ConvertIPv4CIDRToDecimal($ipAndCidr);

        return static::ConvertDecimalToIPv4($ipAndCidr['ip'] | ~$ipAndCidr['subnetMask']);

    }

    /**
     * Checks if a given range is within a range
     *
     * @param $ipAndCidrSub
     * @param $ipAndCidrSuper
     * @return bool
     */
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

    /**
     * Checks if a given IP address is within a range
     *
     * @param $ipOnly
     * @param $ipAndCidrSuper
     * @return bool
     */
    static public function IsIPv4AddressWithinSubnet($ipOnly, $ipAndCidrSuper)
    {

        return static::IsIPv4SubnetWithinSupernet("{$ipOnly}/32", $ipAndCidrSuper);

    }

    /**
     * Get the number of host addresses in a given subnet
     *
     * @param $ipAndCidr
     * @return int
     */
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


    /**
     *
     * Convert IPv6 CIDR to IPv6 mask
     *
     * @param $cidr
     * @return string
     */
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

    /**
     * Convert IPv6 address to packed binary
     *
     * @param $ip
     * @return string
     */
    static public function ConvertIPv6ToBinary($ip)
    {

        return inet_pton($ip);

    }

    /**
     * Converts packed binary to IPv6 address (alias for ConvertIPv6PackedToAddress)
     *
     * @param $bin
     * @return string
     */
    static public function ConvertBinaryToIPv6($bin)
    {

        return static::ConvertIPv6PackedToAddress($bin);

    }

    /**
     * Converts packed binary to IPv6 address
     *
     * @param $packed
     * @return string
     */
    static public function ConvertIPv6PackedToAddress($packed)
    {

        return inet_ntop($packed);

    }

    /**
     * Converts IP/xxx CIDR to a packed binary for operations
     *
     * @param $ipAndCidr
     * @return array
     */
    static public function ConvertIPv6CIDRToBinary($ipAndCidr)
    {

        list($ip, $subnetMask) = explode("/", $ipAndCidr);

        $ip = static::ConvertIPv6ToBinary($ip);

        $subnetMask = static::ConvertIPv6ToBinary(
            static::ConvertIPv6CIDRToMask($subnetMask)
        );

        return compact('ip', 'subnetMask');

    }

    /**
     * Get the "network" address of an IPv6 prefix
     *
     * @param $ipAndCidr
     * @return string
     */
    static public function GetIPv6NetworkAddress($ipAndCidr)
    {

        $ipAndCidr = static::ConvertIPv6CIDRToBinary($ipAndCidr);

        return static::ConvertBinaryToIPv6($ipAndCidr['ip'] & $ipAndCidr['subnetMask']);

    }

    /**
     * There is no such thing as a "broadcast" address in IPv6, but I'm keeping this here for use in the "Within" functions
     *
     * @param $ipAndCidr
     * @return string
     */
    static public function GetIPv6BroadcastAddress($ipAndCidr)
    {

        $ipAndCidr = static::ConvertIPv6CIDRToBinary($ipAndCidr);

        return static::ConvertBinaryToIPv6($ipAndCidr['ip'] | ~$ipAndCidr['subnetMask']);

    }

    /**
     * Checks if an IPv6 subnet is within an IPv6 supernet
     *
     * @param $ipAndCidrSub
     * @param $ipAndCidrSuper
     * @return bool
     */
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

    /**
     * Checks if a IPv6 address is within a range
     *
     * @param $ipOnly
     * @param $ipAndCidrSuper
     * @return bool
     */
    static public function IsIPv6AddressWithinSubnet($ipOnly, $ipAndCidrSuper)
    {

        return static::IsIPv6SubnetWithinSupernet("{$ipOnly}/128", $ipAndCidrSuper);

    }

    /**
     * Converts an IPv6 address and formats to leading 0s format
     *
     * @todo Refactor the living hell out of this.  I haven't found a corner-case that doesn't work YET, but there's one out there, I know it.
     * @param $ipv6
     * @return string
     * @throws \Exception
     */
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