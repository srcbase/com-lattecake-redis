<?php
/**
 * Created by PhpStorm.
 * User: LatteCake
 * Date: 16/7/26
 * Time: 下午4:22
 * File: IP.php
 */

namespace AppBundle\Utils;


/**
 * Class IP
 * @package AppBundle\Utils
 */
class IP
{
    /**
     * ip to long
     *
     * @param string $ip
     * @return int
     */
    public function ipToLong($ip)
    {
        return bindec(decbin(ip2long($ip)));
    }

    /**
     * reset ip
     *
     * @param $decimal
     * @return string
     */
    public function resetIp($decimal)
    {
        return long2ip($decimal);
    }

    /**
     * check ip
     *
     * @param $address
     * @return mixed
     */
    public function checkIP($address)
    {
        return filter_var($address, FILTER_VALIDATE_IP);
    }
}