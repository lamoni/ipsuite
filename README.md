IP Suite
-----------
PHP utilities for dealing with IPv4 and IPv6


Examples
--------
```php
echo IPSuite::GetIPv4NetworkAddress("192.168.0.1/24"); // 192.168.0.0

echo IPSuite::NormalizeIPv6Address("::1"); // 0000:0000:0000:0000:0000:0000:0000:0001

if (IPSuite::IsIPv4SubnetWithinSupernet("192.168.0.0/24", "192.168.0.0/16")) {
    echo "Yes!";
}
else {
    echo "Nope!";
}

if (IPSuite::IsIPv4AddressWithinSubnet("192.168.0.100", "192.168.0.0/24")) {
    echo "Yes!";
}
else {
    echo "Nope!";
}
```